<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/25
 * Time: 10:35
 */

use sinri\ark\TheArk;

if (!function_exists('Ark')) {
    function Ark()
    {
        return TheArk::getInstance();
    }
}