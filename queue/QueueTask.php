<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/21
 * Time: 15:15
 */

namespace sinri\ark\queue;


abstract class QueueTask
{
    /**
     * @var bool
     */
    protected $readyToExecute;
    /**
     * @var bool
     */
    protected $done;
    /**
     * @var string
     */
    protected $executeFeedback;
    /**
     * @var mixed
     */
    protected $executeResult;

    public function __construct()
    {
        $this->readyToExecute = false;
        $this->done = false;
        $this->executeFeedback = "Not Executed Yet";
        $this->executeResult = null;
    }

    /**
     * @return bool
     */
    public function isReadyToExecute()
    {
        return $this->readyToExecute;
    }

    /**
     * Fetch the unique reference of this task, such as TASK_ID
     * @since 0.1.2
     * @return int|string
     */
    abstract public function getTaskReference();

    /**
     * Fetch the type of this task
     * @since 0.1.7
     * @return string
     */
    abstract public function getTaskType();

    /**
     * @return bool
     */
    public function isDone()
    {
        return $this->done;
    }

    /**
     * @return string
     */
    public function getExecuteFeedback()
    {
        return $this->executeFeedback;
    }

    /**
     * @return mixed
     */
    public function getExecuteResult()
    {
        return $this->executeResult;
    }

    /**
     * To prepare and lock task before executing.
     * You should update property $readyToExecute as the result of this method
     * @return bool
     */
    abstract public function beforeExecute();

    /**
     * Execute a task then:
     * (1) store extra output data in property $executeResult
     * (2) give a feedback string in property $feedback
     * (3) give a boolean value in property $done and return
     * @return bool
     */
    abstract public function execute();
}