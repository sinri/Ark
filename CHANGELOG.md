# Change Log of Ark

### Version 1.7.1

Add File Mode parameter in `ArkFileCache` constructor.

### Version 1.7.0

Use `phpmailer/phpmailer` 6.x instead of `sinri/smallphpmailer`.

### Version 1.6.0

Add the daemon and handler standard for queue.

### Version 1.5.0

> Take attention when upgrading from 1.4.x or earlier versions.

Parse the incoming user agent by `Jenssegers\Agent\Agent`.
Auto detect MIME for Content Type Header by `Mimey\MimeTypes`.
Make class parameter of `ArkRequestFilter` to be `string` or `string[]`.
Rename router quick method `option` to be `options`.
Huge changes on `ArkRouterRule` series.

### Version 1.4.2

Fix Curl Header Bug.

### Version 1.3

Fix HTTP Method `OPTIONS` definition.

### Version 1.2

Conveniences methods in ArkWebController.
Fix bugs for web services.
Removed deprecated methods.

----

### Version 0.11 (as 1.0-alpha)

Add quick not empty assert for conveniences.
PDO Table Compare.
And other refines.

### Version 0.10

Changed Ark Database Cluster and added MySQLi Adapter.

### Version 0.9

Remove 'force logging to STDOUT' feature in ArkLogger and designed `echo` instead.
`ArkPDO` treat database config as optional.
`ArkCurl` support setting raw options of CURL.
Great changes on Web Router and Session Management.
ETC., a lot of changes.

### Version 0.8

Made `ArkMailer` an interface and move the original implementation as `ArkSMTPMailer`.

### Version 0.7

Refine ArkLogger with prefix strict rule.
Phar Maker.

### Version 0.6

Refine for DryDockOfArk.

### Version 0.5

* The SQLite3 Support.
* Cache Interface and implementations with File and Redis.

### Version 0.4

* The CLI mode program support.

### Version 0.3

* The database table model.

### Version 0.2

* The Web Router (inherited from Enoch and inspired by both CI and Lumen).
* Email Helper.
* Curl Helper.

### Version 0.1

* The basic helper of PHP developing.
* A logger follows the rule of PSR/3.
* Web Input and Output Helpers.
