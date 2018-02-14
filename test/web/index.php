<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 23:18
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../autoload.php';

$web_service = Ark()->webService();
$web_service->setLogger(new \sinri\ark\core\ArkLogger(__DIR__ . '/../log', 'web'));
$router = $web_service->getRouter();
$router->loadAllControllersInDirectoryAsCI(
    __DIR__ . '/controller',
    '',
    'sinri\ark\test\web\controller\\',
    [
        \sinri\ark\test\web\filter\TestFilter::class,
        \sinri\ark\test\web\filter\AnotherFilter::class,
        //'no_such_filter',//this might cause error
    ]
);
$web_service->handleRequest();