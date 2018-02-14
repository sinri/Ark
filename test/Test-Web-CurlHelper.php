<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 22:50
 */

use sinri\ark\io\curl\CurlHelper;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../autoload.php';

$x = new CurlHelper();
$logger = new \sinri\ark\core\ArkLogger(__DIR__ . '/log', 'curl', false);
$x->setLogger($logger);
$response = $x->prepareToRequestURL(\sinri\ark\io\WebInputHelper::METHOD_GET, "https://sinri.cc")
    ->execute();

$response = $x->prepareToRequestURL(\sinri\ark\io\WebInputHelper::METHOD_POST, "https://sinri.cc")
    ->setPostContent(["a" => "b", "c" => "d"])
    ->execute(true);