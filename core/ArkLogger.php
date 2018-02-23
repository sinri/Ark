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
    protected $prefix = 'ark';
    protected $ignoreLevel;
    protected $silent = false;

    public function __construct($targetLogDir = null, $prefix = '')
    {
        $this->targetLogDir = $targetLogDir;
        $this->setPrefix($prefix);
        $this->ignoreLevel = LogLevel::INFO;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix)
    {
        $prefix = preg_replace('/[^A-Za-z0-9]/', '_', $prefix);
        $this->prefix = $prefix;
    }

    /**
     * @return ArkLogger
     */
    public static function makeSilentLogger()
    {
        $logger = new ArkLogger();
        $logger->silent = true;
        return $logger;
    }

    /**
     * @param null $targetLogDir
     */
    public function setTargetLogDir($targetLogDir)
    {
        $this->targetLogDir = $targetLogDir;
    }

    /**
     * @param string $ignoreLevel
     */
    public function setIgnoreLevel(string $ignoreLevel)
    {
        $this->ignoreLevel = $ignoreLevel;
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
     * If you want to output log directly to STDOUT, use this.
     * @param $level
     * @param $message
     * @param array $context
     */
    public function echo($level, $message, array $context = array())
    {
        if ($this->shouldIgnoreThisLog($level)) {
            return;
        }
        $msg = $this->generateLog($level, $message, $context);
        echo $msg;
    }

    /**
     * @param $level
     * @return bool
     */
    protected function shouldIgnoreThisLog($level)
    {
        if ($this->silent) return true;
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
        if (!file_exists($this->targetLogDir)) {
            @mkdir($this->targetLogDir, 0777, true);
        }
        $today = date('Y-m-d');
        return $this->targetLogDir . '/log-' . (empty($this->prefix) ? '' : $this->prefix . '-') . $today . '.log';
    }
}