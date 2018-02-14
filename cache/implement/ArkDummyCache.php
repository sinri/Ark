<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 22:00
 */

namespace sinri\ark\cache\implement;


use sinri\ark\cache\ArkCache;

class ArkDummyCache implements ArkCache
{

    /**
     * @param string $key
     * @param mixed $object
     * @param int $life 0 for no limit, or seconds
     * @return bool
     */
    public function saveObject($key, $object, $life = 0)
    {
        return true;
    }

    /**
     * @param string $key
     * @return mixed|bool
     */
    public function getObject($key)
    {
        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function removeObject($key)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function removeExpiredObjects()
    {
        return true;
    }
}