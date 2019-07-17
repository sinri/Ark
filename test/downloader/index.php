<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2019-01-29
 * Time: 21:50
 */

require_once __DIR__ . '/../../vendor/autoload.php';

try {
    Ark()->webOutput()->downloadFileIndirectly(__FILE__);
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}