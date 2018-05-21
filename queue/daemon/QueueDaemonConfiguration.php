<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/21
 * Time: 15:16
 */

namespace sinri\ark\queue\daemon;


abstract class QueueDaemonConfiguration
{
    /**
     * Decide in which style the daemon works, among the following constants:
     * QueueDaemon::DAEMON_STYLE_SINGLE_SYNCHRONIZED
     * @return string
     */
    abstract public function getDaemonStyle();

}