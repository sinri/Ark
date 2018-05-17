<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 23:18
 */

use Psr\Log\LogLevel;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../autoload.php';

//\sinri\ark\web\ArkWebSession::sessionStart(__DIR__.'/sessions');

$logger = new \sinri\ark\core\ArkLogger(__DIR__ . '/../log', 'web');
$logger->setIgnoreLevel(LogLevel::DEBUG);

$web_service = Ark()->webService();
//$web_service->setDebug(true);
//$web_service->setLogger($logger);
$web_service->setLogger(new \sinri\ark\core\ArkLogger(__DIR__ . '/../log', 'web'));
$router = $web_service->getRouter();
$router->setDebug(true);
$router->setLogger($logger);

$router->setErrorHandler(\sinri\ark\web\ArkRouteErrorHandler::buildWithCallback(function ($error, $code) {
    if ($code == 404) {
        echo "404!";
    } else {
        echo json_encode(['message' => $error, 'code' => $code]);
    }
}));

$router->get("getDocument/{doc_id}/page/{page_id}", function ($docId, $pageId) {
    echo "GET DOC {$docId} PAGE {$pageId}" . PHP_EOL;
});
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

$router->frontendFolder("qd/ym", __DIR__ . '/frontend', []);

$web_service->handleRequest();