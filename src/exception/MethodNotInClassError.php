<?php


namespace sinri\ark\exception;


use Exception;
use Throwable;

class MethodNotInClassError extends Exception
{
    public function __construct(string $method = '', string $className = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct("Method {$method} is not found in target class {$className}!", $code, $previous);
    }
}