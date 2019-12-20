<?php


namespace sinri\ark\test\web\controller;


use sinri\ark\web\implement\ArkWebController;

class RestfulController extends ArkWebController
{
    public function api($p1, $p2, $p3 = 'default')
    {
        echo "This is a sample restful api with parameters: " . json_encode([$p1, $p2, $p3]) . PHP_EOL;
    }
}