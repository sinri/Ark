<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 18:28
 */

// this file is a sample.
// Try to run `php TestProgramA Main A C`

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../autoload.php';

\sinri\ark\cli\ArkCliProgram::run('\sinri\ark\test\cli\\');