# Start to use Ark

Ark should be referred as library installed through Composer,
with command `composer require sinri/ark` or add require item in file `composer.json`.
Ark could be used in various ways as following described.

## Using Independent Components

You can use Ark as independent components to help you on coding.
Ark provides components on:

* operation on object/array
* assertion
* log
* cache
* database (mysql and sqlite3)
* email
* curl
* session
* CLI support

You can refer to the documents for each components.

## Web Service

To build up a web service, it is recommended to use a sample project of [Dry Dock of Ark](https://github.com/sinri/DryDockOfArk). 

```bash
composer create-project sinri/dry-dock-of-ark YourProjectName
```

It is a official sample for a whole site.
You may refer to its document for the design and extension.
Ark supports the web service with Web Request Router and Filter Architecture.

For details, refer to the relative documents.

## CLI Program

If you want to build up a whole CLI program, which means to access various functions with arguments.
With class `ArkCliProgram`, you just need a single entrance PHP script called like 'runner.php'.

```php
\sinri\ark\cli\ArkCliProgram::run('sinri\DryDockOfArk\program\\');
```

For details, refer to the relative documents.