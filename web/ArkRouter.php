<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 17:58
 */

namespace sinri\ark\web;


use sinri\ark\core\ArkLogger;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\ArkWebOutput;

class ArkRouter
{
    const ROUTE_PARAM_METHOD = "METHOD";
    const ROUTE_PARAM_PATH = "PATH";
    const ROUTE_PARAM_CALLBACK = "CALLBACK";
    const ROUTE_PARAM_FILTER = "FILTER";
    const ROUTE_PARAM_NAMESPACE = "NAMESPACE";// only used in `group`

    const ROUTE_PARSED_PARAMETERS = "PARSED";// only used in sought result

    protected $debug;
    protected $logger;
    protected $defaultControllerName = null;
    protected $defaultMethodName = null;
    protected $errorHandler = null;
    protected $routes = [];

    public function __construct()
    {
        $this->debug = false;
        $this->logger = ArkLogger::makeSilentLogger();
        $this->defaultControllerName = 'Welcome';
        $this->defaultMethodName = 'index';
        $this->errorHandler = null;
        $this->routes = [];
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param ArkLogger $logger
     */
    public function setLogger(ArkLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param null|string $defaultControllerName
     */
    public function setDefaultControllerName(string $defaultControllerName)
    {
        $this->defaultControllerName = $defaultControllerName;
    }

    /**
     * @param null|string $defaultMethodName
     */
    public function setDefaultMethodName(string $defaultMethodName)
    {
        $this->defaultMethodName = $defaultMethodName;
    }

    /**
     * Give a string as template file path for display-page use;
     * give an anonymous function or a callable definition array which consume one parameter of array,
     * or leave it as null to response JSON.
     * @param null|string|callable $errorHandler
     */
    public function setErrorHandler($errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param array $errorData
     * @param int $http_code @since 1.2.8
     */
    public function handleRouteError($errorData = [], $http_code = 404)
    {
        try {
            http_response_code($http_code);
            if (is_string($this->errorHandler) && file_exists($this->errorHandler)) {
                Ark()->webOutput()->displayPage($this->errorHandler, $errorData);
                return;
            } elseif (is_callable($this->errorHandler)) {
                call_user_func_array($this->errorHandler, [$errorData]);
                return;
            } else {
                Ark()->webOutput()->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON);
                Ark()->webOutput()->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_FAIL, $errorData);
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            echo $exception->getTraceAsString();
        }
    }

    /**
     * Designed after Lumen Routing: https://lumen.laravel-china.org/docs/5.3/routing
     * @param string $method use method constants of ArkWebInput
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param ArkRequestFilter[]|null $filters ArkRequestFilter
     */
    protected function registerRoute($method, $path, $callback, $filters = null)
    {
        if ($this->debug) {
            $this->logger->debug(__METHOD__, [$method, $path, $callback, $filters]);
        }
        $path = preg_replace('/\//', '\/', $path);
        $matched = preg_match_all('/\{([^\/]+)\}/', $path, $matches);
        if ($this->debug) {
            $this->logger->debug("Regex Route Variable Components Matched: " . json_encode($matches));
        }
        if ($matched) {
            $regex = preg_replace('/\{([^\/]+)\}/', '([^\/]+)', $path);
        } else {
            $regex = $path;
        }
        $regex = '/^\/' . $regex . '$/';
        $new_route = [
            self::ROUTE_PARAM_METHOD => $method,
            self::ROUTE_PARAM_PATH => $regex,
            self::ROUTE_PARAM_CALLBACK => $callback,
            self::ROUTE_PARAM_FILTER => $filters,
        ];
        if ($this->debug) {
            $this->logger->debug("New Regex Route: " . json_encode($new_route));
        }
        array_unshift($this->routes, $new_route);
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param ArkRequestFilter[]|null $filters ArkRequestFilter
     */
    public function get($path, $callback, $filters = null)
    {
        $this->registerRoute(ArkWebInput::METHOD_GET, $path, $callback, $filters);
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param ArkRequestFilter[]|null $filters ArkRequestFilter
     */
    public function post($path, $callback, $filters = null)
    {
        $this->registerRoute(ArkWebInput::METHOD_POST, $path, $callback, $filters);
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param ArkRequestFilter[]|null $filters ArkRequestFilter
     */
    public function put($path, $callback, $filters = null)
    {
        $this->registerRoute(ArkWebInput::METHOD_PUT, $path, $callback, $filters);
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param ArkRequestFilter[]|null $filters ArkRequestFilter
     */
    public function patch($path, $callback, $filters = null)
    {
        $this->registerRoute(ArkWebInput::METHOD_PATCH, $path, $callback, $filters);
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param ArkRequestFilter[]|null $filters ArkRequestFilter
     */
    public function delete($path, $callback, $filters = null)
    {
        $this->registerRoute(ArkWebInput::METHOD_DELETE, $path, $callback, $filters);
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param ArkRequestFilter[]|null $filters ArkRequestFilter
     */
    public function option($path, $callback, $filters = null)
    {
        $this->registerRoute(ArkWebInput::METHOD_OPTION, $path, $callback, $filters);
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param ArkRequestFilter[]|null $filters ArkRequestFilter
     */
    public function head($path, $callback, $filters = null)
    {
        $this->registerRoute(ArkWebInput::METHOD_HEAD, $path, $callback, $filters);
    }

    /**
     * @param $path
     * @param $method
     * @return mixed
     * @throws \Exception
     */
    public function seekRoute($path, $method)
    {
        // a possible fix in 2.1.4
        if (strlen($path) > 1 && substr($path, strlen($path) - 1, 1) == '/') {
            $path = substr($path, 0, strlen($path) - 1);
        } elseif ($path == '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            $route_regex = $route[self::ROUTE_PARAM_PATH];
            $route_method = $route[self::ROUTE_PARAM_METHOD];
            if ($this->debug) {
                $this->logger->debug(__METHOD__ . " TRY TO MATCH RULE: [$route_method][$route_regex][$path]");
            }
            if (!empty($route_method) && stripos($route_method, $method) === false) {
                if ($this->debug) {
                    $this->logger->debug(__METHOD__ . " ROUTE METHOD NOT MATCH [$method]");
                }
                continue;
            }
            if (preg_match($route_regex, $path, $matches)) {
                // @since 1.2.8 the shift job moved here
                if (!empty($matches)) array_shift($matches);
                $matches = array_filter($matches, function ($v) {
                    return substr($v, 0, 1) != '/';
                });
                $matches = array_values($matches);
                array_walk($matches, function (&$v) {
                    $v = urldecode($v);
                });
                $route[self::ROUTE_PARSED_PARAMETERS] = $matches;
                if ($this->debug) {
                    $this->logger->debug(__METHOD__ . " MATCHED with " . json_encode($matches));
                }
                return $route;
            }
        }
        throw new \Exception("No route matched: path={$path} method={$method}");
    }

    public function group($shared, $list)
    {
        $filters = null;
        $sharedPath = '';
        $sharedNamespace = '';
        if (isset($shared[self::ROUTE_PARAM_FILTER])) {
            $filters = $shared[self::ROUTE_PARAM_FILTER];
        }
        if (isset($shared[self::ROUTE_PARAM_PATH])) {
            $sharedPath = $shared[self::ROUTE_PARAM_PATH];
        }
        if (isset($shared[self::ROUTE_PARAM_NAMESPACE])) {
            $sharedNamespace = $shared[self::ROUTE_PARAM_NAMESPACE];
        }

        foreach ($list as $item) {
            $callback = $item[self::ROUTE_PARAM_CALLBACK];
            if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
                $callback[0] = $sharedNamespace . $callback[0];
            }
            $this->registerRoute(
                $item[self::ROUTE_PARAM_METHOD],
                $sharedPath . $item[self::ROUTE_PARAM_PATH],
                $callback,
                $filters
            );
        }
    }

    /**
     * @param string $directory __DIR__ . '/../controller'
     * @param string $urlBase "XX/"
     * @param string $controllerNamespaceBase '\sinri\sample\controller\\'
     * @param string $filters '\sinri\sample\filter\AuthFilter'
     */
    public function loadAllControllersInDirectoryAsCI($directory, $urlBase = '', $controllerNamespaceBase = '', $filters = '')
    {
        if (!file_exists($directory) || !is_dir($directory)) {
            if ($this->debug) {
                $this->logger->debug(__METHOD__ . " warning: this is not a directory: " . $directory);
            }
            return;
        }
        if ($handle = opendir($directory)) {
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
                            $urlBase . $entry . '/',
                            $controllerNamespaceBase . $entry . '\\',
                            $filters
                        );
                    } else {
                        //FILE
                        $list = explode('.', $entry);
                        $name = isset($list[0]) ? $list[0] : '';
                        //$ppp=method_exists($controllerNamespaceBase . $name,$this->default_method_name);
                        //echo "ppp=".json_encode($ppp).PHP_EOL;
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
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param ArkRequestFilter[]|null $filters ArkRequestFilter
     */
    public function any($path, $callback, $filters = null)
    {
        $this->registerRoute(null, $path, $callback, $filters);
    }

    /**
     * @param $basePath
     * @param $controllerClass
     * @param null $filters
     */
    public function loadController($basePath, $controllerClass, $filters = null)
    {
        try {
            $method_list = get_class_methods($controllerClass);
            $reflector = new \ReflectionClass($controllerClass);
            foreach ($method_list as $method) {
                if (strpos($method, '_') === 0) {
                    continue;
                }
                $path = $basePath . $method;
                $parameters = $reflector->getMethod($method)->getParameters();
                $after_string = "";
                $came_in_default_area = false;
                if (!empty($parameters)) {
                    //self::ROUTER_TYPE_REGEX
                    foreach ($parameters as $param) {
                        if ($param->isDefaultValueAvailable()) {
                            $path .= "(";
                            $after_string .= ")?";
                            $came_in_default_area = true;
                        } elseif ($came_in_default_area) {
                            //non-default after default
                            if ($this->debug) {
                                $this->logger->debug("ROUTE SETTING ERROR: required-parameter after non-required-parameter");
                            }
                            return;
                        }
                        $path .= '/{' . $param->name . '}';
                    }
                    $path .= $after_string;
                }
                $this->registerRoute(null, $path, [$controllerClass, $method], $filters);
                if ($method == $this->defaultMethodName) {
                    $basePathX = $basePath;
                    if (strlen($basePathX) > 0) {
                        $basePathX = substr($basePathX, 0, strlen($basePathX) - 1);
                    }
                    $this->registerRoute(null, $basePathX, [$controllerClass, $method], $filters);
                }
            }
        } catch (\Exception $exception) {
            // do nothing if class not exist

        }
    }
}