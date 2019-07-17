<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 18:03
 */

namespace sinri\ark\cli;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;

class ArkCliProgram
{
    protected $logger;

    public function __construct()
    {
        $this->logger = ArkLogger::makeSilentLogger();
    }

    /**
     * @param $methodName
     * @param array $parameters
     * @throws Exception
     */
    public function call($methodName, $parameters = [])
    {
        $method = 'action' . $methodName;
        if (!method_exists($this, $method)) {
            throw new Exception("No such method: " . $method);
        }
        call_user_func_array([$this, $method], $parameters);
    }

    public function actionDefault()
    {
        echo "It is a default method and should be overrode.";
    }

    /**
     * @param string $baseNamespace the shared namespace prefix with \\ in tail.
     */
    public static function run($baseNamespace)
    {
        global $argc;
        global $argv;

        $program_instance_name = ArkHelper::readTarget($argv, 1);

        if (empty($program_instance_name)) {
            echo "It is an unknown program name!" . PHP_EOL;
            exit(1);
        }

        $program_instance_name = str_replace('/', '\\', $program_instance_name);

        // $baseNamespace like '\\sinri\\enoch\\test\\Enos\\'
        $program_instance_full_name = $baseNamespace . $program_instance_name;
        $program = new $program_instance_full_name();

        $action = ArkHelper::readTarget($argv, 2, 'Default');

        $params = [];
        for ($i = 3; $i < $argc; $i++) {
            $params[] = $argv[$i];
        }

        call_user_func_array([$program, 'action' . $action], $params);
    }
}