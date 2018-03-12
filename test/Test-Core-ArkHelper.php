<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 11:26
 */

use sinri\ark\core\ArkHelper;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../autoload.php';

// readTarget($target,$keychain,$default=null,$regex=null,&$exception=null)
echo "TEST READ TARGET ... " . PHP_EOL;

$read_target = [
    'a' => 'A',
    'b' => [
        "b1" => "B",
        "b2" => json_decode('{"b3":"B3","b4":["b4-0","b4-1"]}'),
    ],
    "c" => 1,
];

$cases = [
    ['a', null, null, 'A'],
    ['aa', 'x', null, 'x'],
    [['b', 'b1'], null, null, 'B'],
    [['b', 'b1'], 'D', '/^C$/', 'D'],
    [['b', 'b2', 'b3'], null, null, 'B3'],
    [['b', 'b2', 'b4', 1], null, null, 'b4-1'],
];

foreach ($cases as $case_index => $case) {
    echo "CASE [\t{$case_index}] ";
    $exception = new Exception('NOT REFRESH EXCEPTION', -1);
    $result = ArkHelper::readTarget($read_target, $case[0], $case[1], $case[2], $exception);
    if ($result === $case[3]) {
        echo "CORRECT.";
    } else {
        echo "ERROR!";
    }
    echo " # " . ($exception ? $exception->getMessage() : 'EXCEPTION FREE') . PHP_EOL;
}

// assertItem($object, $type=self::ASSERT_TYPE_NOT_EMPTY, $exception_message=null)

echo "TEST ASSERT ITEM ... " . PHP_EOL;

$cases = [
    // [object,TYPE MASK,SHOULD THROW]
    [null, ArkHelper::ASSERT_TYPE_NOT_EMPTY, true],
    [null, ArkHelper::ASSERT_TYPE_NOT_NULL, true],
    [null, ArkHelper::ASSERT_TYPE_NOT_FALSE, false],
    [null, ArkHelper::ASSERT_TYPE_NOT_NULL | ArkHelper::ASSERT_TYPE_NOT_FALSE, true],
    [false, ArkHelper::ASSERT_TYPE_NOT_EMPTY, true],
    [false, ArkHelper::ASSERT_TYPE_NOT_NULL, false],
    [false, ArkHelper::ASSERT_TYPE_NOT_FALSE, true],
    [false, ArkHelper::ASSERT_TYPE_NOT_NULL | ArkHelper::ASSERT_TYPE_NOT_FALSE, true],
    [0, ArkHelper::ASSERT_TYPE_NOT_EMPTY, true],
    [0, ArkHelper::ASSERT_TYPE_NOT_NULL, false],
    [0, ArkHelper::ASSERT_TYPE_NOT_FALSE, false],
    [0, ArkHelper::ASSERT_TYPE_NOT_NULL | ArkHelper::ASSERT_TYPE_NOT_FALSE, false],
    ['0', ArkHelper::ASSERT_TYPE_NOT_EMPTY, true],// it is special
    ['0', ArkHelper::ASSERT_TYPE_NOT_NULL, false],
    ['0', ArkHelper::ASSERT_TYPE_NOT_FALSE, false],
    ['0', ArkHelper::ASSERT_TYPE_NOT_NULL | ArkHelper::ASSERT_TYPE_NOT_FALSE, false],
    ['', ArkHelper::ASSERT_TYPE_NOT_EMPTY, true],
    ['', ArkHelper::ASSERT_TYPE_NOT_NULL, false],
    ['', ArkHelper::ASSERT_TYPE_NOT_FALSE, false],
    ['', ArkHelper::ASSERT_TYPE_NOT_NULL | ArkHelper::ASSERT_TYPE_NOT_FALSE, false],
];
foreach ($cases as $case_index => $case) {
    echo "CASE [\t{$case_index}] ";
    try {
        ArkHelper::assertItem($case[0], $case[1], "DIED FOR CASE " . $case_index . ", " . $case[0] . ', ' . $case[1]);
        if ($case[2]) {
            echo "WRONG (should throw)";
        } else {
            echo "CORRECT";
        }
    } catch (\Exception $exception) {
        if (!$case[2]) {
            echo "WRONG (should not throw)";
        } else {
            echo "CORRECT";
        }
        echo " EXCEPTION: " . $exception->getMessage();
    }
    echo PHP_EOL;
}

ArkHelper::quickNotEmptyAssert("quick not empty assert failed", 0, 1, false, null, '', '0', '00', 000, 0x00, 0b00, ' ', []);

//turnListToMapping

echo "Test For turnListToMapping ... " . PHP_EOL;

$result = ArkHelper::turnListToMapping([
    ["id" => "A", "value" => "aaa"], ["id" => "B", "value" => "bbb"],
], "id");
print_r($result);