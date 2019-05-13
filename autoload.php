<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 11:06
 */

// FOR DEV/TEST USE ONLY

//require_once __DIR__ . '/core/ArkHelper.php';
use sinri\ark\core\ArkHelper;
use sinri\ark\TheArk;

require_once __DIR__ . '/vendor/autoload.php';

ArkHelper::registerAutoload(
    "sinri\ark",
    __DIR__,
    ".php"
);

if (!function_exists('Ark')) {
    function Ark()
    {
        return TheArk::getInstance();
    }
}