<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 13:48
 */

namespace sinri\ark\web;

/**
 * Interface ArkRouterRule
 * @package sinri\ark\web
 * @since 1.5.0
 */
interface ArkRouterRule
{
    /**
     * @param string $method
     * @param string $path
     * @param callable|string[] $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @return ArkRouterRule
     */
    public static function buildRouteRule($method, $path, $callback, $filters = []);

    /**
     * @param $path_string
     * @param array|mixed $preparedData @since 1.1 this became reference and bug fixed
     * @param int $responseCode @since 1.1 this became reference
     */
    public function execute($path_string, &$preparedData = [], &$responseCode = 200);
}