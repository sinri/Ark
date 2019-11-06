<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 13:48
 */

namespace sinri\ark\web;

use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\io\ArkWebInput;

/**
 * Interface ArkRouterRule
 * @package sinri\ark\web
 * @since 1.5.0 as interface
 * @since 2.9.0 became abstract class
 */
abstract class ArkRouterRule
{
    /**
     * @var string[] ArkRequestFilter class name list
     */
    protected $filters;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var callable|string[]
     */
    protected $callback;
    /**
     * @var string
     */
    protected $namespace;
    /**
     * @var string[]
     */
    protected $parsed;

    public function __construct()
    {
        $this->method = ArkWebInput::METHOD_ANY;
        $this->path = '';
        $this->callback = function () {
        };
        $this->namespace = '';
        $this->parsed = [];
        $this->filters = [];
    }

    /**
     * @return string[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param string[] $filters
     * @return ArkRouterRule
     */
    public function setFilters(array $filters): ArkRouterRule
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return ArkRouterRule
     */
    public function setMethod(string $method): ArkRouterRule
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return ArkRouterRule
     */
    public function setPath(string $path): ArkRouterRule
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return callable|string[]
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param callable|string[] $callback
     * @return ArkRouterRule
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return ArkRouterRule
     */
    public function setNamespace(string $namespace): ArkRouterRule
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getParsed(): array
    {
        return $this->parsed;
    }

    /**
     * @param string[] $parsed
     * @return ArkRouterRule
     */
    public function setParsed(array $parsed): ArkRouterRule
    {
        $this->parsed = $parsed;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode([
            "method" => $this->method,
            "path" => $this->path,
            "callback" => $this->callback,
            "filters" => $this->filters,
            "namespace" => $this->namespace,
            "parsed" => $this->parsed,
        ]);
    }

    /**
     * @param string $className class name with full namespace; use X::CLASS is recommended.
     * @param string $methodName
     */
    public function setCallbackWithClassNameAndMethod($className, $methodName)
    {
        $this->callback = self::buildCallbackDescriptionWithClassNameAndMethod($className, $methodName);
    }

    public static function buildCallbackDescriptionWithClassNameAndMethod($className, $methodName)
    {
        return [$className, $methodName];
    }

    /**
     * @param string $method
     * @param string $path
     * @param callable|string[] $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @return ArkRouterRule
     */
    abstract public static function buildRouteRule($method, $path, $callback, $filters = []);

    /**
     * @param $path_string
     * @param array|mixed $preparedData @since 1.1 this became reference and bug fixed
     * @param int $responseCode @since 1.1 this became reference
     * @throws Exception
     */
    public function execute($path_string, &$preparedData = [], &$responseCode = 200)
    {
        $callable = $this->getCallback();//ArkHelper::readTarget($route, ArkRouter::ROUTE_PARAM_CALLBACK);
        $params = $this->getParsed();//ArkHelper::readTarget($route, ArkRouter::ROUTE_PARSED_PARAMETERS);
        $filter_chain = $this->getFilters();//ArkHelper::readTarget($route, ArkRouter::ROUTE_PARAM_FILTER);

        self::executeWithFilters($params, $filter_chain, $path_string, $preparedData, $responseCode);
        self::executeWithParameters($callable, $params);
    }

    /**
     * @param $params
     * @param $filter_chain
     * @param $path_string
     * @param array $preparedData
     * @param int $responseCode
     * @throws Exception
     */
    protected static function executeWithFilters($params, $filter_chain, $path_string, &$preparedData = [], &$responseCode = 200)
    {
        if (!is_array($filter_chain)) {
            $filter_chain = [$filter_chain];
        }
        foreach ($filter_chain as $filter) {
            $filter_instance = ArkRequestFilter::makeInstance($filter);
            $shouldAcceptRequest = $filter_instance->shouldAcceptRequest(
                $path_string,
                Ark()->webInput()->getRequestMethod(),
                $params,
                $preparedData,
                $responseCode,
                $filterError
            );
            if (!$shouldAcceptRequest) {
                throw new Exception(
                    "Your request is rejected by [" . $filter_instance->filterTitle() . "], reason: " . $filterError,
                    $responseCode
                );
            }
        }
    }

    /**
     * @param $callable
     * @param $params
     * @throws Exception
     */
    protected static function executeWithParameters($callable, $params)
    {
        if (is_array($callable)) {
            if (count($callable) < 2) {
                throw new Exception("Callback Array Format Mistakes", (ArkHelper::isCLI() ? -1 : 500));
            }
            $class_instance_name = $callable[0];
            $class_instance = new $class_instance_name();

            $callable[0] = $class_instance;
        }
        call_user_func_array($callable, $params);
    }

    /**
     * @return string
     */
    abstract public function getType();

    protected function preprocessIncomingPath($incomingPath)
    {
        $path = $incomingPath;// as is for static
        if (strlen($incomingPath) > 1 && substr($incomingPath, strlen($incomingPath) - 1, 1) == '/') {
            $path = substr($incomingPath, 0, strlen($incomingPath) - 1);// this should be cut for non-static route rule
        } elseif ($incomingPath == '') {
            $path = '/'; // fulfill as no leading `/`
        }
        return $path;
    }

    protected function checkIfMatchMethod($method)
    {
        $route_method = $this->getMethod();

        if (
            $route_method !== ArkWebInput::METHOD_ANY
            && stripos($route_method, $method) === false
        ) {
//            if ($this->debug) {
//                $this->logger->debug(__METHOD__ . '@' . __LINE__ . " ROUTE METHOD NOT MATCH [$method]");
//            }
            return false;
        }

        return true;
    }

    /**
     * @param $method
     * @param $incomingPath
     * @return boolean
     */
    public function checkIfMatchRequest($method, $incomingPath)
    {
        if (!$this->checkIfMatchMethod($method)) return false;
        $path = $this->preprocessIncomingPath($incomingPath);
        $route_regex = $this->getPath();

        if (preg_match($route_regex, $path, $matches)) {
            if (!empty($matches)) array_shift($matches);
            $matches = array_filter($matches, function ($v) {
                return substr($v, 0, 1) != '/';
            });
            $matches = array_values($matches);
            array_walk($matches, function (&$v) {
                $v = urldecode($v);
            });
            $this->setParsed($matches);
//            if ($this->debug) {
//                $this->logger->debug(__METHOD__ . '@' . __LINE__ . " MATCHED with " . json_encode($matches));
//            }
            return true;
        }

        return false;
    }
}