<?php


namespace sinri\ark\test\web\controller;


use sinri\ark\web\implement\ArkWebController;

class FreeTailController extends ArkWebController
{
    public function handlePath($path = [])
    {
        echo "handle the path: " . json_encode($path) . PHP_EOL;
    }
}