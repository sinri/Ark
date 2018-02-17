# CLI Program

PHP supports CLI mode and PHP script could be run in CLI mode.
Ark provides you class `ArkCliProgram` to organize CLI programs.

## Place Program Classes

Make your programs as `ArkCliProgram` instances under one root namespace,
and place them into target directories following the PSR-4 rule by auto-loader (or you might require them manually). 

## Runner

Your runner for CLI programs is just one line, such as,

```php
$base_namespace='\sinri\ark\test\cli'.'\\'
\sinri\ark\cli\ArkCliProgram::run($base_namespace);
```

And the base namespace is defined by the previous step.

## Call with arguments

Assume your runner is `runner.php`,
your program might be called as 

```bash
php runner.php [SUB_NAMESPACE/]CLASS_NAME [ACTION_NAME] [PARAMETERS...]
``` 

The sub namespace use '/' if needed.
The action name is the method name without prefix `action`, keep upper case.
The default action is `actionDefault` so action name is `Default`.
The parameters are defined as action method parameters.
If the parameters have default value, it could be ignored from tail.

