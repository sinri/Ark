<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 14:15
 */

namespace sinri\ark;


use sinri\ark\io\WebInputHelper;

class TheArk
{
    private static $instance;

    private function __construct()
    {
        // do nothing
    }

    /**
     * @return TheArk
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new TheArk();
        }
        return self::$instance;
    }

    // -----------

    public function webInputHelper()
    {
        static $webInputHelper = null;
        if (!$webInputHelper) {
            $webInputHelper = new WebInputHelper();
        }
        return $webInputHelper;
    }
}