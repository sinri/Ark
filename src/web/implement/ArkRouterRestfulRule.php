<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/3/5
 * Time: 21:51
 */

namespace sinri\ark\web\implement;


use sinri\ark\web\ArkRouterRule;

/**
 * Class ArkRouterRestfulRule
 * @package sinri\ark\web\implement
 * @since 2.10 moved here
 */
class ArkRouterRestfulRule extends ArkRouterRule
{
    /**
     * Designed after Lumen Routing: https://lumen.laravel-china.org/docs/5.3/routing
     * @param string[] $methods use method constants of ArkWebInput
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|string[] $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     */
    public function __construct($methods, $path, $callback, $filters = [])
    {
        parent::__construct();

        $path = preg_replace('/\//', '\/', $path);
        $matched = preg_match_all('/{([^\/]+)}/', $path, $matches);
        if ($matched) {
            $regex = preg_replace('/{([^\/]+)}/', '([^\/]+)', $path);
        } else {
            $regex = $path;
        }
        $regex = '/^\/' . $regex . '$/';
        $this->setMethods($methods);
        $this->setPath($regex);
        $this->setCallback($callback);
        $this->setFilters($filters);
    }

    /**
     * Designed after Lumen Routing: https://lumen.laravel-china.org/docs/5.3/routing
     * @param string[] $methods use method constants of ArkWebInput
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|string[] $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @return ArkRouterRestfulRule
     * @deprecated since 3.1.0
     */
    public static function buildRouteRule($methods, $path, $callback, $filters = [])
    {
        return new ArkRouterRestfulRule($methods, $path, $callback, $filters);

//        $path = preg_replace('/\//', '\/', $path);
//        $matched = preg_match_all('/{([^\/]+)}/', $path, $matches);
//        if ($matched) {
//            $regex = preg_replace('/{([^\/]+)}/', '([^\/]+)', $path);
//        } else {
//            $regex = $path;
//        }
//        $regex = '/^\/' . $regex . '$/';
//        $new_route = new ArkRouterRestfulRule();
//        $new_route->setMethod($method);
//        $new_route->setPath($regex);
//        $new_route->setCallback($callback);
//        $new_route->setFilters($filters);
//
//        return $new_route;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return "ArkRouterRestfulRule";
    }
}