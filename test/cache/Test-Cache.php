<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/24
 * Time: 10:32
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../autoload.php';


$cache = new \sinri\ark\cache\implement\ArkFileCache(__DIR__ . '/cache');
$cache = new \sinri\ark\cache\implement\ArkDummyCache();

$cache->saveObject("key", "value", 3600);
echo $cache->getObject("key") . PHP_EOL;
$cache->removeObject("key");
$cache->removeExpiredObjects();