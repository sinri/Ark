<?php


namespace sinri\ark\web;


use Exception;
use sinri\ark\io\ArkWebInput;

class ArkRouterFreeTailRule extends ArkRouterRule
{

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
     * @var string[]
     */
    protected $filters;
    /**
     * @var string
     */
    protected $namespace;
    /**
     * @var string[]
     */
    protected $parsed;
    /**
     * @var int
     */
    protected $headComponentsCount;

    public function __construct()
    {
        $this->method = ArkWebInput::METHOD_ANY;
        $this->path = '';
        $this->callback = function () {
        };
        $this->filters = [];
        $this->namespace = '';
        $this->parsed = [];
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
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
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
     */
    public function setPath(string $path)
    {
        $this->path = $path;
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
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param string $className class name with full namespace; use X::CLASS is recommended.
     * @param string $methodName
     */
    public function setCallbackWithClassNameAndMethod($className, $methodName)
    {
        $this->callback = self::buildCallbackDescriptionWithClassNameAndMethod($className, $methodName);
    }

    /**
     * @return string[] an array of ArkRequestFilter class names
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param string[] $filters an array of ArkRequestFilter class names
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
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
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
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
     */
    public function setParsed(array $parsed)
    {
        $this->parsed = $parsed;
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

    public static function buildCallbackDescriptionWithClassNameAndMethod($className, $methodName)
    {
        return [$className, $methodName];
    }

    /**
     * @param string $method
     * @param string $path the leading components
     * @param callable|string[] $callback a function with parameters in path, such as `function($p1,$p2,$tailComponents)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @return ArkRouterFreeTailRule
     */
    public static function buildRouteRule($method, $path, $callback, $filters = [])
    {
        $new_route = new ArkRouterFreeTailRule();

        $path = preg_replace('/\//', '\/', $path);
        $matched = preg_match_all('/{([^\/]+)}/', $path, $matches);
        if ($matched) {
            $regex = preg_replace('/{([^\/]+)}/', '([^\/]+)', $path);
            $new_route->headComponentsCount = count($matches[0]);
        } else {
            $regex = $path;
            $new_route->headComponentsCount = 0;
        }
        $regex = '/^\/' . $regex . '\/?(.*)$/';

        $new_route->setMethod($method);
        $new_route->setPath($regex);
        $new_route->setCallback($callback);
        $new_route->setFilters($filters);

        return $new_route;
    }

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

        // process tail
        if (count($params) > $this->headComponentsCount) {
            $tail = [];
            for ($i = $this->headComponentsCount; $i < count($params); $i++) {
                $items = explode('/', $params[$i]);
                $tail = array_merge($tail, $items);
            }
            array_splice($params, $this->headComponentsCount, count($params) - $this->headComponentsCount);
            $params[] = $tail;
        }

        self::executeWithFilters($params, $filter_chain, $path_string, $preparedData, $responseCode);
        self::executeWithParameters($callable, $params);
    }
}