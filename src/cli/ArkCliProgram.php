<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 18:03
 */

namespace sinri\ark\cli;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\ark\exception\MethodNotInClassError;

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
     * Called before action in `run`.
     * It might be overridden to initialize logger for each action or the whole.
     * The default is set the logger to write into STDOUT if no writable ArkLogger
     * @param string $action
     * @param null|array $parameters
     * @since 3.1.5
     * @deprecated since 3.4.2 use initializeProgram
     */
    public function initializeLogger(string $action, $parameters = null)
    {
        $this->initializeProgram($action, $parameters);
    }

    /**
     * @param string $action
     * @param null|array $parameters
     * @since 3.4.2
     */
    public function initializeProgram(string $action, &$parameters = null)
    {
        if ($this->logger === null || $this->logger->isSilent()) {
            $this->logger = new ArkLogger(null, $action);
        }
        $this->logger->debug(__METHOD__, ['action' => $action, 'parameters' => $parameters]);
    }

    /**
     * @param string $methodName
     * @param array $parameters
     * @throws MethodNotInClassError
     */
    public function call(string $methodName, $parameters = [])
    {
        $method = 'action' . $methodName;
        if (!method_exists($this, $method)) {
            throw new MethodNotInClassError($method, __CLASS__);
        }
        call_user_func_array([$this, $method], $parameters);
    }

    public function actionDefault()
    {
        echo "It is a default method and should be overrode.";
    }

    /**
     * It is used to build the runner script.
     * @param string $baseNamespace the shared namespace prefix. Till 2.8.2, ensure the '\\' in tail. @see test/cli/runner.php
     */
    public static function run(string $baseNamespace)
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

        // try to use refer since 3.4.3
        $program->initializeProgram($action, $params);
//        call_user_func_array([$program, 'initializeProgram'], [$action, $params]);

        call_user_func_array([$program, 'action' . $action], $params);
    }
}