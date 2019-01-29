<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2019-01-29
 * Time: 21:50
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../autoload.php';

try {
    Ark()->webOutput()->downloadFileIndirectly("./atom-mac.zip");
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}