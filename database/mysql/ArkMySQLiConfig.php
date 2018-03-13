<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/3/8
 * Time: 11:42
 */

namespace sinri\ark\database\mysql;


use sinri\ark\core\ArkHelper;

/**
 * Class ArkMySQLiConfig
 * @package sinri\ark\database\mysql
 * @property string $title
 * @property string $host
 * @property int $port
 * @property string $username
 * @property string $password
 * @property string $database
 * @property string $charset
 */
class ArkMySQLiConfig
{
    const CONFIG_TITLE = "title";
    const CONFIG_HOST = "host";
    const CONFIG_PORT = "port";
    const CONFIG_USERNAME = "username";
    const CONFIG_PASSWORD = "password";
    const CONFIG_DATABASE = "database";
    const CONFIG_CHARSET = "charset";

    protected $dict;

    /**
     * ArkMySQLiConfig constructor.
     * @param null|array $dict
     */
    public function __construct($dict = null)
    {
        $this->dict = is_array($dict) ? $dict : [];
        if (null === $this->getConfigField(self::CONFIG_CHARSET)) {
            $this->setCharset("utf8");
        }
        if (null === $this->getConfigField(self::CONFIG_PORT)) {
            $this->setPort("3306");
        }
    }

    public function __set($name, $value)
    {
        ArkHelper::writeIntoArray($this->dict, $name, $value);
    }

    public function __get($name)
    {
        return $this->getConfigField($name);
    }

    public function __isset($name)
    {
        return (isset($this->dict) && isset($this->dict[$name]));
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setHost($value)
    {
        $field = self::CONFIG_HOST;
        $this->$field = $value;
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setPort($value)
    {
        $field = self::CONFIG_PORT;
        $this->$field = intval($value, 10);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setUsername($value)
    {
        $field = self::CONFIG_USERNAME;
        $this->$field = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPassword($value)
    {
        $field = self::CONFIG_PASSWORD;
        $this->$field = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDatabase($value)
    {
        $field = self::CONFIG_DATABASE;
        $this->$field = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCharset($value)
    {
        $field = self::CONFIG_CHARSET;
        $this->$field = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTitle($value)
    {
        $field = self::CONFIG_TITLE;
        $this->$field = $value;
        return $this;
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function getConfigField($name, $default = null)
    {
        return ArkHelper::readTarget($this->dict, $name, $default);
    }

}