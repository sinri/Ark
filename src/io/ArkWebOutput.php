<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 16:13
 */

namespace sinri\ark\io;


use Exception;
use Mimey\MimeTypes;

class ArkWebOutput
{
    const AJAX_JSON_CODE_OK = "OK";
    const AJAX_JSON_CODE_FAIL = "FAIL";

    const CONTENT_TYPE_JSON = "application/json";
    const CONTENT_TYPE_OCTET_STREAM = 'application/octet-stream';

    const CHARSET_UTF8 = "UTF-8";

    /**
     * @return int
     */
    public function getCurrentHTTPCode()
    {
        return http_response_code();
    }

    /**
     * @param $httpCode
     * @return ArkWebOutput
     * @since 2.8.1 return $this instead of int
     */
    public function sendHTTPCode($httpCode)
    {
        http_response_code($httpCode);
        return $this;
    }

    /**
     * @param $contentType
     * @param null $charSet
     * @return ArkWebOutput
     * @since 2.8.1 return $this
     */
    public function setContentTypeHeader($contentType, $charSet = null)
    {
        $this->sendHeader("Content-Type: " . $contentType . ($charSet !== null ? '; charset=' . $charSet : ''));
        return $this;
    }

    public function sendHeader($header, $replace = true)
    {
        header($header, $replace);
        return $this;
    }

    /**
     * @param mixed $anything
     * @param int $options
     * @param int $depth
     * @throws Exception @since 3.2.2
     */
    public function json($anything, $options = 0, $depth = 512)
    {
        $response = json_encode($anything, $options, $depth);
        if ($response === false) {
            throw new Exception("JSON ENCODING FAILED: " . json_last_error_msg());
        }
        echo $response;
    }

    /**
     * @param string $code OK or FAIL
     * @param mixed $data
     * @param int $options @since 3.2.2
     * @param int $depth @since 3.2.2
     * @throws Exception @since 3.2.2
     */
    public function jsonForAjax($code = self::AJAX_JSON_CODE_OK, $data = '', $options = 0, $depth = 512)
    {
        $response = json_encode(["code" => $code, "data" => $data], $options, $depth);
        if ($response === false) {
            throw new Exception("JSON ENCODING FAILED: " . json_last_error_msg());
        }
        echo $response;
    }

    /**
     * @param string $templateFile
     * @param array $params
     * @throws Exception
     */
    public function displayPage($templateFile, $params = [])
    {
        extract($params);
        if (!file_exists($templateFile)) {
            throw new Exception("Template file [{$templateFile}] not found.");
        }
        /** @noinspection PhpIncludeInspection */
        require $templateFile;
    }

    /**
     * @param string $url
     * @since 2.8.1
     * @since 3.1.7 remove urlencode on url
     */
    public function redirect($url)
    {
        $this->sendHTTPCode(302);
        header("Location: " . $url);
    }

    /**
     * 文件通过非直接方式下载
     * @param string $file
     * @param null $down_name Extension Free File Name For Download
     * @param null|string $content_type
     * @return bool
     * @throws Exception
     */
    public function downloadFileIndirectly($file, $content_type = null, $down_name = null)
    {
        if (!file_exists($file)) {
            throw new Exception("No such file there: " . $file, 404);
        }

        if ($down_name !== null && $down_name !== false) {
            $extension = substr($file, strrpos($file, '.')); //获取文件后缀
            $down_name = $down_name . $extension; //新文件名，就是下载后的名字
        } else {
            $k = pathinfo($file);
            $extension = $k['extension'];
            $down_name = $k['filename'] . '.' . $extension;
        }

        $fp = fopen($file, "r");
        $file_size = filesize($file);

        if ($content_type === null) {
            // @since 1.5.0 The default $content_type for NULL would not be self::CONTENT_TYPE_OCTET_STREAM any more
            // but use MimeTypes to parse from extension.
            $content_type = $this->getMimeTypeByExtension($extension);
        }
        if ($content_type === self::CONTENT_TYPE_OCTET_STREAM) {
            $content_disposition = 'attachment; filename=' . $down_name;
        } else {
            $content_disposition = 'inline';
        }

        // Headers
        header("Content-Type: " . $content_type);
        //header("Accept-Ranges: bytes");
        header("Content-Length: " . $file_size);
        header("Content-Disposition: " . $content_disposition);
        $bufferSize = 1024;
        $fileSentBytesCount = 0;

        // Write to client
        while (!feof($fp) && $fileSentBytesCount < $file_size) {
            $buffer = fread($fp, $bufferSize);
            $fileSentBytesCount += $bufferSize;
            echo $buffer;
            /**
             * This flush added @since 2.3 try to make the save dialog comes soon
             */
            flush();
        }
        fclose($fp);
        return true;
    }

    /**
     * @param string $extension
     * @return string
     * @since 1.5.0
     */
    public function getMimeTypeByExtension($extension)
    {
        $mime = (new MimeTypes())->getMimeType($extension);
        if ($mime === null) $mime = self::CONTENT_TYPE_OCTET_STREAM;
        return $mime;
    }

    /**
     * 这是一个实验性的功能，需要在NGINX设定好internal声明并且计划好对应的文件库。
     * @param string $baseUrl e.g. /protected (set in nginx config)
     * @param string $baseDirectory e.g. /var/code/protected_file
     * @param string $relativeFilePath e.g. sub_path/file.ext
     * @param null|string $down_name e.g. customized.name
     * @return bool
     * @throws Exception
     * @since 2.9.0
     * @todo 需要实地验证
     */
    public function downloadFileThroughNginxSendFile($baseUrl, $baseDirectory, $relativeFilePath, $down_name = null)
    {
        /**
         * here base url is /protected
         * # IN NGINX server block 这个是定义读取你的文件的目录的url开头  直接访问是不可以的
         * location /protected {
         * internal;
         * alias   /var/vhost/demo/uploadfiles;
         * }
         */

        $file = $baseDirectory . DIRECTORY_SEPARATOR . $relativeFilePath;

        if (!file_exists($file)) {
            throw new Exception("No such file there: " . $file, 404);
        }

        if ($down_name !== null && $down_name !== false) {
            $extension = substr($file, strrpos($file, '.')); //获取文件后缀
            $down_name = $down_name . $extension; //新文件名，就是下载后的名字
        } else {
            $k = pathinfo($file);
            $extension = $k['extension'];
            $down_name = $k['filename'] . '.' . $extension;
        }

        $content_type = $this->getMimeTypeByExtension($extension);
        if ($content_type === self::CONTENT_TYPE_OCTET_STREAM) {
            //$content_disposition = 'attachment; filename=' . $down_name;

            // 处理中文文件名
            $ua = $_SERVER ["HTTP_USER_AGENT"];
            if (preg_match("/MSIE/", $ua)) {
                //$encoded_filename = rawurlencode ( $down_name );
                header('Content-Disposition: attachment; filename="' . rawurlencode($down_name) . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header("Content-Disposition: attachment; filename*=\"utf8''" . $down_name . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $down_name . '"');
            }
        } else {
            // $content_disposition = 'inline';
            header('Content-Disposition: inline');
        }

        // 就这么简单一句话搞定 注意“protected”是和nginx配置文件的 protected要一致
        header("X-Accel-Redirect: " . $baseUrl . "/" . $relativeFilePath);

        return true;
    }

}