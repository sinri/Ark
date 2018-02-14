<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 10:49
 */

namespace sinri\ark\test\web\filter;


use sinri\ark\core\ArkHelper;
use sinri\ark\web\ArkRequestFilter;

class AnotherFilter extends ArkRequestFilter
{

    /**
     * Check request data with $_REQUEST, $_SESSION, $_SERVER, etc.
     * And decide if the request should be accepted.
     * If return false, the request would be thrown.
     * You can pass anything into $preparedData, that controller might use it (not sure, by the realization)
     * @param $path
     * @param $method
     * @param $params
     * @param null $preparedData @since 1.3.6
     * @param int $responseCode @since 2.2.0
     * @param null $error @since 2.2.0
     * @return bool
     */
    public function shouldAcceptRequest($path, $method, $params, &$preparedData = null, &$responseCode = 200, &$error = null)
    {
        ArkHelper::writeIntoArray($preparedData, ['request_time'], time());
        return true;
    }

    /**
     * Give filter a name for Error Report
     * @return string
     */
    public function filterTitle()
    {
        return "Another Filter";
    }
}