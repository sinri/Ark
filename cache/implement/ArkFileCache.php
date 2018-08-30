<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 20:07
 */

namespace sinri\ark\cache\implement;


use sinri\ark\cache\ArkCache;

class ArkFileCache implements ArkCache
{

    protected $cacheDir;
    protected $fileMode = null;

    /**
     * ArkFileCache constructor.
     * @param string $cacheDir
     * @param null|int $fileMode such as 0777
     */
    public function __construct($cacheDir, $fileMode = null)
    {
        $this->fileMode = $fileMode;
        $this->setCacheDir($cacheDir);//should be overrode by setter
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        if (!file_exists($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        $this->cacheDir = $cacheDir;
    }

    protected function validateObjectKey($key)
    {
        if (preg_match('/^[A-Za-z0-9_]+$/', $key)) {
            return true;
        }
        return false;
    }

    protected function getTimeLimitFromObjectPath($path)
    {
        $parts = explode('.', $path);
        $limit = $parts[count($parts) - 1];
        return $limit;
    }

    /**
     * @param string $key
     * @param mixed $object
     * @param int $life 0 for no limit, or seconds
     * @return bool
     */
    public function saveObject($key, $object, $life = 0)
    {
        if (!$this->validateObjectKey($key)) return false;
        $data = serialize($object);
        $this->removeObject($key);
        $file_name = $key . '.' . ($life <= 0 ? '0' : time() + $life);
        $path = $this->cacheDir . '/' . $file_name;
        $done = file_put_contents($path, $data);
        if ($this->fileMode !== null) chmod($path, $this->fileMode);
        return $done ? true : false;
    }

    /**
     * @param string $key
     * @return mixed|bool
     */
    public function getObject($key)
    {
        if (!$this->validateObjectKey($key)) return false;
        $list = glob($this->cacheDir . '/' . $key . '.*');
        if (count($list) === 0) {
            return false;
        }
        $path = $list[0];
        $limit = $this->getTimeLimitFromObjectPath($path);
        if ($limit < time()) {
            $this->removeObject($key);
            return false;
        }
        $data = file_get_contents($path);
        $object = unserialize($data);
        return $object;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function removeObject($key)
    {
        if (!$this->validateObjectKey($key)) return false;
        array_map('unlink', glob($this->cacheDir . '/' . $key . '.*'));
        return true;
    }

    /**
     * @return bool
     */
    public function removeExpiredObjects()
    {
        $list = glob($this->cacheDir . '/*.*');
        if (empty($list)) return true;
        $all_deleted = true;
        foreach ($list as $path) {
            $limit = $this->getTimeLimitFromObjectPath($path);
            if ($limit < time()) {
                $deleted = unlink($path);
                if (!$deleted) {
                    $all_deleted = false;
                }
            }
        }
        return $all_deleted;
    }
}