<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 15:02
 */

namespace sinri\ark\io;


use sinri\ark\core\ArkHelper;

class ArkWebInput
{
    const METHOD_ANY = "ANY";//since v2.1.3 for TreeRouter

    const METHOD_HEAD = "HEAD";//since v1.3.0
    const METHOD_GET = "GET";//since v1.3.0
    const METHOD_POST = "POST";//since v1.3.0
    const METHOD_PUT = "PUT";//since v1.3.0
    const METHOD_DELETE = "DELETE";//since v1.3.0
    const METHOD_OPTIONS = "OPTIONS";//since v1.3.0
    const METHOD_PATCH = "PATCH";//since v1.3.0
    const METHOD_CLI = "cli";//since v1.3.0

    protected $headerHelper;
    protected $ipHelper;
    protected $uploadFileHelper;
    protected $rawPostBody;
    protected $rawPostBodyParsedAsJson;

    public function __construct()
    {
        $this->headerHelper = new WebInputHeaderHelper();
        $this->ipHelper = new WebInputIPHelper();
        $this->uploadFileHelper = new WebInputFileUploadHelper();
        $this->rawPostBody = file_get_contents('php://input');
        $this->rawPostBodyParsedAsJson = @json_decode($this->rawPostBody, true);
    }

    /**
     * @return WebInputFileUploadHelper
     */
    public function getUploadFileHelper(): WebInputFileUploadHelper
    {
        return $this->uploadFileHelper;
    }

    /**
     * @return WebInputHeaderHelper
     */
    public function getHeaderHelper(): WebInputHeaderHelper
    {
        return $this->headerHelper;
    }

    /**
     * @return WebInputIPHelper
     */
    public function getIpHelper(): WebInputIPHelper
    {
        return $this->ipHelper;
    }

    /**
     * @return bool|string
     */
    public function getRawPostBody()
    {
        return $this->rawPostBody;
    }

    /**
     * @return mixed
     */
    public function getRawPostBodyParsedAsJson()
    {
        return $this->rawPostBodyParsedAsJson;
    }

    /**
     * @param string|array $name
     * @param null|mixed $default
     * @param null|string $regex
     * @param null|\Exception $error
     * @return mixed
     */
    public function readRequest($name, $default = null, $regex = null, &$error = null)
    {
        $value = ArkHelper::readTarget($_REQUEST, $name, $default, $regex, $error);
        try {
            $content_type = $this->headerHelper->getHeader("CONTENT-TYPE", null, '/^application\/json/');
            if (
                $content_type !== null
                //preg_match('/^application\/json(;.+)?$/', $content_type)
            ) {
                if (is_array($this->rawPostBodyParsedAsJson)) {
                    $value = ArkHelper::readTarget($this->rawPostBodyParsedAsJson, $name, $default, $regex, $error);
                }
            }
        } catch (\Exception $exception) {
            // actually do nothing.
            $error = $exception;
        }
        return $value;
    }

    public function readHeader($name, $default = null, $regex = null)
    {
        return $this->headerHelper->getHeader($name, $default, $regex);
    }

    public function readSession($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_SESSION, $name, $default, $regex);
    }

    public function readServer($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_SERVER, $name, $default, $regex);
    }

    public function readCookie($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_COOKIE, $name, $default, $regex);
    }

    public function readGet($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_GET, $name, $default, $regex);
    }

    public function readPost($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_POST, $name, $default, $regex);
    }


    /**
     * @param string[] $proxyIPs
     * @return string
     */
    public function getRequestSourceIP($proxyIPs = [])
    {
        return $this->ipHelper->detectVisitorIP($proxyIPs);
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        $method = $this->readServer('REQUEST_METHOD');
        if ($method !== null) {
            $method = strtoupper($method);
            return $method;
        }
        return ArkHelper::isCLI() ? self::METHOD_CLI : php_sapi_name();
    }

}