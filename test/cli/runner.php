<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 18:28
 */

// this file is a sample.
// Try to run `php TestProgramA Main A C`

use sinri\ark\cli\ArkCliProgram;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../autoload.php';

ArkCliProgram::run('\sinri\ark\test\cli\\');