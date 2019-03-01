# Start to use Ark II

Ark should be referred as library installed through Composer,
with command `composer require sinri/ark` or add require item in file `composer.json`.
Ark could be used in various ways as following described,
and a general entrance is provided as `Ark()`.

## Using Independent Components

You can use Ark as independent components to help you on coding.

### Itself

Library Ark itself provides you

* CLI Support
* Web IO
* Phar Packaging Support

### Core

Ark relies on `sinri/ark-core` to provide basic functions. 

* Autoload Support with PSR-0/4 Standard
* Operation on object/array
* Assertion
* Logging

### Cache

Ark relies on `sinri/ark-cache`, which provides standard cache interface, simple File System based implementation and a dummy.

### Database

* sinri/ark-pdo
* sinri/ark-mysqli
* sinri/ark-sqlite3
* sinri/ark-couchdb

### Redis

* sinri/ark-redis

### Email

* sinri/ark-mail

### CURL

* sinri/ark-curl

### QR Code

* sinri/ark-qr-builder


### Queue Daemon

* sinri/ark-queue

You can refer to the documents for each components as Packagist Reliance.

## Web Service

> WARNING: Dry Dock is out of date now (which is use version 1.x).

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