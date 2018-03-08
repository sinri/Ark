<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 14:15
 */

namespace sinri\ark;


use sinri\ark\cache\ArkCache;
use sinri\ark\cache\implement\ArkDummyCache;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\ark\database\pdo\ArkPDO;
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
     * @var ArkWebInput
     */
    protected $webInputHelper;
    /**
     * @var ArkWebOutput
     */
    protected $webOutputHelper;
    /**
     * @var ArkWebService
     */
    protected $webServiceHandler;
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

    private function __construct()
    {
        $this->webInputHelper = null;
        $this->webOutputHelper = null;
        $this->webServiceHandler = null;
    }

    /**
     * @return TheArk
     */
    public static function getInstance()
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
        if (!$this->webInputHelper) {
            $this->webInputHelper = new ArkWebInput();
        }
        return $this->webInputHelper;
    }

    /**
     * @return ArkWebOutput
     */
    public function webOutput(): ArkWebOutput
    {
        if (!$this->webOutputHelper) {
            $this->webOutputHelper = new ArkWebOutput();
        }
        return $this->webOutputHelper;
    }

    /**
     * @return ArkWebService
     */
    public function webService(): ArkWebService
    {
        if (!$this->webServiceHandler) {
            $this->webServiceHandler = new ArkWebService();
        }
        return $this->webServiceHandler;
    }

    /**
     * @param string $name
     * @param ArkLogger $logger
     */
    public function registerLogger($name, ArkLogger $logger)
    {
        ArkHelper::writeIntoArray($this->loggerDict, $name, $logger);
    }

    /**
     * @param string $name
     * @return ArkLogger
     */
    public function logger($name = 'Ark'): ArkLogger
    {
        $logger = ArkHelper::readTarget($this->loggerDict, $name);
        if (!$logger) {
            $logger = ArkLogger::makeSilentLogger();
            $this->registerLogger($name, $logger);
        }
        return $logger;
    }

    /**
     * @param string $name
     * @param ArkPDO $pdo
     */
    public function registerDb($name, $pdo)
    {
        ArkHelper::writeIntoArray($this->pdoDict, $name, $pdo);
    }

    /**
     * @param string $name
     * @return ArkPDO
     */
    public function db($name = 'default'): ArkPDO
    {
        $pdo = ArkHelper::readTarget($this->pdoDict, $name);
        if (!$pdo) {
            $pdo = new ArkPDO();
            $this->registerDb($name, $pdo);
        }
        return $pdo;
    }

    /**
     * @param string $name
     * @param ArkCache $cache
     */
    public function registerCache($name, $cache)
    {
        ArkHelper::writeIntoArray($this->cacheDict, $name, $cache);
    }

    /**
     * @param string $name
     * @return ArkCache
     */
    public function cache($name = 'default'): ArkCache
    {
        $cache = ArkHelper::readTarget($this->cacheDict, $name);
        if (!$cache) {
            $cache = new ArkDummyCache();
            $this->registerCache($name, $cache);
        }
        return $cache;
    }
}