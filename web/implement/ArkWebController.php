<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 21:37
 */

namespace sinri\ark\web\implement;


use sinri\ark\io\ArkWebOutput;

class ArkWebController
{

    protected $shouldSendJsonHeader = true;
    protected $filterGeneratedData;

    public function __construct()
    {
        // If you want to track a request, use
        // $request_serial = Ark()->webService()->getRequestSerial();

        // You might process filters-generated-data here
        $this->filterGeneratedData = Ark()->webService()->getFilterGeneratedData();
    }

    protected function _sayOK($data = "", $httpCode = 200)
    {
        if ($this->shouldSendJsonHeader) {
            Ark()->webOutput()->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON);
        }
        http_response_code($httpCode);
        Ark()->webOutput()->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_OK, $data);
    }

    protected function _sayFail($error = "", $httpCode = 200)
    {
        if ($this->shouldSendJsonHeader) {
            Ark()->webOutput()->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON);
        }
        http_response_code($httpCode);
        Ark()->webOutput()->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_FAIL, $error);
    }

    protected function _showPage($templateFile, $params = [], $httpCode = 200)
    {
        http_response_code($httpCode);
        try {
            Ark()->webOutput()->displayPage($templateFile, $params);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}