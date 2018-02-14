<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 18:27
 */

namespace sinri\ark\test\cli;


use sinri\ark\cli\ArkCliProgram;

class TestProgramA extends ArkCliProgram
{
    public function actionDefault()
    {
        echo __METHOD__ . PHP_EOL;
    }

    public function actionMain($p1, $p2 = 'B')
    {
        echo __METHOD__ . '(' . $p1 . ',' . $p2 . ')' . PHP_EOL;
    }
}