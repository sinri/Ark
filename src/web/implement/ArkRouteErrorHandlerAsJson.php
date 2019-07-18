<?php


namespace sinri\ark\web\implement;


use sinri\ark\io\ArkWebOutput;
use sinri\ark\web\ArkRouteErrorHandlerInterface;

class ArkRouteErrorHandlerAsJson implements ArkRouteErrorHandlerInterface
{

    public function execute($errorData = [], $http_code = 404)
    {
        Ark()->webOutput()
            ->sendHTTPCode($http_code)
            ->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON)
            ->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_FAIL, $errorData);
    }
}