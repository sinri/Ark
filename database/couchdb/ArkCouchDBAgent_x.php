<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/6
 * Time: 23:09
 */

namespace sinri\ark\database\couchdb;


use sinri\ark\io\curl\ArkCurl;

/**
 * Class ArkCouchDBAgent_x
 * @package sinri\ark\database\couchdb
 * @deprecated to be moved to new component
 */
class ArkCouchDBAgent_x
{
    const COUCH_DB_METHOD_GET = "GET";
    const COUCH_DB_METHOD_HEAD = "HEAD";
    const COUCH_DB_METHOD_POST = "POST";
    const COUCH_DB_METHOD_PUT = "PUT";
    const COUCH_DB_METHOD_DELETE = "DELETE";
    const COUCH_DB_METHOD_COPY = "COPY";

    protected $host;
    protected $port;
    protected $username;
    protected $password;

    public function __construct($host, $port, $username, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function sendRequest($method, $api, $headers = null, $body = null)
    {
        $req = new ArkCurl();
        $req->prepareToRequestURL($method, $this->username . ":" . $this->password . "@" . $this->host . ":" . $this->port . $api);
        if ($headers) {
            foreach ($headers as $headerName => $headerValue) {
                $req->setHeader($headerName, $headerValue);
            }
        }
        if ($body) {
            $req->setPostContent($body);
        }
        return $req->execute(true);
    }
}