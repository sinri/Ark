# Core - Logger

Ark provides you class `ArkLogger`, which follows the PSR-3 standard to handle logging requirement.

Log would be written into certain directory and would be able to be ignored by level.
Besides, a static method is provided to generate a complete silent ArkLogger instance: `ArkLogger::makeSilentLogger()`.

## Initialization

You should build up an instance of ArkLogger first.

```php
new ArkLogger($targetLogDir, $prefix, $rotateTimeFormat, $buffer);
```

When here explains the options.

There are two ways to output logs:

1. `log` to write files; if the target file or storage directory is not correctly existing, fall back to
2. `echo` contents to Standard Output directly.

### Storage Directory (`$targetLogDir`)

The directory to storage the log files.
If the directory path is not existed, ArkLogger would try to run `mkdir` to create it.
If it is omitted or as default value `null`, all the visible logs would be printed to Standard Output. 

### Log File Naming (`$prefix` and `$rotateTimeFormat`)

If you want to store log files of more than one kinds, you might need to set prefix for each logger.
Also, the logging content would be rotated (split) into different files according to the time.
You can set the rotate time format, following the `date` method parameter standard.
The default value is `Y-m-d`.

The log file name would be like

* log-2018-02-17.log by default without prefix,
* log-PREFIX-2018-02-17.log with prefix.

Note, prefix accepts characters `[A-Za-z0-9]`, others would be replaced by `_`.
  

### Buffer (`$buffer`)

Buffer is supported since Ark 2.3.

By default, the buffer is turned off.
If you need to batch process logs, for any reasons, you can turn on the buffer,
by set the buffer parameter to an instance of `ArkLoggerBuffer`.

To initialize an instance of `ArkLoggerBuffer`, three parameters are needed.

* Buffer Size: an integer as the size of the cached logs, by default as 100.
* Buffer Flusher: a callable descriptor or just an anonymous function, such as `function(ArkLoggerBufferItem[] $items):bool`, to handle the logs in cache when cache is full.
* Buffer Only: a boolean, by default as false. If this is set to `true`, logs would not be printed to Standard Output nor target file.

## Logging

### Log Levels and Ignoring

Logs are categorized into several levels.
ArkLogger holds one ignore level,
the logs reported with a level lower than this would be ignored.
It is set to `INFO` by default, you might change it to another. 

#### Lv. Emergency

System is unusable.

#### Lv. Alert

Action must be taken immediately.

Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.

#### Lv. Critical

Critical conditions.

Example: Application component unavailable, unexpected exception.
 

#### Lv. Error

Runtime errors that do not require immediate action but should typically be logged and monitored.

#### Lv. Warning

Exceptional occurrences that are not errors.

Example: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.

#### Lv. Notice

Normal but significant events.

#### Lv. Info

Interesting events.

#### Lv. Debug

Detailed debug information.

### Logging

ArkLogger provides methods for each log level, with two parameters:

1. Log Content, a string.
1. Log Context, an array, optional, would be output as json encoded.

## Code Sample

A standard instance sample.

```php
$storage=__DIR__ . '/log';
$prefix='PREFIX';

$logger = new \sinri\ark\core\ArkLogger($storage, $prefix);

$logger->setIgnoreLevel(\Psr\Log\LogLevel::ERROR);

$logger->critical("Event which is ".\Psr\Log\LogLevel::CRITICAL, ["level"=>\Psr\Log\LogLevel::CRITICAL]);
``` 

For some components which contain ArkLogger embedded,
the default logger would be a silent logger by 

```php 
ArkLogger::makeSilentLogger();
```

It won't output anything to terminal or file.