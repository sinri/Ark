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
    /**
     * @var ArkLogger
     */
    protected $logger;

    public function __construct()
    {
        $this->logger = ArkLogger::makeSilentLogger();
    }

    /**
     * @param string $action
     * @param null|array $parameters
     * @since 3.1.5
     */
    public function initializeLogger($action, $parameters = null)
    {
        // it might be overrode to initialize logger for each
        // here is a writer to STDOUT
        $this->logger = new ArkLogger();
    }

    /**
     * @param string $methodName
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
     * It is used to build the runner script. @param string $baseNamespace the shared namespace prefix. Till 2.8.2, ensure the '\\' in tail.
     * @see test/cli/runner.php
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
        if (substr($baseNamespace, strlen($baseNamespace) - 1) !== '\\') {
            $baseNamespace .= '\\';
        }
        $program_instance_full_name = $baseNamespace . $program_instance_name;

        $action = ArkHelper::readTarget($argv, 2, 'Default');

        $params = [];
        for ($i = 3; $i < $argc; $i++) {
            $params[] = $argv[$i];
        }

        $program = new $program_instance_full_name();

        call_user_func_array([$program, 'initializeLogger'], [$action, $params]);
        call_user_func_array([$program, 'action' . $action], $params);
    }
}