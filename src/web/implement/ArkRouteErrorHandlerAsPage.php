<?php


namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\web\ArkRouteErrorHandlerInterface;

abstract class ArkRouteErrorHandlerAsPage implements ArkRouteErrorHandlerInterface
{
    /**
     * @var string
     */
    protected $templateFile;

    public function __construct()
    {
        $this->templateFile = $this->getTemplateFile();
    }

    /**
     * @return string
     */
    abstract public function getTemplateFile();

    public function execute($errorData = [], $http_code = 404)
    {
        try {
            if (!is_string($this->templateFile) || !file_exists($this->templateFile)) {
                throw new Exception("ArkRouteErrorHandler::Template file is not available.");
            }
            Ark()->webOutput()
                ->sendHTTPCode($http_code)
                ->displayPage($this->templateFile, $errorData);
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL . $exception->getTraceAsString();
        }
    }
}