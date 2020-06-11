<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 17:58
 */

namespace sinri\ark\web;


use Exception;
use Psr\Log\LogLevel;
use ReflectionClass;
use sinri\ark\core\ArkLogger;
use sinri\ark\io\ArkWebInput;
use sinri\ark\web\implement\ArkRouteErrorHandlerAsJson;
use sinri\ark\web\implement\ArkRouterAutoRestfulRule;
use sinri\ark\web\implement\ArkRouterFreeTailRule;
use sinri\ark\web\implement\ArkRouterRestfulRule;
use sinri\ark\web\implement\ArkRouterStaticRule;

class ArkRouter
{
    /**
     * @var bool
     */
    protected $debug;
    /**
     * @var ArkLogger
     */
    protected $logger;
    protected $defaultControllerName = null;
    protected $defaultMethodName = null;
    /**
     * @var ArkRouteErrorHandlerInterface
     */
    protected $errorHandler = null;

    /**
     * @var ArkRouterRule[]
     */
    protected $rules;


    public function __construct()
    {
        $this->debug = false;
        $this->logger = ArkLogger::makeSilentLogger();
        $this->defaultControllerName = 'Welcome';
        $this->defaultMethodName = 'index';
        $this->errorHandler = null;
        $this->rules = [];
    }

    /**
     * @param bool $debug
     * @return ArkRouter
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param ArkLogger $logger
     * @return ArkRouter
     */
    public function setLogger(ArkLogger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param null|string $defaultControllerName
     * @return ArkRouter
     */
    public function setDefaultControllerName(string $defaultControllerName)
    {
        $this->defaultControllerName = $defaultControllerName;
        return $this;
    }

    /**
     * @param null|string $defaultMethodName
     * @return ArkRouter
     */
    public function setDefaultMethodName(string $defaultMethodName)
    {
        $this->defaultMethodName = $defaultMethodName;
        return $this;
    }

    /**
     * Give a string as template file path for display-page use;
     * give an anonymous function or a callable definition array which consume one parameter of array,
     * or leave it as null to response JSON.
     * @param ArkRouteErrorHandlerInterface|null $errorHandler
     * @return ArkRouter
     */
    public function setErrorHandler($errorHandler)
    {
        $this->errorHandler = $errorHandler;
        return $this;
    }

    /**
     * @param array $errorData
     * @param int $httpCode @since 1.2.8
     */
    public function handleRouteError($errorData = [], $httpCode = 404)
    {
        if (!$this->errorHandler) {
            $this->errorHandler = new ArkRouteErrorHandlerAsJson();
        }
        $this->errorHandler->execute($errorData, $httpCode);
    }

    /**
     * @param ArkRouterRule $routeRule
     * @return $this
     */
    public function registerRouteRule($routeRule)
    {
        array_unshift($this->rules, $routeRule);
        return $this;
    }

    /**
     * @param string $path
     * @param string $dir
     * @param string[] $filters
     * @return ArkRouter
     */
    public function registerFrontendFolder($path, $dir, $filters = [])
    {
        $staticRule = new ArkRouterStaticRule(
            ArkWebInput::METHOD_ANY,
            $path,
            function ($subPath = null) use ($dir) {
                if ($subPath === null || $subPath === '') {
                    $subPath = 'index.html';
                }
                $path = $dir . '/' . $subPath;
                Ark()->webOutput()->downloadFileIndirectly($path);
            },
            $filters
        );
        //$this->registerStaticRouteRule($staticRule);
        $this->registerRouteRule($staticRule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function get($path, $callback, $filters = [], $hasFreeTail = false)
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule(ArkWebInput::METHOD_GET, $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_GET, $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function post($path, $callback, $filters = [], $hasFreeTail = false)
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule(ArkWebInput::METHOD_POST, $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_POST, $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function put($path, $callback, $filters = [], $hasFreeTail = false)
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule(ArkWebInput::METHOD_PUT, $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_PUT, $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function patch($path, $callback, $filters = [], $hasFreeTail = false)
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule(ArkWebInput::METHOD_PATCH, $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_PATCH, $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function delete($path, $callback, $filters = [], $hasFreeTail = false)
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule(ArkWebInput::METHOD_DELETE, $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_DELETE, $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function options($path, $callback, $filters = [], $hasFreeTail = false)
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule(ArkWebInput::METHOD_OPTIONS, $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_OPTIONS, $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function head($path, $callback, $filters = [], $hasFreeTail = false)
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule(ArkWebInput::METHOD_HEAD, $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_HEAD, $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function any($path, $callback, $filters = [], $hasFreeTail = false)
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule(ArkWebInput::METHOD_ANY, $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_ANY, $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string[] $methods array of ArkWebInput::METHOD_*
     * @param string $path
     * @param callable $callback
     * @param string[] $filters
     * @param bool $hasFreeTail
     * @return ArkRouter
     * @since 2.9.2
     */
    public function multiMethods($methods, $path, $callback, $filters = [], $hasFreeTail = false)
    {
        foreach ($methods as $method) {
            if ($hasFreeTail) {
                $route_rule = new ArkRouterFreeTailRule($method, $path, $callback, $filters);
            } else {
                $route_rule = new ArkRouterRestfulRule($method, $path, $callback, $filters);
            }
            $this->registerRouteRule($route_rule);
        }
        return $this;
    }

    /**
     * @param $incomingPath
     * @param $method
     * @return mixed|ArkRouterRule
     * @throws Exception
     */
    public function seekRoute($incomingPath, $method)
    {
        foreach ($this->rules as $rule) {
            $matched = $rule->checkIfMatchRequest($method, $incomingPath, (($this->debug && $this->logger) ? $this->logger : null));
            if ($this->debug) {
                $context = @json_decode($rule->__toString(), true);
                $context['incoming_path'] = $incomingPath;
                $this->logger->smartLog(
                    $matched,
                    'Route Rule Matched!', $context,
                    'Route Rule Did not match!', $context,
                    LogLevel::DEBUG,
                    LogLevel::DEBUG
                );
            }
            if ($matched) {
                return $rule;
            }
        }
        throw new Exception("No route matched: path={$incomingPath} method={$method}", 404);
    }

//    /**
//     * @param ArkRouterRestfulRule $shared
//     * @param ArkRouterRestfulRule[] $list
//     * @return ArkRouter
//     * @deprecated since 2.10 It should not be used anymore
//     */
//    public function group($shared, $list)
//    {
//        $filters = null;
//        $sharedPath = '';
//        $sharedNamespace = '';
//
//        if ($shared->getFilters() !== null) {
//            $filters = $shared->getFilters();
//        }
//        if ($shared->getPath() !== null) {
//            $sharedPath = $shared->getPath();
//        }
//        if ($shared->getNamespace() !== null) {
//            $sharedNamespace = $shared->getNamespace();
//        }
//
//        foreach ($list as $item) {
//            $callback = $item->getCallback();
//            if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
//                $callback[0] = $sharedNamespace . $callback[0];
//            }
//            $route_rule = new ArkRouterRestfulRule(
//                $item->getMethod(),//$item[self::ROUTE_PARAM_METHOD],
//                $sharedPath . $item->getPath(),//$item[self::ROUTE_PARAM_PATH],
//                $callback,
//                $filters
//            );
//            $this->registerRouteRule($route_rule);
////            $this->registerRestfulRouteRule($route_rule);
//        }
//        return $this;
//    }

    /**
     * @param string $urlBase 'xx/'
     * @param string $namespace '\a\b\c' without tail
     * @param string[] $filters array of class name
     * @return $this
     * @since 3.1.0
     */
    public function loadAutoRestfulControllerRoot($urlBase, $namespace, $filters = [])
    {
        $this->registerRouteRule(
            new ArkRouterAutoRestfulRule(
                ArkWebInput::METHOD_ANY,
                $urlBase,
                $namespace,
                $filters
            )
        );
        return $this;
    }

    /**
     * @param string $directory __DIR__ . '/../controller'
     * @param string $urlBase "XX/"
     * @param string $controllerNamespaceBase '\sinri\sample\controller\\'
     * @param string[] $filters ['\sinri\sample\filter\AuthFilter']
     * @return ArkRouter
     */
    public function loadAllControllersInDirectoryAsCI($directory, $urlBase = '', $controllerNamespaceBase = '', $filters = [])
    {
        if (!file_exists($directory) || !is_dir($directory)) {
            if ($this->debug) {
                $this->logger->debug(__METHOD__ . '@' . __LINE__ . " warning: this is not a directory: " . $directory);
            }
        } elseif ($handle = opendir($directory)) {
            if (
                $this->defaultControllerName
                && file_exists($directory . '/' . $this->defaultControllerName . '.php')
                && $this->defaultMethodName
                && method_exists($controllerNamespaceBase . $this->defaultControllerName, $this->defaultMethodName)
            ) {
                $this->any(
                    $urlBase . '?',
                    [$controllerNamespaceBase . $this->defaultControllerName, $this->defaultMethodName],
                    $filters
                );
            }
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (is_dir($directory . '/' . $entry)) {
                        //DIR,
                        $this->loadAllControllersInDirectoryAsCI(
                            $directory . '/' . $entry,
                            $urlBase . $entry . '/',
                            $controllerNamespaceBase . $entry . '\\',
                            $filters
                        );
                    } else {
                        //FILE
                        $list = explode('.', $entry);
                        $name = isset($list[0]) ? $list[0] : '';
                        if (
                            $this->defaultMethodName
                            && method_exists($controllerNamespaceBase . $name, $this->defaultMethodName)
                        ) {
                            $this->any(
                                $urlBase . $name . '/?',
                                [$controllerNamespaceBase . $name, $this->defaultMethodName],
                                $filters
                            );
                        }
                        $this->loadController(
                            $urlBase . $name . '/',
                            $controllerNamespaceBase . $name,
                            $filters
                        );
                    }
                }
            }
            closedir($handle);
        }
        return $this;
    }

    /**
     * @param string $basePath "" or "xx/"
     * @param string $controllerClass full class describer
     * @param string[] $filters
     * @return ArkRouter
     */
    public function loadController($basePath, $controllerClass, $filters = [])
    {
        try {
            $method_list = get_class_methods($controllerClass);
            $reflector = new ReflectionClass($controllerClass);
            foreach ($method_list as $method) {
                if (strpos($method, '_') === 0) {
                    continue;
                }
                $path = $basePath . $method;
                $parameters = $reflector->getMethod($method)->getParameters();
                $after_string = "";
                $came_in_default_area = false;
                if (!empty($parameters)) {
                    foreach ($parameters as $param) {
                        if ($param->isDefaultValueAvailable()) {
                            $path .= "(";
                            $after_string .= ")?";
                            $came_in_default_area = true;
                        } elseif ($came_in_default_area) {
                            //non-default after default
                            if ($this->debug) {
                                $this->logger->debug(__METHOD__ . '@' . __LINE__ . " ROUTE SETTING ERROR: required-parameter after non-required-parameter");
                            }
                            return $this;
                        }
                        $path .= '/{' . $param->name . '}';
                    }
                    $path .= $after_string;
                }
                $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_ANY, $path, [$controllerClass, $method], $filters);
//                $this->registerRouteRule($route_rule);
                $this->registerRouteRule($route_rule);
                if ($method == $this->defaultMethodName) {
                    $basePathX = $basePath;
                    if (strlen($basePathX) > 0) {
                        $basePathX = substr($basePathX, 0, strlen($basePathX) - 1);
                    }
                    $route_rule = new ArkRouterRestfulRule(ArkWebInput::METHOD_ANY, $basePathX, [$controllerClass, $method], $filters);
//                    $this->registerRouteRule($route_rule);
                    $this->registerRouteRule($route_rule);
                }
            }
        } catch (Exception $exception) {
            // do nothing if class not exist
        }
        return $this;
    }
}