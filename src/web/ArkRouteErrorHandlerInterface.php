<?php


namespace sinri\ark\web;


interface ArkRouteErrorHandlerInterface
{
    public function execute($errorData = [], $http_code = 404);

}