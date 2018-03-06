<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/3/6
 * Time: 20:59
 */

namespace sinri\ark\web;


use sinri\ark\io\ArkWebOutput;

class ArkRouteErrorHandler
{
    const TYPE_DEFAULT = "DEFAULT";
    const TYPE_TEMPLATE = "TEMPLATE";
    const TYPE_CALLBACK = "CALLBACK";

    protected $type;

    protected $templateFile;
    protected $callback;

    public function __construct()
    {
        $this->type = self::TYPE_DEFAULT;
    }

    public function execute($errorData = [], $http_code = 404)
    {
        try {
            http_response_code($http_code);

            switch ($this->type) {
                case self::TYPE_CALLBACK:
                    if (!is_callable($this->callback)) {
                        throw new \Exception("ArkRouteErrorHandler::Callback is not a proper callback instance.");
                    }
                    call_user_func_array($this->callback, [$errorData]);
                    break;
                case self::TYPE_TEMPLATE:
                    if (!is_string($this->templateFile) || !file_exists($this->templateFile)) {
                        throw new \Exception("ArkRouteErrorHandler::Template file is not available.");
                    }
                    Ark()->webOutput()->displayPage($this->templateFile, $errorData);
                    break;
                default:
                    Ark()->webOutput()->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON);
                    Ark()->webOutput()->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_FAIL, $errorData);
                    break;
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage() . PHP_EOL . $exception->getTraceAsString();
        }
    }

    /**
     * @param string $templateFile
     * @return ArkRouteErrorHandler
     */
    public static function buildWithTemplate($templateFile)
    {
        $handler = new ArkRouteErrorHandler();
        $handler->type = self::TYPE_TEMPLATE;
        $handler->templateFile = $templateFile;
        return $handler;
    }

    /**
     * @param callable|string[] $callback
     * @return ArkRouteErrorHandler
     */
    public static function buildWithCallback($callback)
    {
        $handler = new ArkRouteErrorHandler();
        $handler->type = self::TYPE_CALLBACK;
        $handler->callback = $callback;
        return $handler;
    }
}