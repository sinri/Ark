<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/21
 * Time: 15:17
 */

namespace sinri\ark\queue\daemon;

use sinri\ark\queue\QueueTask;


/**
 * Class QueueDaemonDelegate
 * NOTE: ALL THE METHODS OF THIS CLASS SHOULD NOT THROW EXCEPTIONS. ALL THE LOOP WOULD NOT BE CONTROLLED.
 * @package leqee\phpqueuekit\daemon
 */
abstract class QueueDaemonDelegate
{
    const QUEUE_RUNTIME_COMMAND_PAUSE = "PAUSE";
    const QUEUE_RUNTIME_COMMAND_CONTINUE = "CONTINUE";
    const QUEUE_RUNTIME_COMMAND_STOP = "STOP";
    const QUEUE_RUNTIME_COMMAND_FORCE_STOP = "FORCE-STOP";
    const QUEUE_RUNTIME_COMMAND_RESTART = "RESTART";
    const QUEUE_RUNTIME_COMMAND_FORCE_RESTART = "FORCE-RESTART";

    /**
     * QueueDaemon constructor.
     * To make it more smooth to extend the config class, removed the config property definition.
     * @param QueueDaemonConfiguration $config
     */
    abstract public function __construct($config);

    /**
     * @return string
     */
    abstract public function getDaemonStyle();

    /**
     * @param string $error
     */
    abstract public function whenLoopReportError($error);

    /**
     * If not runnable, the daemon loop would sleep.
     * @return bool
     */
    abstract public function isRunnable();

    /**
     * Tell daemon loop to exit.
     * @return bool
     */
    abstract public function shouldTerminate();

    /**
     * When the loop gets ready to terminate by shouldTerminate instructed, execute this
     */
    public function whenLoopTerminates()
    {
        // do nothing by default, you can write some logs here
    }

    /**
     * Sleep for a certain while.
     * @return void
     */
    abstract public function whenLoopShouldNotRun();

    /**
     * @return QueueTask|false
     */
    abstract public function checkNextTask();

    /**
     * When the loop cannot check for a task to do next, execute this
     */
    abstract public function whenNoTaskToDo();

    /**
     * @since 0.2.0 this is done before fork in pooled style
     * @param QueueTask $task
     */
    abstract public function whenTaskNotExecutable($task);

    /**
     *
     * @param QueueTask $task
     */
    abstract public function whenToExecuteTask($task);

    /**
     * @param QueueTask $task
     */
    abstract public function whenTaskExecuted($task);

    /**
     * @param QueueTask $task
     * @param \Exception $exception
     */
    abstract public function whenTaskRaisedException($task, $exception);

    // for pooled style daemon

    /**
     * The daemon would fork child processes up to the certain number
     * @return int
     */
    public function maxChildProcessCountForSinglePooledStyle()
    {
        return 5;
    }

    /**
     * When a child process is forked
     * @param int $pid
     * @param string $note
     */
    abstract public function whenChildProcessForked($pid, $note = '');

    /**
     * When a child process is observed dead by WAIT function
     * @param int $pid
     */
    abstract public function whenChildProcessConfirmedDead($pid);

    /**
     * When the daemon has made the pool full of child processes to work
     * It is recommended to take a sleep here
     */
    abstract public function whenPoolIsFull();

    /**
     * 如果返回true，则在执行完whenPoolIsFull之后会进行阻塞wait子进程
     * @return bool
     */
    abstract public function shouldWaitForAnyWorkerDone();

    const PROCESS_TYPE_WORKER = "WORKER";

    protected $typeOfThisProcess = null;

    /**
     * When use worker process pool style, the worker progress should have chance to declare this identity.
     */
    public function markThisProcessAsWorker()
    {
        $this->typeOfThisProcess = self::PROCESS_TYPE_WORKER;
    }

    /**
     * You can close all opened DB connection here
     */
    abstract public function beforeFork();
}