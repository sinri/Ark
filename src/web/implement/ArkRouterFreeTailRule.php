<?php


namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\web\ArkRouterRule;

/**
 * Class ArkRouterFreeTailRule
 * @package sinri\ark\web\implement
 * @since 2.7
 * @since 2.10 moved here
 */
class ArkRouterFreeTailRule extends ArkRouterRule
{
    /**
     * @var int
     */
    protected $headComponentsCount;

    /**
     * @param string $method
     * @param string $path the leading components
     * @param callable|string[] $callback a function with parameters in path, such as `function($p1,$p2,$tailComponents)` for above
     * @param string[] $filters ArkRequestFilter class name list
     */
    public function __construct($method, $path, $callback, $filters = [])
    {
        parent::__construct();

        $path = preg_replace('/\//', '\/', $path);
        $matched = preg_match_all('/{([^\/]+)}/', $path, $matches);
        if ($matched) {
            $regex = preg_replace('/{([^\/]+)}/', '([^\/]+)', $path);
            $this->headComponentsCount = count($matches[0]);
        } else {
            $regex = $path;
            $this->headComponentsCount = 0;
        }
        $regex = '/^\/' . $regex . '\/?(.*)$/';

        $this->setMethod($method);
        $this->setPath($regex);
        $this->setCallback($callback);
        $this->setFilters($filters);
    }

    /**
     * @param string $method
     * @param string $path the leading components
     * @param callable|string[] $callback a function with parameters in path, such as `function($p1,$p2,$tailComponents)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @return ArkRouterFreeTailRule
     * @deprecated since 3.1.0
     */
    public static function buildRouteRule($method, $path, $callback, $filters = [])
    {
        return new ArkRouterFreeTailRule($method, $path, $callback, $filters);

//        $new_route = new ArkRouterFreeTailRule();
//
//        $path = preg_replace('/\//', '\/', $path);
//        $matched = preg_match_all('/{([^\/]+)}/', $path, $matches);
//        if ($matched) {
//            $regex = preg_replace('/{([^\/]+)}/', '([^\/]+)', $path);
//            $new_route->headComponentsCount = count($matches[0]);
//        } else {
//            $regex = $path;
//            $new_route->headComponentsCount = 0;
//        }
//        $regex = '/^\/' . $regex . '\/?(.*)$/';
//
//        $new_route->setMethod($method);
//        $new_route->setPath($regex);
//        $new_route->setCallback($callback);
//        $new_route->setFilters($filters);
//
//        return $new_route;
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

    /**
     * @return string
     */
    public function getType()
    {
        return "ArkRouterFreeTailRule";
    }
}