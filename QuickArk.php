<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/25
 * Time: 10:35
 */

if (!function_exists('Ark')) {
    function Ark()
    {
        return \sinri\ark\TheArk::getInstance();
    }
}