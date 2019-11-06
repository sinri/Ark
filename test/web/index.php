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
use sinri\ark\test\web\filter\AnotherFilter;
use sinri\ark\test\web\filter\TestFilter;
use sinri\ark\web\implement\ArkRouteErrorHandlerAsCallback;
use sinri\ark\web\implement\ArkRouterFreeTailRule;

require_once __DIR__ . '/../../vendor/autoload.php';

//\sinri\ark\web\ArkWebSession::sessionStart(__DIR__.'/sessions');

date_default_timezone_set("Asia/Shanghai");

$logger = new ArkLogger(__DIR__ . '/../log', 'web');
$logger->setIgnoreLevel(LogLevel::DEBUG);

$web_service = Ark()->webService();
//$web_service->setDebug(true);
$web_service->setLogger($logger);
//$web_service->setLogger(new ArkLogger(__DIR__ . '/../log', 'web'));
$router = $web_service->getRouter();
//$router->setDebug(true);
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


$router->get("", function () {
    echo "Welcome to Ark!" . PHP_EOL;
});

$router->registerFrontendFolder("static/frontend", __DIR__ . '/frontend', []);

$freeTailRouteRule1 = ArkRouterFreeTailRule::buildRouteRule(
    ArkWebInput::METHOD_ANY,
    "free/tail/{a}/{b}",
    ArkRouterFreeTailRule::buildCallbackDescriptionWithClassNameAndMethod(Foo::class, 'tail')
);

$router->registerRouteRule($freeTailRouteRule1);

$freeTailRouteRule2 = ArkRouterFreeTailRule::buildRouteRule(
    ArkWebInput::METHOD_ANY,
    "freeTail",
    ArkRouterFreeTailRule::buildCallbackDescriptionWithClassNameAndMethod(FreeTailController::class, 'handlePath')
);

$router->registerRouteRule($freeTailRouteRule2);

$web_service->setupFileSystemViewer("fs", __DIR__ . '/../', [], function ($file, $components) {
    echo "Target File: " . ($file) . PHP_EOL;
    echo "Path Components: " . json_encode($components) . PHP_EOL;
});

$web_service->handleRequest();

// call http://localhost/phpstorm/Ark/test/web/