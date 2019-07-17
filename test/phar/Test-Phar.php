<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/17
 * Time: 21:38
 */

use sinri\ark\core\ArkHelper;
use sinri\ark\phar\PharMaker;

require_once __DIR__ . '/../../vendor/autoload.php';

if (!ArkHelper::isCLI()) {
    echo "CLI...CLI...CLI..." . PHP_EOL;
    exit(1);
}

$pm = new PharMaker();
$pm->setPharName("ArkTestSuit");
$pm->setDirectory(__DIR__ . '/../../');
$pm->setOutputDirectory(__DIR__);
$pm->setBootstrapStubAsCLIEntrance("test/cli/runner.php");
$pm->addExtension("php");
//$pm->addExcludeEntrance("composer.phar");
$pm->archive();