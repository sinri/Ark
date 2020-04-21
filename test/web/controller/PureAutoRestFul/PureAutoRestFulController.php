<?php


namespace sinri\ark\test\web\controller\PureAutoRestFul;


use sinri\ark\web\implement\ArkWebController;

class PureAutoRestFulController extends ArkWebController
{

    public function api()
    {
        echo __METHOD__ . '@' . __LINE__ . PHP_EOL;
    }
}