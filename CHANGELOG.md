# Change Log of Ark

### pre Version 0.9

Remove 'force logging to STDOUT' feature in ArkLogger and designed `echo` instead.
`ArkPDO` treat database config as optional.
`ArkCurl` support setting raw options of CURL.

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
