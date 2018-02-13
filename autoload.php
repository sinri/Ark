<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 11:06
 */

require_once __DIR__ . '/core/ArkHelper.php';

\sinri\ark\core\ArkHelper::registerAutoload(
    "sinri\ark",
    __DIR__,
    ".php"
);

if (!function_exists('Ark')) {
    function Ark()
    {
        return \sinri\ark\TheArk::getInstance();
    }
}