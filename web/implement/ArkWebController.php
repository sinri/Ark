<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 21:37
 */

namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\ArkWebOutput;

class ArkWebController
{

    protected $filterGeneratedData;

    /**
     * ArkWebController constructor.
     * You should process filters-generated-data here if needed,
     * the property $filterGeneratedData is set here.
     */
    public function __construct()
    {
        $this->filterGeneratedData = Ark()->webService()->getFilterGeneratedData();
    }

    /**
     * @since 1.1 method added
     * @return ArkWebInput
     */
    protected function _getInputHandler()
    {
        return Ark()->webInput();
    }

    /**
     * @since 1.1 method added
     * @return ArkWebOutput
     */
    protected function _getOutputHandler()
    {
        return Ark()->webOutput();
    }

    /**
     * @param string|array $name
     * @param null|mixed $default
     * @param null|string $regex
     * @param null|Exception $error
     * @return mixed
     * @since 1.1 method added
     */
    protected function _readRequest($name, $default = null, $regex = null, &$error = null)
    {
        return Ark()->webInput()->readRequest($name, $default, $regex, $error);
    }

    /**
     * @param string $name
     * @param callable|string|null $checker An anonymous function `f(v)` or a regular expression, else would not check any more
     * @return mixed
     * @throws Exception
     * @since 2.6
     */
    protected function _readIndispensableRequest($name, $checker)
    {
        return Ark()->webInput()->readIndispensableRequest($name, $checker);
    }

    /**
     * If you want to track a request, use this method to get the serial number of a request.
     * @return string
     */
    protected function _getRequestSerial()
    {
        return Ark()->webService()->getRequestSerial();
    }

    /**
     * @param string $data
     * @param int $httpCode
     */
    protected function _sayOK($data = "", $httpCode = 200)
    {
        Ark()->webOutput()->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON);
        http_response_code($httpCode);
        Ark()->webOutput()->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_OK, $data);
    }

    /**
     * @param string $error
     * @param int $httpCode
     */
    protected function _sayFail($error = "", $httpCode = 200)
    {
        Ark()->webOutput()->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON);
        http_response_code($httpCode);
        Ark()->webOutput()->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_FAIL, $error);
    }

    /**
     * @param string $templateFile
     * @param array $params
     * @param int $httpCode
     */
    protected function _showPage($templateFile, $params = [], $httpCode = 200)
    {
        http_response_code($httpCode);
        try {
            Ark()->webOutput()->displayPage($templateFile, $params);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}