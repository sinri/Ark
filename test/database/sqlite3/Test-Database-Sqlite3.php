<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 19:42
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../autoload.php';

$logger = new \sinri\ark\core\ArkLogger(__DIR__ . '/../../log', 'sqlite');
$db_file = __DIR__ . '/../../log/test-sqlite3.db';
$db = new \sinri\ark\database\sqlite\ArkSqlite3($db_file);

$created = $db->exec("CREATE TABLE COMPANY(
   ID INTEGER PRIMARY KEY   AUTOINCREMENT,
   NAME           TEXT      NOT NULL,
   AGE            INT       NOT NULL,
   ADDRESS        CHAR(50),
   SALARY         REAL
);");
$logger->info('created table', [$created]);

$id = $db->insertWithLastInsertId("INSERT INTO COMPANY (NAME,AGE,ADDRESS,SALARY)
VALUES ( 'Paul', 32, 'California', 20000.00 );");
$logger->info('inserted one', [$id]);
$id = $db->insertWithLastInsertId("INSERT INTO COMPANY (NAME,AGE,ADDRESS,SALARY)
VALUES ('Allen', 25, 'Texas', 15000.00 );");
$logger->info('inserted another', [$id]);

$all = $db->safeReadAll("SELECT id,name FROM company");
$logger->info('select all', [$all]);

$dropped = $db->exec("DROP TABLE COMPANY");
$logger->info('dropped table', [$dropped]);

unlink($db_file);