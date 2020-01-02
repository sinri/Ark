<?php


namespace sinri\ark\test\web\controller;


use sinri\ark\web\implement\ArkWebController;

class InputController extends ArkWebController
{
    public function checkClientIP()
    {
        echo $this->_getInputHandler()->getRequestSourceIP(['::1', '2.2.2.2']) . PHP_EOL;
        var_dump($this->_getInputHandler()->getIpHelper()->readForwardIpLine());
        var_dump($this->_getInputHandler()->getRequestSourceIP());
        echo "<pre>";
        var_dump($_SERVER);
    }

}