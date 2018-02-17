<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/17
 * Time: 21:38
 */

require_once __DIR__ . '/../../autoload.php';

if (!\sinri\ark\core\ArkHelper::isCLI()) {
    echo "CLI...CLI...CLI..." . PHP_EOL;
    exit(1);
}

$pm = new \sinri\ark\phar\PharMaker();
$pm->setPharName("ArkTestSuit");
$pm->setDirectory(__DIR__ . '/../../');
$pm->setOutputDirectory(__DIR__);
$pm->setBootstrapStubAsCLIEntrance("test/cli/runner.php");
$pm->addExtension("php");
//$pm->addExcludeEntrance("composer.phar");
$pm->archive();