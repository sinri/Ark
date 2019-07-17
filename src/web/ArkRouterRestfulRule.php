<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/3/5
 * Time: 21:51
 */

namespace sinri\ark\web;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\io\ArkWebInput;

class ArkRouterRestfulRule implements ArkRouterRule
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
     * Designed after Lumen Routing: https://lumen.laravel-china.org/docs/5.3/routing
     * @param string $method use method constants of ArkWebInput
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|string[] $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @return ArkRouterRestfulRule
     */
    public static function buildRouteRule($method, $path, $callback, $filters = [])
    {
        $path = preg_replace('/\//', '\/', $path);
        $matched = preg_match_all('/\{([^\/]+)\}/', $path, $matches);
        if ($matched) {
            $regex = preg_replace('/\{([^\/]+)\}/', '([^\/]+)', $path);
        } else {
            $regex = $path;
        }
        $regex = '/^\/' . $regex . '$/';
        $new_route = new ArkRouterRestfulRule();
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
}