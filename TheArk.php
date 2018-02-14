<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 14:15
 */

namespace sinri\ark;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\ark\io\WebInputHelper;
use sinri\ark\io\WebOutputHelper;
use sinri\ark\web\ArkWebService;

class TheArk
{
    /**
     * @var TheArk
     */
    private static $instance;
    /**
     * @var WebInputHelper
     */
    protected $webInputHelper;
    /**
     * @var WebOutputHelper
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

    private function __construct()
    {
        $this->webInputHelper = new WebInputHelper();
        $this->webOutputHelper = new WebOutputHelper();
        $this->webServiceHandler = new ArkWebService();
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
     * @return WebInputHelper
     */
    public function webInput()
    {
        return $this->webInputHelper;
    }

    /**
     * @return WebOutputHelper
     */
    public function webOutput()
    {
        return $this->webOutputHelper;
    }

    /**
     * @return ArkWebService
     */
    public function webService()
    {
        return $this->webServiceHandler;
    }

    public function registerLogger($name, $logger)
    {
        $this->loggerDict[$name] = $logger;
    }

    public function logger($name = null)
    {
        return ArkHelper::readTarget($this->loggerDict, $name, ArkLogger::makeSilentLogger());
    }
}