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

/**
 * Interface ArkRouterRule
 * @package sinri\ark\web
 * @since 1.5.0 as interface
 * @since 2.9.0 became abstract class
 */
abstract class ArkRouterRule
{
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
}