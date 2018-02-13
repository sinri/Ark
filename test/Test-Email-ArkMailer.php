<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 17:41
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../autoload.php';

$config = [
    'host' => 'smtp.exmail.qq.com',
    'smtp_auth' => true,
    'username' => '',
    'password' => '',
    'smtp_secure' => 'ssl',
    'port' => 465,
    'display_name' => 'Ark Mailer Tester',
];

$mailer = new \sinri\ark\email\ArkMailer($config);
$sent = $mailer->prepareSMTP()
    ->addReceiver("ljni@leqee.com", 'DANI')
    ->setSubject(__FILE__)
    ->setHTMLBody("<p style='color:red'>" . __LINE__ . "</p>")
    ->finallySend();

var_dump($sent);