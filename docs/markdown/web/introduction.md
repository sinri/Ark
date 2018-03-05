# Ark Web Service

Ark provides simple web service toolkit for RESTful design.
There are mainly two jobs for web service.

1. analyze the request to find the handler
1. manage sessions

With Ark, you can follow this routine to handle a request:

1. use Router to match an handle set (filters and controller), if none, use Router's error handler.
1. execute each filter, commonly authentic check and data preparation
1. let controller handle the request

For session control, you can use Ark PHP Web Session Manager (`ArkWebSession`) or use independent token system.
Note, `ArkWebSession` should be initialized in the beginning of web service, 
while token commonly be checked among filters.

## Sample

Here give an example.

```php
// if you need PHP Session Management (with file system)
//\sinri\ark\web\ArkWebSession::sessionStart(__DIR__.'/sessions');

$logger=new \sinri\ark\core\ArkLogger(__DIR__.'/../log','web');
$logger->setIgnoreLevel(LogLevel::DEBUG);

$web_service = Ark()->webService();
//$web_service->setDebug(true);
//$web_service->setLogger($logger);
$web_service->setLogger(new \sinri\ark\core\ArkLogger(__DIR__ . '/../log', 'web'));
$router = $web_service->getRouter();
//$router->setDebug(true);
//$router->setLogger($logger);
$router->get("getDocument/{doc_id}/page/{page_id}",function($docId,$pageId){
    echo "GET DOC {$docId} PAGE {$pageId}".PHP_EOL;
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
$web_service->handleRequest();
```