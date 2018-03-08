<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/3/8
 * Time: 13:31
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../autoload.php';

$config_mysqli = new \sinri\ark\database\mysql\ArkMySQLiConfig();
require __DIR__ . '/config.php';

$db = new \sinri\ark\database\mysql\ArkMySQLi($config_mysqli);

try {
    $db->connect();

    $result = $db->getInstanceOfMySQLi()->query("SELECT * FROM ecshop.ecs_order_info LIMIT 2");
    \sinri\ark\core\ArkHelper::assertItem($result, 'result null', \sinri\ark\core\ArkHelper::ASSERT_TYPE_NOT_EMPTY);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    do {
        print_r($row);
    } while ($row = $result->fetch_array(MYSQLI_ASSOC));

    echo "DONE" . PHP_EOL;
} catch (Exception $e) {
    echo "ERR" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
} finally {
    echo "FIN" . PHP_EOL;
    $db->closeConnection();
}
