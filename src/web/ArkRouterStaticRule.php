<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 13:24
 */

namespace sinri\ark\web;

use sinri\ark\io\ArkWebInput;

/**
 * Class ArkRouterStaticRule
 * @package sinri\ark\web
 * @since 1.5.0
 */
class ArkRouterStaticRule extends ArkRouterRule
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
     * @var string[] ArkRequestFilter class name list
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
     * @return string[] ArkRequestFilter class name list
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param string[] $filters ArkRequestFilter class name list
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

    public static function buildCallbackDescriptionWithClassNameAndMethod($className, $methodName)
    {
        return [$className, $methodName];
    }

    /**
     * Designed after Lumen Routing: https://lumen.laravel-china.org/docs/5.3/routing
     * @param string $method use method constants of ArkWebInput
     * @param string $path `pre/fix` no leading `/` and tail `/`
     * @param callable|string[] $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param String[] $filters ArkRequestFilter class name list
     * @return ArkRouterStaticRule
     */
    public static function buildRouteRule($method, $path, $callback, $filters = [])
    {
        $path = preg_replace('/\//', '\/', $path);
        $regex = '/^\/' . $path . '\/(.*)$/';
        $new_route = new ArkRouterStaticRule();
        $new_route->setMethod($method);
        $new_route->setPath($regex);
        $new_route->setCallback($callback);
        $new_route->setFilters($filters);

        return $new_route;
    }
}