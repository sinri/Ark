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
use sinri\ark\test\web\filter\AnotherFilter;
use sinri\ark\test\web\filter\TestFilter;
use sinri\ark\web\ArkRouterFreeTailRule;
use sinri\ark\web\implement\ArkRouteErrorHandlerAsCallback;

require_once __DIR__ . '/../../vendor/autoload.php';

//\sinri\ark\web\ArkWebSession::sessionStart(__DIR__.'/sessions');

date_default_timezone_set("Asia/Shanghai");

$logger = new ArkLogger(__DIR__ . '/../log', 'web');
$logger->setIgnoreLevel(LogLevel::DEBUG);

$web_service = Ark()->webService();
$web_service->setDebug(true);
$web_service->setLogger($logger);
//$web_service->setLogger(new ArkLogger(__DIR__ . '/../log', 'web'));
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

$router->registerFrontendFolder("qd/ym", __DIR__ . '/frontend', []);

$freeTailRouteRule = ArkRouterFreeTailRule::buildRouteRule(
    ArkWebInput::METHOD_ANY,
    "free/tail/{a}/{b}",
    ArkRouterFreeTailRule::buildCallbackDescriptionWithClassNameAndMethod(Foo::class, 'tail')
);

$router->registerFreeTailRouteRule($freeTailRouteRule);

$web_service->setupFileSystemViewer("fs", __DIR__ . '/../', [], function ($file, $components) {
    var_dump($file);
    var_dump($components);
});

$web_service->handleRequest();

// call http://localhost/phpstorm/Ark/test/web/