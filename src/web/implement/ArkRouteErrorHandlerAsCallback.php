<?php


namespace sinri\ark\web\implement;


use sinri\ark\web\ArkRouteErrorHandlerInterface;

abstract class ArkRouteErrorHandlerAsCallback implements ArkRouteErrorHandlerInterface
{

    /**
     * @param mixed $errorMessage
     * @param int $httpCode
     */
    abstract public function requestErrorCallback($errorMessage, $httpCode);

    public function execute($errorData = [], $http_code = 404)
    {
        $this->requestErrorCallback($errorData, $http_code);
    }
}