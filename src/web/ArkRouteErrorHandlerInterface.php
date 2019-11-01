<?php


namespace sinri\ark\web;


interface ArkRouteErrorHandlerInterface
{
    /**
     * @param array $errorData
     * @param int $http_code
     * @return void
     */
    public function execute($errorData = [], $http_code = 404);

}