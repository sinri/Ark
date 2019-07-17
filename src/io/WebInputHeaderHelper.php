<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 15:06
 */

namespace sinri\ark\io;


use Jenssegers\Agent\Agent;
use sinri\ark\core\ArkHelper;

class WebInputHeaderHelper
{
    protected $headers;
    protected $userAgentWorker;

    public function __construct()
    {
        $headers = getallheaders();
        // make all as lower case
        $this->headers = [];
        if (is_array($headers) && !empty($headers)) {
            foreach ($headers as $key => $value) {
                $this->headers[strtolower($key)] = $value;
            }
        }

        $this->userAgentWorker = null;
    }

    /**
     * @return array|false
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $name
     * @param null $default
     * @param null $regex
     * @return mixed|null
     */
    public function getHeader($name, $default = null, $regex = null)
    {
        $lower_name = strtolower($name);
        $header = ArkHelper::readTarget($this->headers, $lower_name, $default, $regex);
        return $header;
    }

    /**
     * @return Agent
     * @since 1.5.0
     */
    public function getUserAgentWorker()
    {
        if (!$this->userAgentWorker) $this->userAgentWorker = new Agent();
        return $this->userAgentWorker;
    }
}