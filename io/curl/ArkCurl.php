<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 22:26
 */

namespace sinri\ark\io\curl;


use sinri\ark\core\ArkLogger;
use sinri\ark\io\ArkWebInput;

class ArkCurl
{
    protected $method;
    protected $url;
    protected $queryList;
    protected $postData;
    protected $headerList;
    protected $cookieList;
    protected $logger;
    protected $optionList;

    public function __construct()
    {
        $this->logger = ArkLogger::makeSilentLogger();
        $this->resetParameters();
    }

    protected function resetParameters()
    {
        $this->method = ArkWebInput::METHOD_GET;
        $this->url = "";
        $this->queryList = [];
        $this->postData = "";
        $this->headerList = [];
        $this->cookieList = [];
        $this->optionList = [];
    }

    /**
     * @param ArkLogger $logger
     */
    public function setLogger(ArkLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param int $option definition of CURLOPT cluster
     * @param mixed $value
     */
    public function setCURLOption($option, $value)
    {
        $this->optionList[$option] = $value;
    }

    /**
     * @param $method
     * @param $url
     * @return $this
     */
    public function prepareToRequestURL($method, $url)
    {
        $this->method = $method;
        $this->url = $url;
        return $this;
    }

    /**
     * @param $queryName
     * @param $queryValue
     * @return $this
     */
    public function setQueryField($queryName, $queryValue)
    {
        $this->queryList[$queryName] = $queryValue;
        return $this;
    }

    /**
     * @param array|string $data
     * @return $this
     */
    public function setPostContent($data)
    {
        $this->postData = $data;
        return $this;
    }

    /**
     * @param $fieldName
     * @param $fieldValue
     * @return $this
     */
    public function setPostFormField($fieldName, $fieldValue)
    {
        if (!is_array($this->postData)) {
            $this->postData = [];
        }
        $this->postData[$fieldName] = $fieldValue;
        return $this;
    }

    /**
     * @param $headerName
     * @param $headerValue
     * @return $this
     */
    public function setHeader($headerName, $headerValue)
    {
        $this->headerList[$headerName] = $headerValue;
        return $this;
    }

    /**
     * @param $cookie
     * @return $this
     */
    public function setCookie($cookie)
    {
        $this->cookieList[] = $cookie;
        return $this;
    }

    /**
     * @param bool $takePostDataAsJson
     * @return mixed
     */
    public function execute($takePostDataAsJson = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        $use_body = in_array($this->method, [ArkWebInput::METHOD_POST, ArkWebInput::METHOD_PUT]);
        if ($use_body) {
            curl_setopt($ch, CURLOPT_POST, 1);

            if ($takePostDataAsJson) {
                $this->headerList['Content-Type'] = 'application/json';
                $this->postData = json_encode($this->postData);
            } else {
                // if postData is raw string, leave it simply original
                if (!is_scalar($this->postData)) {
                    $this->postData = http_build_query($this->postData);
                }
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postData);
        }

        $query_string = http_build_query($this->queryList);
        if (!empty($query_string)) {
            $this->url .= "?" . $query_string;
        }
        curl_setopt($ch, CURLOPT_URL, $this->url);

        if (!empty($this->headerList)) {
            $headers = [];
            foreach ($this->headerList as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if (!empty($cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $this->logger->info(
            "CURL-{$this->method}-Request",
            ["URL" => $this->url, "HEADER" => $this->headerList, "BODY" => $this->postData]
        );

        // inject options
        if (!empty($this->optionList)) {
            foreach ($this->optionList as $option => $value) {
                curl_setopt($ch, $option, $value);
            }
        }

        $response = curl_exec($ch);

        $this->logger->info("CURL-{$this->method}-Response", [$response]);

        curl_close($ch);

        $this->resetParameters();

        return $response;
    }
}