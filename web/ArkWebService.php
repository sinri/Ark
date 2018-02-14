<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 17:51
 */

namespace sinri\ark\web;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;

class ArkWebService
{
    /**
     * @var bool
     */
    protected $debug;
    /**
     * @var ArkLogger
     */
    protected $logger;
    /**
     * @var ArkRouter
     */
    protected $router;

    /**
     * @return ArkRouter
     */
    public function getRouter(): ArkRouter
    {
        return $this->router;
    }

    /**
     * @var string
     */
    protected $gateway;

    protected $filterGeneratedData;

    /**
     * @return null
     */
    public function getFilterGeneratedData()
    {
        return $this->filterGeneratedData;
    }

    public function __construct()
    {
        $this->gateway = "index.php";
        $this->logger = ArkLogger::makeSilentLogger();
        $this->debug = false;
        $this->router = new ArkRouter();
        $this->filterGeneratedData = null;
    }

    /**
     * @param string $sessionDir
     */
    public function startPHPSession($sessionDir)
    {
        ArkWebSession::sessionStart($sessionDir);
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param string $gateway
     */
    public function setGateway(string $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param ArkLogger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function handleRequest()
    {
        if (ArkHelper::isCLI()) {
            $this->handleRequestForCLI();
            return;
        }
        $this->handleRequestForWeb();
    }

    public function handleRequestForCLI()
    {
        global $argc;
        global $argv;
        try {
            // php index.php [PATH] [ARGV]
            $path = ArkHelper::readTarget($argv, 1, null);
            if ($path === null) {
                $this->logger->error("PATH EMPTY", [$path]);
                return;
            }
            $arguments = [];
            for ($i = 2; $i < $argc; $i++) {
                $arguments[] = $argv[$i];
            }
            $route = $this->router->seekRoute($path, Ark()->webInput()->requestMethod());
            $callable = ArkHelper::readTarget($route, ArkRouter::ROUTE_PARAM_CALLBACK);
            $filter_chain = ArkHelper::readTarget($route, ArkRouter::ROUTE_PARAM_FILTER);

            if (!is_array($filter_chain)) {
                $filter_chain = [$filter_chain];
            }
            $preparedData = null;
            foreach ($filter_chain as $filter) {
                $filter_instance = ArkRequestFilter::makeInstance($filter);
                $shouldAcceptRequest = $filter_instance->shouldAcceptRequest(
                    $path,
                    Ark()->webInput()->requestMethod(),
                    $arguments,
                    $this->filterGeneratedData,
                    $responseCode,
                    $filterError
                );
                if (!$shouldAcceptRequest) {
                    //header('HTTP/1.0 403 Forbidden');
                    throw new \Exception("Rejected by Filter " . $filter . ". " . $filterError, $responseCode);
                }
            }

            if (is_array($callable) && isset($callable[0])) {
                $class_instance_name = $callable[0];
                $class_instance = new $class_instance_name();

                $callable[0] = $class_instance;
            }
            call_user_func_array($callable, $arguments);
        } catch (\Exception $exception) {
            $this->logger->error("Exception in " . __METHOD__ . " : " . $exception->getMessage());
        }
    }

    public function handleRequestForWeb()
    {
        try {
            $responseCode = 200;

            $this->dividePath($path_string);
            $route = $this->router->seekRoute($path_string, Ark()->webInput()->requestMethod());
            $callable = ArkHelper::readTarget($route, ArkRouter::ROUTE_PARAM_CALLBACK);
            $params = ArkHelper::readTarget($route, ArkRouter::ROUTE_PARSED_PARAMETERS);
            $filter_chain = ArkHelper::readTarget($route, ArkRouter::ROUTE_PARAM_FILTER);

            if (!is_array($filter_chain)) {
                $filter_chain = [$filter_chain];
            }
            $preparedData = null;
            foreach ($filter_chain as $filter) {
                $filter_instance = ArkRequestFilter::makeInstance($filter);
                $shouldAcceptRequest = $filter_instance->shouldAcceptRequest(
                    $path_string,
                    Ark()->webInput()->requestMethod(),
                    $params,
                    $this->filterGeneratedData,
                    $responseCode,
                    $filterError
                );
                if (!$shouldAcceptRequest) {
                    throw new \Exception(
                        "Your request is rejected by [" . $filter_instance->filterTitle() . "], reason: " . $filterError,
                        $responseCode
                    );
                }
            }

            if (is_array($callable) && isset($callable[0])) {
                $class_instance_name = $callable[0];
                $class_instance = new $class_instance_name();

                $callable[0] = $class_instance;
            }
            call_user_func_array($callable, $params);
        } catch (\Exception $exception) {
            $this->router->handleRouteError(
                $exception->getMessage(),
                $exception->getCode()
            );
            if ($this->debug) {
                echo "<pre>" . PHP_EOL;
                print_r($exception);
                echo "</pre>" . PHP_EOL;
            }
        }
    }

    protected function dividePath(&$pathString = '')
    {
        $sub_paths = array();
        if (ArkHelper::isCLI()) {
            global $argv;
            global $argc;
            for ($i = 1; $i < $argc; $i++) {
                $sub_paths[] = $argv[$i];
            }
            return $sub_paths;
        }

        $fullPathString = $this->fetchControllerPathString();
        $tmp = explode('?', $fullPathString);
        $pathString = isset($tmp[0]) ? $tmp[0] : '';
        $pattern = '/^\/([^\?]*)(\?|$)/';
        $r = preg_match($pattern, $pathString, $matches);
        if (!$r) {
            // https://github.com/sinri/enoch/issues/1
            // this bug (return '' which is not an array) fixed since v1.0.2
            return [''];
        }
        $controller_array = explode('/', $matches[1]);
        if (count($controller_array) > 0) {
            $sub_paths = array_filter($controller_array, function ($var) {
                return $var !== '';
            });
            $sub_paths = array_values($sub_paths);
        }

        return $sub_paths;
    }

    protected function fetchControllerPathString()
    {
        $prefix = $_SERVER['SCRIPT_NAME'];
        //$delta=10;//original
        $delta = strlen($this->gateway) + 1;

        if (
            (strpos($_SERVER['REQUEST_URI'], $prefix) !== 0)
            && (strrpos($prefix, '/' . $this->gateway) + $delta == strlen($prefix))
        ) {
            $prefix = substr($prefix, 0, strlen($prefix) - $delta);
        }

        return substr($_SERVER['REQUEST_URI'], strlen($prefix));
    }

    public function startSession($sessionDir)
    {
        ArkWebSession::sessionStart($sessionDir);
    }
}