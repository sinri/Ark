<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 20:57
 */

namespace sinri\ark\web;


abstract class ArkRequestFilter
{
    /**
     * @param $class_name string class name with namespace
     * @return ArkRequestFilter
     */
    public static function makeInstance($class_name)
    {
        if (!empty($class_name)) {
            try {
                if (class_exists($class_name)) {
                    return new $class_name();
                } else {
                    return self::generateFilter(true, 'Required a filter not existed.', 500);
                }
            } catch (\Exception $exception) {
                return self::generateFilter(true, 'Exception in requiring a filter: ' . $exception->getMessage(), 500);
            }
        }
        // if not given, just make an anonymous implementation
        return self::generateFilter(false, null, 200);
    }

    protected static function generateFilter($shouldDeny, $injectError, $injectCode)
    {
        return new class($shouldDeny, $injectError, $injectCode) extends ArkRequestFilter
        {
            protected $shouldDeny;
            protected $injectError;
            protected $injectCode;

            /**
             *  constructor.
             * @param boolean $shouldDeny
             * @param string $injectError
             * @param int $injectCode
             */
            public function __construct($shouldDeny, $injectError, $injectCode)
            {
                $this->shouldDeny = $shouldDeny;
                $this->injectError = $injectError;
                $this->injectCode = $injectCode;
            }

            public function shouldAcceptRequest($path, $method, $params, &$preparedData = null, &$responseCode = 200, &$error = null)
            {
                $responseCode = $this->injectCode;
                $error = $this->injectError;//"The Ark is locked now.";
                return !$this->shouldDeny;
            }

            /**
             * Give filter a name for Error Report
             * @return string
             */
            public function filterTitle()
            {
                return "Auto-Generated Ark Filter";
            }
        };
    }

    /**
     * Check request data with $_REQUEST, $_SESSION, $_SERVER, etc.
     * And decide if the request should be accepted.
     * If return false, the request would be thrown.
     * You can pass anything into $preparedData, that controller might use it (not sure, by the realization)
     * @param $path
     * @param $method
     * @param $params
     * @param null $preparedData @since 1.3.6
     * @param int $responseCode @since 2.2.0
     * @param null $error @since 2.2.0
     * @return bool
     */
    abstract public function shouldAcceptRequest($path, $method, $params, &$preparedData = null, &$responseCode = 200, &$error = null);

    /**
     * Give filter a name for Error Report
     * @return string
     */
    abstract public function filterTitle();

    /**
     * You can use this as `hasPrefixAmong($path,['/AdminSession/login','/FileAgent/getFile/'])`
     * as the paths like '/AdminSession/login' and '/FileAgent/getFile/xxx' would return true.
     * Only pure string (case sensitive) would be taken as check rule.
     * Anyway, you may think about the shared prefix, such as '/AdminSession/loginAgain', returns true too.
     * But you should know, all the urls are designed by yourself, you can design them to avoid side effects.
     * @param $path
     * @param array $prefixList
     * @return bool
     */
    public static function hasPrefixAmong($path, $prefixList = [])
    {
        foreach ($prefixList as $prefix) {
            if (0 === strpos($path, $prefix)) {
                return true;
            }
        }
        return false;
    }

}