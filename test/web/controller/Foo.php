<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 23:22
 */

namespace sinri\ark\test\web\controller;


use sinri\ark\core\ArkHelper;
use sinri\ark\web\implement\ArkWebController;

class Foo extends ArkWebController
{
    public function bar($a, $b = 'B')
    {
        $this->_sayOK([
            'a' => $a,
            'b' => $b,
            'token' => Ark()->webInput()->readRequest('token'),
            'time' => ArkHelper::readTarget($this->filterGeneratedData, ['request_time'], 'unknown'),
        ]);
    }
}