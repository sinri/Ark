<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 13:24
 */

namespace sinri\ark\web\implement;

use sinri\ark\web\ArkRouterRule;

/**
 * Class ArkRouterStaticRule
 * @package sinri\ark\web
 * @since 1.5.0
 * @since 2.10 moved here
 */
class ArkRouterStaticRule extends ArkRouterRule
{

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

    /**
     * @return string
     */
    public function getType()
    {
        return "ArkRouterStaticRule";
    }

    protected function preprocessIncomingPath($incomingPath)
    {
        return $incomingPath;
    }
}