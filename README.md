# Ark

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/sinri/Ark/master/LICENSE) 
[![GitHub release](https://img.shields.io/github/release/sinri/Ark.svg)](https://github.com/sinri/Ark/releases)
[![Packagist](https://img.shields.io/packagist/v/sinri/ark.svg)](https://packagist.org/packages/sinri/ark) 


A fundamental toolkit for PHP 7.

```bash
composer require sinri/ark
```

It is a new generation for [Enoch Project](https://github.com/sinri/enoch), as which might continuously support projects in PHP 5.4+. 

If you have problem to connect to GitHub or too slow, try use mirror:

`composer config repo.packagist composer https://mirrors.aliyun.com/composer/`

> And every living substance was destroyed which was upon the face of the ground, both man, and cattle, and the creeping things, and the fowl of the heaven; and they were destroyed from the earth: and Noah only remained [alive], and they that [were] with him in the ark. (Genesis 7:23)

## Environment

Ark requests PHP 7.
If you need Redis, you might need to declare the reference of `predis/predis`.
Since version 2.1, Ark-Core and Ark-Curl use version 2.
Since version 2.4, Ark-Cache use version 2 to support PSR-16.
Since version 3.3, Ark-Web became independent as 1.0.0.
Now Ark has been in 3.x.

## Toolkit Map

### Components

Basic Functions

* `sinri/ark-core` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-core.svg)](https://packagist.org/packages/sinri/ark-core)
* `sinri/ark-web` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-web.svg)](https://packagist.org/packages/sinri/ark-web)
* `sinri/ark-curl` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-curl.svg)](https://packagist.org/packages/sinri/ark-curl)
* `sinri/ark-cache` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-cache.svg)](https://packagist.org/packages/sinri/ark-cache)
* `sinri/ark-xml` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-xml.svg)](https://packagist.org/packages/sinri/ark-xml)

Database Related

* `sinri/ark-pdo` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-pdo.svg)](https://packagist.org/packages/sinri/ark-pdo)
* `sinri/ark-mysqli` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-mysqli.svg)](https://packagist.org/packages/sinri/ark-mysqli)
* `sinri/ark-sqlite3` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-sqlite3.svg)](https://packagist.org/packages/sinri/ark-sqlite3)
* `sinri/ark-couchdb` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-couchdb.svg)](https://packagist.org/packages/sinri/ark-couchdb)
* `sinri/ark-redis` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-redis.svg)](https://packagist.org/packages/sinri/ark-redis)

Email Related

* `sinri/ark-mail` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-mail.svg)](https://packagist.org/packages/sinri/ark-mail)
* `sinri/ark-imap` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-imap.svg)](https://packagist.org/packages/sinri/ark-imap)

Queue Related

* `sinri/ark-queue` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-queue.svg)](https://packagist.org/packages/sinri/ark-queue)
* `sinri/ark-lock` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-lock.svg)](https://packagist.org/packages/sinri/ark-lock)
* `sinri/ark-event` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-event.svg)](https://packagist.org/packages/sinri/ark-event)


Lightweight Directory Access Protocol

* `sinri/ark-ldap` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-ldap.svg)](https://packagist.org/packages/sinri/ark-ldap)

QR Code

* `sinri/ark-qr-builder` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-qr-builder.svg)](https://packagist.org/packages/sinri/ark-qr-builder)

Remote File System Access

* `sinri/ark-ftp` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-ftp.svg)](https://packagist.org/packages/sinri/ark-ftp)
* `sinri/ark-sftp` [![Packagist](https://img.shields.io/packagist/v/sinri/ark-sftp.svg)](https://packagist.org/packages/sinri/ark-sftp)

WebSocket Server

* `sinri/ark-websocket` ![Packagist Version](https://img.shields.io/packagist/v/sinri/Ark-WebSocket)


### Independent Toolkit

Class ArkHelper is designed for the convenience of developing, it help you to operate data structure safely, and some environment shortcuts.

Class ArkLogger is an implementation of [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md).
You might create an instance with certain log storage path, or use a silent one as default.

Class ArkCurl provides a higher level usage of CURL.

Class ArkCache defined the interface of being a cache handler, and implementations by File System and Redis, also the fallback choice Dummy.
(Note, if you want to use Redis, `predis/predis` is needed in `composer.json`.)

Class ArkPDO with model encapsulation are there for you Database Operation.

Class ArkSqlite3 is an extended tool for working on SQLite3.

### Web Toolkit

The main reference is `Ark()`, which would provide a singleton of class TheArk.

For web service, Class TheArk contains:

* Method `webInput`, give the global instance of class ArkWebInput.
* Method `webOutput`, give the global instance of class ArkWebOutput.
* Method `webService`, give the global instance of class ArkWebService.

For general routines, the multi-instance hubs (register and get) are provided:

* Hub for ArkPDO
* Hub for ArkLogger
* Hub for ArkCache

### CLI Toolkit

Class ArkCliProgram is designed to support a whole CLI project with certain namespace and class rule.

### Server Config Reference

If you use Apache to load the project, you need to add the .htaccess file and open the allow override option.

```apacheconfig
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

For Nginx, you should use try_files.

```nginx
server {
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
}
```

### Ark Quick Configuration

```php
<?php
$config=[
    'log'=>[
        'path'=>'/path/to/log',
        'level'=>LogLevel::INFO,
    ],
    'pdo'=>[
        'default'=>[
            "title"=>'Default PDO',
            "host"=>'',
            "port"=>3306,
            "username"=>'',
            "password"=>'',
            "database"=>'',
            // "charset"=>'utf8',
            // "engine"=>'mysql',
            // "options"=>[],
        ]
    ],
    'cache'=>[
        'default'=>[
            'type'=>'FILE',
            'dir'=>'/path/to/cache',
            'mode'=>0777,
        ]
    ]
];
```

## Who use this?

Not so many in fact. Amongst them, Leqee is one.

## Donation

BitCoin/BTC: 18wCjV8mnepDpLzASKdW7CGo6U8F9rPuV4

Alipay Account:

![Alipay](https://ourbible.net/assets/img/AlipayUkanokan258.png)


