<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 14:15
 */

namespace sinri\ark;


use Psr\Log\LogLevel;
use sinri\ark\cache\ArkCache;
use sinri\ark\cache\implement\ArkDummyCache;
use sinri\ark\cache\implement\ArkFileCache;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\web\ArkWebService;

class TheArk
{
    /**
     * @var TheArk
     */
    private static $instance;
    /**
     * @var ArkLogger[]
     */
    protected $loggerDict = [];

    /**
     * @var ArkPDO[]
     */
    protected $pdoDict = [];
    /**
     * @var ArkCache[]
     */
    protected $cacheDict = [];
    /**
     * @var array
     */
    protected $config = [];

    private function __construct()
    {
    }

    /**
     * @param string $configFile It must be a PHP file with a $config inside
     * @return $this
     * @since 3.1.9
     */
    public function loadConfigFileWithPHPFormat(string $configFile): TheArk
    {
        $config = [];
        /** @noinspection PhpIncludeInspection */
        require $configFile;// where $config was defined inside
        $this->config = $config;
        return $this;
    }

    public function setConfig(array $config): TheArk
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param array $keyChain
     * @param mixed $default
     * @return mixed
     */
    public function readConfig(array $keyChain, $default = null)
    {
        return ArkHelper::readTarget($this->config, $keyChain, $default);
    }

    /**
     * @return TheArk
     */
    public static function getInstance(): TheArk
    {
        if (!self::$instance) {
            self::$instance = new TheArk();
        }
        return self::$instance;
    }

    /**
     * @return ArkWebInput
     */
    public function webInput(): ArkWebInput
    {
        return ArkWebInput::getSharedInstance();
    }

    /**
     * @return ArkWebOutput
     */
    public function webOutput(): ArkWebOutput
    {
        return ArkWebOutput::getSharedInstance();
    }

    /**
     * @return ArkWebService
     */
    public function webService(): ArkWebService
    {
        return ArkWebService::getSharedInstance();
    }

    /**
     * @param string $name
     * @param ArkLogger $logger
     */
    public function registerLogger(string $name, ArkLogger $logger)
    {
        ArkHelper::writeIntoArray($this->loggerDict, $name, $logger);
    }

    /**
     * Register an instance of ArkLogger based on the config item [log][path]
     *
     * @param string $name
     * @return ArkLogger
     */
    public function logger($name = 'Ark'): ArkLogger
    {
        $logger = ArkHelper::readTarget($this->loggerDict, $name);
        if (!$logger) {
            $path = $this->readConfig(['log', 'path']);
            if ($path !== null) {
                $logger = new ArkLogger($path, $name);
                $level = $this->readConfig(['log', 'level'], LogLevel::INFO);
                $logger->setIgnoreLevel($level);
                $logger->setGroupByPrefix(true);
            } else {
                $logger = ArkLogger::makeSilentLogger();
            }
            $this->registerLogger($name, $logger);
        }
        return $logger;
    }

    /**
     * @param string $name
     * @param ArkPDO $pdo
     */
    public function registerDb(string $name, ArkPDO $pdo)
    {
        ArkHelper::writeIntoArray($this->pdoDict, $name, $pdo);
    }

    /**
     * Register an instance of ArkPDO based on the config item [pdo][NAME]
     *
     * @param string $name
     * @param bool $shouldConnectFirst @since 2.8.2
     * @return ArkPDO
     *
     * @throws database\Exception\ArkPDOConfigError
     * @since 2.8.1
     */
    public function pdo($name = 'default', $shouldConnectFirst = true): ArkPDO
    {
        $pdo = ArkHelper::readTarget($this->pdoDict, $name);
        if (!$pdo) {
            $dbConfigDict = $this->readConfig(['pdo', $name]);
            if ($dbConfigDict === null) {
                $pdo = new ArkPDO();
            } else {
                $pdoConfig = new ArkPDOConfig($dbConfigDict);
                $pdo = new ArkPDO($pdoConfig);
                if ($shouldConnectFirst) $pdo->connect();
            }
            $this->registerDb($name, $pdo);
        }
        return $pdo;
    }

    /**
     * @param string $name
     * @param ArkCache $cache
     */
    public function registerCache(string $name, ArkCache $cache)
    {
        ArkHelper::writeIntoArray($this->cacheDict, $name, $cache);
    }

    /**
     * Register an instance of ArkFileCache based on the config item [cache][NAME][type|dir|mode]
     *
     * @param string $name
     * @return ArkCache
     */
    public function cache($name = 'default'): ArkCache
    {
        $cache = ArkHelper::readTarget($this->cacheDict, $name);
        if (!$cache) {
            $cacheType = $this->readConfig(['cache', $name, 'type']);
            switch ($cacheType) {
                case 'FILE':
                    $cache = new ArkFileCache(
                        $this->readConfig(['cache', $name, 'dir'], '/tmp/ark-cache-' . $name),
                        $this->readConfig(['cache', $name, 'mode'], 0777)
                    );
                    break;
                default:
                    $cache = new ArkDummyCache();
            }
            $this->registerCache($name, $cache);
        }
        return $cache;
    }

    /**
     * Quick Call Ark CLI Program Action
     * @param string $programClass the full class path for ArkCliProgram Class Definition
     * @param string $actionName the method without action prefix
     * @param array $params any params the program needs
     * @return bool|mixed
     * @since 2.8.0
     */
    public function runProgramInCLI(string $programClass, string $actionName, $params = []): bool
    {
        $actionName = "action" . $actionName;
        $callable = [$programClass, $actionName];
        if (!is_callable($callable)) {
            return false;
        }
        return call_user_func_array($callable, $params);
    }
}