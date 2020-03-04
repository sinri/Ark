<?php


namespace sinri\ark\web\implement;


use ReflectionClass;
use ReflectionException;
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
     * @param string $path
     * @param string $namespace
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
     * @return bool
     */
    public function checkIfMatchRequest($method, $incomingPath)
    {
//        echo __METHOD__.'@'.__LINE__.' debug beginning!'.PHP_EOL;
//        var_dump($method); // GET
//        var_dump($incomingPath); // '/xxxx' or '/', wonder ''
//        var_dump($this->path);

        if (substr($incomingPath, 0, 1) === '/') {
            $incomingPath = substr($incomingPath, 1);
        }

        if (!$this->checkIfMatchMethod($method)) return false;

        if (0 !== stripos($incomingPath, $this->path)) {
            return false;
        }
        $incomingPath = substr($incomingPath, strlen($this->path));

        $components = explode('/', $incomingPath);
        $components = array_filter($components);// not url-decoded yet
        $components = array_values($components);
//        var_dump($components);

        if (empty($components) || count($components) < 2) {
            // it might be the root, plz use a manual restful rule
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
            if (class_exists($className, false)) {
                // great!
                $this->callback[0] = $className;
                break;
            }
        }
        if (empty($this->callback)) {
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
                return false;
            }
            $this->callback[1] = $foundMethod->getName();
            $this->setParsed($parameters);
        } catch (ReflectionException $e) {
            return false;
        }

        // if match, would execute
        // call_user_func_array($this->callback,[$components]);

        return true;
    }

}