<?php

use sinri\ark\io\curl\ArkCurl;

require_once __DIR__ . '/../../vendor/autoload.php';

$baseUrl = "http://localhost/phpstorm/Ark/test/web";

$curl = new ArkCurl();

$testCases = [
    'root' => [
        'method' => 'GET',
        'url' => "/",
    ],
    'restful-full-parameters' => [
        'method' => 'GET',
        'url' => "/RestfulController/api/A/B/C",
    ],
    'restful-enough-parameters' => [
        'method' => 'GET',
        'url' => "/RestfulController/api/A/B",
    ],
    'restful-lack-parameters' => [
        'method' => 'GET',
        'url' => "/RestfulController/api/A",
    ],
    'free-tail' => [
        'method' => 'GET',
        'url' => '/freeTail/a/b/c'
    ],
    'file-system-dir' => [
        'method' => 'GET',
        'url' => '/fs/'
    ],
    'file-system-file' => [
        'method' => 'GET',
        'url' => '/fs/web/.htaccess'
    ],
    'static-frontend-index' => [
        'method' => 'GET',
        'url' => '/static/frontend/'
    ]
];

foreach ($testCases as $testCaseName => $testCase) {

    $method = $testCase['method'];
    $url = $testCase['url'];

    echo "Test for {$testCaseName} : " . $method . ' ' . $url . PHP_EOL;
    $result = $curl->prepareToRequestURL($method, $baseUrl . $url)
        ->setCURLOption(CURLOPT_HEADER, 1)
        ->execute();
    echo "Result:" . PHP_EOL;
    echo $result . PHP_EOL;
    echo "-----------------------" . PHP_EOL;
}

