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

`composer config repo.packagist composer https://packagist.laravel-china.org`

> And every living substance was destroyed which was upon the face of the ground, both man, and cattle, and the creeping things, and the fowl of the heaven; and they were destroyed from the earth: and Noah only remained [alive], and they that [were] with him in the ark. (Genesis 7:23)

## Environment

Ark requests PHP 7.
If you need Redis, you might need to declare the reference of `predis/predis`.
Since version 2.1, Ark-Core and Ark-Curl use version 2.
Since version 2.4, Ark-Cache use version 2 to support PSR-16.

## Toolkit Map

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


## Donation

BitCoin/BTC: 18wCjV8mnepDpLzASKdW7CGo6U8F9rPuV4

Alipay Account:

![Alipay](https://ourbible.net/assets/img/AlipayUkanokan258.png)


