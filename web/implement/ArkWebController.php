<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 21:37
 */

namespace sinri\ark\web\implement;


use sinri\ark\io\WebOutputHelper;

class ArkWebController
{

    protected $request_uuid;
    protected $shouldSendJsonHeader = true;
    protected $filterGeneratedData;

    public function __construct($initData = null)
    {
        $this->request_uuid = uniqid();

        // You might process filters-generated-data here
        $this->filterGeneratedData = Ark()->webService()->getFilterGeneratedData();
    }

    protected function _sayOK($data = "", $http_code = 200)
    {
        if ($this->shouldSendJsonHeader) {
            Ark()->webOutput()->setContentTypeHeader(WebOutputHelper::CONTENT_TYPE_JSON);
        }
        http_response_code($http_code);
        Ark()->webOutput()->jsonForAjax(WebOutputHelper::AJAX_JSON_CODE_OK, $data);
    }

    protected function _sayFail($error = "", $http_code = 200)
    {
        if ($this->shouldSendJsonHeader) {
            Ark()->webOutput()->setContentTypeHeader(WebOutputHelper::CONTENT_TYPE_JSON);
        }
        http_response_code($http_code);
        Ark()->webOutput()->jsonForAjax(WebOutputHelper::AJAX_JSON_CODE_FAIL, $error);
    }
}