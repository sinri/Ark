<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 16:45
 */

namespace sinri\ark\core;


use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class ArkLogger extends AbstractLogger
{
    protected $targetLogDir = null;
    protected $prefix = 'enoch';
    protected $ignoreLevel;
    protected $cliUseSTDOUT = true;

    public function __construct($targetLogDir = null, $prefix = '', $cliUseSTDOUT = true)
    {
        $this->targetLogDir = $targetLogDir;
        $this->prefix = $prefix;
        $this->ignoreLevel = LogLevel::DEBUG;
        $this->cliUseSTDOUT = $cliUseSTDOUT;
    }

    /**
     * @param null $targetLogDir
     */
    public function setTargetLogDir($targetLogDir)
    {
        $this->targetLogDir = $targetLogDir;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param string $ignoreLevel
     */
    public function setIgnoreLevel(string $ignoreLevel)
    {
        $this->ignoreLevel = $ignoreLevel;
    }

    /**
     * @param bool $cliUseSTDOUT
     */
    public function setForceUseStandardOutputInCLI(bool $cliUseSTDOUT)
    {
        $this->cliUseSTDOUT = $cliUseSTDOUT;
    }

    /**
     * @param $level
     * @return bool
     */
    protected function shouldIgnoreThisLog($level)
    {
        static $levelValue = [
            LogLevel::EMERGENCY => 7,
            LogLevel::ALERT => 6,
            LogLevel::CRITICAL => 5,
            LogLevel::ERROR => 4,
            LogLevel::WARNING => 3,
            LogLevel::NOTICE => 2,
            LogLevel::INFO => 1,
            LogLevel::DEBUG => 0,
        ];
        $coming = ArkHelper::readTarget($levelValue, $level, 1);
        $limit = ArkHelper::readTarget($levelValue, $this->ignoreLevel, 0);
        if ($coming < $limit) {
            return true;
        }
        return false;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->shouldIgnoreThisLog($level)) {
            return;
        }
        $msg = $this->generateLog($level, $message, $context);
        $target_file = $this->decideTargetFile();
        if (!$target_file) {
            echo $msg;
            return;
        }
        @file_put_contents($target_file, $msg, FILE_APPEND);
    }

    /**
     * Return the string format log content
     * @param $level
     * @param $message
     * @param string $object
     * @return string
     */
    protected function generateLog($level, $message, $object = '')
    {
        $now = date('Y-m-d H:i:s');
        $level_string = "[{$level}]";

        $log = "{$now} {$level_string} {$message} |";
        $log .= is_string($object) ? $object : json_encode($object, JSON_UNESCAPED_UNICODE);
        $log .= PHP_EOL;

        return $log;
    }

    /**
     * Return the target file path which log would be written into.
     * If target log directory not set, return false.
     * @return bool|string
     */
    protected function decideTargetFile()
    {
        if (empty($this->targetLogDir)) {
            return false;
        }
        if ($this->cliUseSTDOUT && ArkHelper::isCLI()) {
            return false;
        }
        if (!file_exists($this->targetLogDir)) {
            @mkdir($this->targetLogDir, 0777, true);
        }
        $today = date('Y-m-d');
        return $this->targetLogDir . '/log-' . (empty($this->prefix) ? '' : $this->prefix . '-') . $today . '.log';
    }
}