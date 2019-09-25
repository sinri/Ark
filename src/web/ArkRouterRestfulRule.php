<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/3/5
 * Time: 21:51
 */

namespace sinri\ark\web;


use sinri\ark\io\ArkWebInput;

class ArkRouterRestfulRule extends ArkRouterRule
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
        $matched = preg_match_all('/{([^\/]+)}/', $path, $matches);
        if ($matched) {
            $regex = preg_replace('/{([^\/]+)}/', '([^\/]+)', $path);
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
}