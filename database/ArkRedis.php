<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/3/6
 * Time: 11:45
 */

namespace sinri\ark\database;


use Predis\Client;

class ArkRedis
{
    protected $redisClient;

    /**
     * @return Client
     */
    public function getRedisClient(): Client
    {
        return $this->redisClient;
    }

    public function __construct($parameters = null, $options = null)
    {
        $this->redisClient = new Client($parameters, $options);
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $database
     * @param null|string $password
     * @return array
     */
    public static function simpleParameterBuilder($host, $port = 6379, $database = 255, $password = null)
    {
        $parameters = [
            'host' => $host,
            'port' => $port,
            'database' => $database,
        ];
        if ($password !== null) {
            $parameters['password'] = $password;
        }
        return $parameters;
    }

}