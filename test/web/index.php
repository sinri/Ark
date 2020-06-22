<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 23:18
 */

use Psr\Log\LogLevel;
use sinri\ark\core\ArkLogger;
use sinri\ark\io\ArkWebInput;
use sinri\ark\test\web\controller\Foo;
use sinri\ark\test\web\controller\FreeTailController;
use sinri\ark\test\web\controller\PureAutoRestFul\JustSingleController;
use sinri\ark\test\web\filter\AnotherFilter;
use sinri\ark\test\web\filter\TestFilter;
use sinri\ark\web\implement\ArkRouteErrorHandlerAsCallback;
use sinri\ark\web\implement\ArkRouterFreeTailRule;

require_once __DIR__ . '/../../vendor/autoload.php';

//\sinri\ark\web\ArkWebSession::sessionStart(__DIR__.'/sessions');

date_default_timezone_set("Asia/Shanghai");

$logger = new ArkLogger(__DIR__ . '/../log', 'web');
$logger->setIgnoreLevel(LogLevel::DEBUG);
$logger->setGroupByPrefix(true);
$logger->removeCurrentLogFile();

$web_service = Ark()->webService();
$web_service->setDebug(true);
$web_service->setLogger($logger);
$router = $web_service->getRouter();
$router->setDebug(true);
$router->setLogger($logger);

$router->setErrorHandler(new class extends ArkRouteErrorHandlerAsCallback
{

    /**
     * @param mixed $errorMessage
     * @param int $httpCode
     */
    public function requestErrorCallback($errorMessage, $httpCode)
    {
        Ark()->webOutput()
            ->sendHTTPCode($httpCode)
            ->setContentTypeHeader('application/json')
            ->json(['message' => $errorMessage, 'code' => $httpCode]);
    }
});

$router->get("getDocument/{doc_id}/page/{page_id}", function ($docId, $pageId) {
    echo "GET DOC {$docId} PAGE {$pageId}" . PHP_EOL;
});
$router->loadAllControllersInDirectoryAsCI(
    __DIR__ . '/controller',
    '',
    'sinri\ark\test\web\controller\\',
    [
        TestFilter::class,
        AnotherFilter::class,
        //'no_such_filter',//this might cause error
    ]
);


$router->get("", function () use ($logger) {
    $logger->info("Homepage Requested");
    echo "Welcome to Ark!" . PHP_EOL;
    echo "Check static/frontend for url test cases" . PHP_EOL;
});

// Note: if you use http://xxxx.com/static/frontend without tail `/`
// the `frontend` would not be treated as folder but a file,
// so you should rewrite this in Nginx in front of PHP
$router->registerFrontendFolder("static/frontend", __DIR__ . '/frontend', []);

//$autoRoute = new ArkRouterAutoRestfulRule(
//    ArkWebInput::METHOD_ANY,
//    'auto_router/',
//    'sinri\ark\test\web\controller',
//    []
//);
//$router->registerRouteRule($autoRoute);

$router->loadAutoRestfulControllerRoot('auto_router/', 'sinri\ark\test\web\controller', []);

// Fix Bug: http://localhost/phpstorm/Ark/test/web/PureAutoRestFulController/api
$router->loadAutoRestfulControllerRoot('', 'sinri\ark\test\web\controller\PureAutoRestFul', []);

$router->loadAutoRestfulControllerRoot(
    'single/',
    JustSingleController::class
);

$freeTailRouteRule1 = new ArkRouterFreeTailRule(
    [ArkWebInput::METHOD_ANY],
    "free/tail/{a}/{b}",
    ArkRouterFreeTailRule::buildCallbackDescriptionWithClassNameAndMethod(Foo::class, 'tail')
);

$router->registerRouteRule($freeTailRouteRule1);

$freeTailRouteRule2 = new ArkRouterFreeTailRule(
    [ArkWebInput::METHOD_ANY],
    "freeTail",
    ArkRouterFreeTailRule::buildCallbackDescriptionWithClassNameAndMethod(FreeTailController::class, 'handlePath')
);

$router->registerRouteRule($freeTailRouteRule2);

$web_service->setupFileSystemViewer("fs", __DIR__ . '/../', [], function ($file, $components) {
    echo "Target File: " . ($file) . PHP_EOL;
    echo "Path Components: " . json_encode($components) . PHP_EOL;
});

$listOfRouteRules = $router->getListOfRouteRules();
foreach ($listOfRouteRules as $index => $listOfRouteRule) {
    $logger->info("[RULE " . ($index + 1) . "]" . $listOfRouteRule);
}

$web_service->handleRequest();

// call http://localhost/phpstorm/Ark/test/web/