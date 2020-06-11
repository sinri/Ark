<?php


namespace sinri\ark\web\implement;


use ReflectionClass;
use ReflectionException;
use sinri\ark\core\ArkLogger;
use sinri\ark\web\ArkRouterRule;

/**
 * Class ArkRouterAutoRestfulRule
 * @package sinri\ark\web\implement
 * @since 3.1.0
 */
class ArkRouterAutoRestfulRule extends ArkRouterRule
{
    /**
     * ArkRouterAutoRestfulRule constructor.
     * @param string $method
     * @param string $path no leading /
     * @param string $namespace no ending \
     * @param string[] $filter array of class name
     */
    public function __construct($method, $path, $namespace, $filter = [])
    {
        parent::__construct();

        $this->method = $method;
        $this->path = $path;
        $this->namespace = $namespace;
        $this->filters = $filter;

    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return "ArkRouterAutoRestfulRule";
    }

    /**
     * @param string $method
     * @param string $incomingPath
     * @param null|ArkLogger $logger
     * @return bool
     */
    public function checkIfMatchRequest($method, $incomingPath, $logger = null)
    {
//        echo __METHOD__.'@'.__LINE__.' debug beginning!'.PHP_EOL;
//        var_dump($method); // GET
//        var_dump($incomingPath); // '/xxxx' or '/', wonder ''
//        var_dump($this->path);

        if ($logger) {
            $logger->debug(
                __METHOD__ . '@' . __LINE__ . ' this rule: ' . $this->__toString(),
                [
                    'req_method' => $method,
                    'req_incoming_path' => $incomingPath,
                ]
            );
        }

        if (substr($incomingPath, 0, 1) === '/') {
            $incomingPath = substr($incomingPath, 1);
        }

        if (!$this->checkIfMatchMethod($method)) {
            if ($logger) {
                $logger->debug(__METHOD__ . '@' . __LINE__ . ' Method Not Match, incoming method is ' . $method);
            }
            return false;
        }

        // Fix the bug when `$this->path` is an empty string
        if (strlen($this->path) > 0 && 0 !== stripos($incomingPath, $this->path)) {
            if ($logger) {
                $logger->debug(__METHOD__ . '@' . __LINE__ . ' Incoming Path Not Has Path Prefix i.e. ' . $this->path);
            }
            return false;
        }
        $incomingPath = substr($incomingPath, strlen($this->path));

        $components = explode('/', $incomingPath);
        $components = array_filter($components);// not url-decoded yet
        $components = array_values($components);
//        var_dump($components);

        if (empty($components) || count($components) < 2) {
            // it might be the root, plz use a manual restful rule
            if ($logger) {
                $logger->debug(__METHOD__ . '@' . __LINE__ . ' it might be the root, plz use a manual restful rule');
            }
            return false;
        }

        // confirm class
        $className = $this->namespace;
        $i = 0;
        $this->callback = [];
        while ($i < count($components)) {
//            var_dump($components);
//            var_dump($i);
            $className .= '\\' . $components[$i];
            $i++;
            if (class_exists($className, true)) {
                // great!
                $this->callback[0] = $className;
                break;
            }
        }
        if (empty($this->callback)) {
            if ($logger) {
                $logger->debug(__METHOD__ . '@' . __LINE__ . ' no callback available');
            }
            return false;
        }

        try {
            // confirm method
            $reflector = new ReflectionClass($className);
            $foundMethod = $reflector->getMethod($components[$i]);
            $i++;

            // parameters
            $parameters = array_slice($components, $i);
            if ($foundMethod->getNumberOfRequiredParameters() > count($parameters)) {
                if ($logger) {
                    $logger->debug(__METHOD__ . '@' . __LINE__ . ' no enough parameters');
                }
                return false;
            }
            $this->callback[1] = $foundMethod->getName();
            $this->setParsed($parameters);
        } catch (ReflectionException $e) {
            if ($logger) {
                $logger->debug(__METHOD__ . '@' . __LINE__ . ' reflection error: ' . $e->getMessage());
            }
            return false;
        }

        // if match, would execute
        // call_user_func_array($this->callback,[$components]);

        if ($logger) {
            $logger->debug(__METHOD__ . '@' . __LINE__ . ' MATCHED!', [
                'callback' => $this->callback,
                'parsed' => $this->parsed,
            ]);
        }
        return true;
    }

}