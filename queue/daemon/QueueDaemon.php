<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/21
 * Time: 15:18
 */

namespace sinri\ark\queue\daemon;


class QueueDaemon
{
    const DAEMON_STYLE_SINGLE_SYNCHRONIZED = "SINGLE_SYNCHRONIZED";
    const DAEMON_STYLE_SINGLE_POOLED = "SINGLE_POOLED";

    protected $delegate;
    protected $daemonStyle;

    // properties which only used in pooled mode
    protected $childrenCount;

    /**
     * QueueDaemon constructor.
     * @param QueueDaemonDelegate $delegate
     */
    public function __construct($delegate)
    {
        $this->delegate = $delegate;

        $this->daemonStyle = $this->delegate->getDaemonStyle();
        if (!in_array(
            $this->daemonStyle,
            [
                self::DAEMON_STYLE_SINGLE_SYNCHRONIZED,
                self::DAEMON_STYLE_SINGLE_POOLED,
            ]
        )) {
            $this->daemonStyle = self::DAEMON_STYLE_SINGLE_SYNCHRONIZED;
        }

        //initialize
        $this->childrenCount = 0;
    }

    public function loop()
    {
        switch ($this->daemonStyle) {
            case self::DAEMON_STYLE_SINGLE_POOLED:
                $this->loopWithSinglePooledStyle();
                break;
            case self::DAEMON_STYLE_SINGLE_SYNCHRONIZED:
            default:
                $this->loopWithSingleSynchronizedStyle();
                break;
        }
    }

    protected function loopWithSingleSynchronizedStyle()
    {
        while (true) {
            if ($this->delegate->shouldTerminate()) {
                break;
            }
            if (!$this->delegate->isRunnable()) {
                $this->delegate->whenLoopShouldNotRun();
                continue;
            }
            $nextTask = $this->delegate->checkNextTask();
            if ($nextTask === false) {
                $this->delegate->whenNoTaskToDo();
                continue;
            }

            if (!$nextTask->beforeExecute()) {
                $this->delegate->whenTaskNotExecutable($nextTask);
                continue;
            }

            try {
                $this->delegate->whenToExecuteTask($nextTask);
                $nextTask->execute();
                $this->delegate->whenTaskExecuted($nextTask);
            } catch (\Exception $exception) {
                $this->delegate->whenTaskRaisedException($nextTask, $exception);
            }

        }
        $this->delegate->whenLoopTerminates();
    }

    protected function loopWithSinglePooledStyle()
    {
        while (true) {
            if ($this->delegate->shouldTerminate()) {
                break;
            }
            for ($i = 0; $i < $this->childrenCount; $i++) {
                // pcntl_wait() returns the process ID of the child which exited,
                // -1 on error
                // or zero if WNOHANG was provided as an option (on wait3-available systems) and no child was available.
                $exitedChildProcessID = pcntl_wait($status, WNOHANG | WUNTRACED);
                if ($exitedChildProcessID > 0) {
                    $this->childrenCount--;
                    $this->delegate->whenChildProcessConfirmedDead($exitedChildProcessID);
                } elseif ($exitedChildProcessID === -1) {
                    $pcntl_error_number = pcntl_get_last_error();
                    $pcntl_error_string = pcntl_strerror($pcntl_error_number);
                    $error_message = 'Loop could not wait a child process to stop. Error No:' . $pcntl_error_number . " Message:" . $pcntl_error_string;
                    $this->delegate->whenLoopReportError($error_message);
                    break;
                } else {
                    break;
                }
            }
            if ($this->childrenCount >= $this->delegate->maxChildProcessCountForSinglePooledStyle()) {
                $this->delegate->whenPoolIsFull();
                if ($this->delegate->shouldWaitForAnyWorkerDone()) {
                    $exitedChildProcessID = pcntl_wait($status);
                    if ($exitedChildProcessID > 0) {
                        $this->childrenCount--;
                        $this->delegate->whenChildProcessConfirmedDead($exitedChildProcessID);
                    } elseif ($exitedChildProcessID === -1) {
                        $pcntl_error_number = pcntl_get_last_error();
                        $pcntl_error_string = pcntl_strerror($pcntl_error_number);
                        $error_message = 'Loop could not wait a child process to stop. Error No:' . $pcntl_error_number . " Message:" . $pcntl_error_string;
                        $this->delegate->whenLoopReportError($error_message);
                    }
                }
                continue;
            }
            if (!$this->delegate->isRunnable()) {
                $this->delegate->whenLoopShouldNotRun();
                continue;
            }
            $nextTask = $this->delegate->checkNextTask();
            if ($nextTask === false) {
                $this->delegate->whenNoTaskToDo();
                continue;
            }

            // since @0.2.0 it is executed before fork
            if (!$nextTask->beforeExecute()) {
                $this->delegate->whenTaskNotExecutable($nextTask);
                continue;
            }

            $this->delegate->beforeFork();
            $childProcessID = pcntl_fork();
            if ($childProcessID == -1) {
                $pcntl_error_number = pcntl_get_last_error();
                $pcntl_error_string = pcntl_strerror($pcntl_error_number);
                $error_message = 'Loop could not fork a child process to execute task. Error No:' . $pcntl_error_number . " Message:" . $pcntl_error_string;
                $this->delegate->whenLoopReportError($error_message);
                $this->delegate->whenTaskRaisedException($nextTask, new \Exception($error_message));
            } else if ($childProcessID) {
                // we are the parent
                $this->childrenCount++;
                $this->delegate->whenChildProcessForked($childProcessID, "For task " . $nextTask->getTaskReference());
            } else {
                // we are the child
                $this->delegate->markThisProcessAsWorker();
                try {
                    $this->delegate->whenToExecuteTask($nextTask);
                    $nextTask->execute();
                    $this->delegate->whenTaskExecuted($nextTask);
                } catch (\Exception $exception) {
                    $this->delegate->whenTaskRaisedException($nextTask, $exception);
                }

                // Lord, now lettest thou thy servant depart in peace, according to thy word: (Luke 2:29, KJV)
                exit(0);
            }

        }
        $this->delegate->whenLoopTerminates();
    }
}