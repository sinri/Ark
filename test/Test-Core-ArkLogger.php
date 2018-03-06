<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 16:59
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../autoload.php';

$logger = new \sinri\ark\core\ArkLogger(__DIR__ . '/log', 'core-logger-test');
$logger->debug(\Psr\Log\LogLevel::DEBUG, [\Psr\Log\LogLevel::DEBUG]);
$logger = new \sinri\ark\core\ArkLogger(__DIR__ . '/log', 'core-logger-test');
$logger->critical(\Psr\Log\LogLevel::CRITICAL, [\Psr\Log\LogLevel::CRITICAL]);

// validate prefix
$logger = new \sinri\ark\core\ArkLogger(__DIR__ . '/log', 'Aa0/Bb1');
$logger->setIgnoreLevel(\Psr\Log\LogLevel::ERROR);
$logger->alert("file prefix should be Aa0_Bb1");