<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 17:51
 */

namespace sinri\ark\web;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;

class ArkWebService
{
    /**
     * @var bool
     */
    protected $debug;
    /**
     * @var ArkLogger
     */
    protected $logger;
    /**
     * @var ArkRouter
     */
    protected $router;

    /**
     * @var string
     */
    protected $requestSerial;
    /**
     * @var string
     */
    protected $gateway;
    protected $filterGeneratedData;

    public function __construct()
    {
        $this->requestSerial = uniqid();
        $this->gateway = "index.php";
        $this->logger = ArkLogger::makeSilentLogger();
        $this->debug = false;
        $this->router = new ArkRouter();
        $this->filterGeneratedData = null;
    }

    /**
     * @return string
     */
    public function getRequestSerial(): string
    {
        return $this->requestSerial;
    }

    /**
     * @return ArkRouter
     */
    public function getRouter(): ArkRouter
    {
        return $this->router;
    }

    /**
     * @return null
     */
    public function getFilterGeneratedData()
    {
        return $this->filterGeneratedData;
    }

    /**
     * @param string $sessionDir
     */
    public function startPHPSession($sessionDir)
    {
        ArkWebSession::sessionStart($sessionDir);
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param string $gateway
     */
    public function setGateway(string $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param ArkLogger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function handleRequest()
    {
        if (ArkHelper::isCLI()) {
            $this->handleRequestForCLI();
            return;
        }
        $this->handleRequestForWeb();
    }

    public function handleRequestForCLI()
    {
        global $argc;
        global $argv;
        try {
            // php index.php [PATH] [ARGV]
            $path = ArkHelper::readTarget($argv, 1, null);
            if ($path === null) {
                $this->logger->error("PATH EMPTY", [$path]);
                return;
            }
            $arguments = [];
            for ($i = 2; $i < $argc; $i++) {
                $arguments[] = $argv[$i];
            }
            $route = $this->router->seekRoute($path, Ark()->webInput()->getRequestMethod());
            $code = 0;
            $route->execute($path, $this->filterGeneratedData, $code);
        } catch (Exception $exception) {
            $this->logger->error("Exception in " . __METHOD__ . " : " . $exception->getMessage());
        }
    }

    public function handleRequestForWeb()
    {
        try {
            $this->dividePath($path_string);
            $route = $this->router->seekRoute($path_string, Ark()->webInput()->getRequestMethod());
            $code = 200;
            $route->execute($path_string, $this->filterGeneratedData, $code);
        } catch (Exception $exception) {
            $this->router->handleRouteError($exception->getMessage(), $exception->getCode());
            if ($this->debug) {
                echo "<pre>" . PHP_EOL . print_r($exception, true) . "</pre>" . PHP_EOL;
            }
        }
    }

    /**
     * @param string $pathString It would be as output.
     * @return string[] array Array of components
     */
    protected function dividePath(&$pathString = '')
    {
        $sub_paths = array();
        if (ArkHelper::isCLI()) {
            global $argv;
            global $argc;
            for ($i = 1; $i < $argc; $i++) {
                $sub_paths[] = $argv[$i];
            }
            return $sub_paths;
        }

        $fullPathString = $this->fetchControllerPathString();
        $tmp = explode('?', $fullPathString);
        $pathString = isset($tmp[0]) ? $tmp[0] : '';
        $pattern = '/^\/([^\?]*)(\?|$)/';
        $r = preg_match($pattern, $pathString, $matches);
        if (!$r) {
            // https://github.com/sinri/enoch/issues/1
            // this bug (return '' which is not an array) fixed since v1.0.2
            return [''];
        }
        $controller_array = explode('/', $matches[1]);
        if (count($controller_array) > 0) {
            $sub_paths = array_filter($controller_array, function ($var) {
                return $var !== '';
            });
            $sub_paths = array_values($sub_paths);
        }

        return $sub_paths;
    }

    protected function fetchControllerPathString()
    {
        $prefix = $_SERVER['SCRIPT_NAME'];
        //$delta=10;//original
        $delta = strlen($this->gateway) + 1;

        if (
            (strpos($_SERVER['REQUEST_URI'], $prefix) !== 0)
            && (strrpos($prefix, '/' . $this->gateway) + $delta == strlen($prefix))
        ) {
            $prefix = substr($prefix, 0, strlen($prefix) - $delta);
        }

        return substr($_SERVER['REQUEST_URI'], strlen($prefix));
    }

    /**
     * If you decide to use PHP Session, please run this before the code to handle request.
     * @param $sessionDir
     */
    public function startSession($sessionDir)
    {
        ArkWebSession::sessionStart($sessionDir);
    }

    /**
     * @param string $pathPrefix no leading and tail '/'
     * @param string $baseDirPath
     * @param ArkRequestFilter[] $filters
     * @param null|callable $fileHandler e.g. function($realPath):void if return false, execute default downloader @since 2.7.2
     * @since 2.7.1
     * Set up a quick readonly FTP-like file system viewer,
     * binding a uri path prefix to a file system path prefix.
     */
    public function setupFileSystemViewer($pathPrefix, $baseDirPath, $filters = [], $fileHandler = null)
    {
        $this->router->get($pathPrefix, function ($components) use ($fileHandler, $pathPrefix, $baseDirPath) {
            if (count($components) === 1 && $components[0] === '') {
                $components = [];
            }
            $baseDirPath = realpath($baseDirPath);
            if ($baseDirPath === false) {
                throw new Exception("Resource Configuration Error!", 500);
            }
            $rawPath = $baseDirPath . (empty($components) ? "" : "/" . implode("/", $components));
            $realPath = realpath($rawPath);
            if ($realPath === false || $realPath !== $rawPath) {
                throw new Exception("Illegal Resource Index!", 400);
            }
            if (!file_exists($realPath)) {
                throw new Exception("Resource Not Found!", 404);
            }
            if (is_dir($realPath)) {
                // if dir path not ends with / add one to its tail
                $parts = explode("?", $_SERVER['REQUEST_URI']);
                $path = $parts[0];
                if (substr($path, strlen($path) - 1, 1) !== '/') {
                    header("Location: " . $path . '/' . (count($parts) > 1 ? "?" . $parts[1] : ""));
                    return;
                }
                echo "<!doctype html>" . PHP_EOL;
                echo "<head>" . PHP_EOL
                    . "<meta charset='UTF-8'>" . PHP_EOL
                    . "<title>~/" . implode("/", $components) . " - Ark File System Viewer</title>" . PHP_EOL
                    . "<style>" . PHP_EOL
                    . "table th,td {" . PHP_EOL
                    . "padding: 5px 10px;" . PHP_EOL
                    . "}" . PHP_EOL
                    . "</style>" . PHP_EOL
                    . "</head>" . PHP_EOL;
                // list
                echo "<body>" . PHP_EOL;
                echo "<h1>Ark File System Viewer</h1>" . PHP_EOL;
                echo "<p>You are here: ~/" . implode("/", $components) . "</p>" . PHP_EOL;
                echo "<hr>" . PHP_EOL;
                echo "<table>" . PHP_EOL;
                echo "<tr>"
                    . "<th>Name</th>"
                    . "<th>Size</th>"
                    . "<th>Created</th>"
                    . "<th>Modified</th>"
                    . "<th>Accessed</th>"
                    . "</tr>" . PHP_EOL;
                $dir = opendir($realPath);
                while ($item = readdir($dir)) {
                    if ($item === '.') continue;
                    if ($item === '..' && empty($components)) {
                        continue;
                    }

                    $fileStat = stat($realPath . '/' . $item);

                    $fileSize = $fileStat[7];//filesize($realPath.'/'.$item);
                    $lastAccessTime = date("Y-m-d H:i:s", $fileStat[8]);
                    $lastModificationTime = date("Y-m-d H:i:s", $fileStat[9]);
                    $lastCreateTime = date("Y-m-d H:i:s", $fileStat[10]);
                    echo "<tr>"
                        . "<td>" . "<a href='./{$item}'>{$item}</a> " . "</td>"
                        . "<td>{$fileSize} bytes</td>"
                        . "<td>{$lastCreateTime}</td>"
                        . "<td>{$lastModificationTime}</td>"
                        . "<td>{$lastAccessTime}</td>"
                        . "</tr>" . PHP_EOL;
                }
                closedir($dir);
                echo "</table>" . PHP_EOL;
                echo "<hr>" . PHP_EOL;
                echo "<div>" . PHP_EOL
                    . "This page is generated on " . date('Y-m-d H:i:s') . ", "
                    . "powered by Framework <a href='https://github.com/sinri/Ark' target='_blank'>sinri/ark</a>."
                    . "</div>" . PHP_EOL;
                echo "</body>" . PHP_EOL;
                echo "</html>" . PHP_EOL;
            } else {
                // show content
                if (is_callable($fileHandler)) {
                    //$extension=pathinfo($realPath,PATHINFO_EXTENSION);
                    call_user_func_array($fileHandler, [$realPath]);
                } else {
                    Ark()->webOutput()->downloadFileIndirectly($realPath);
                }
            }
        }, $filters, true);
    }
}