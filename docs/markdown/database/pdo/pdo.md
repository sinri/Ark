# Ark PDO

Class `ArkPDO` is the PDO support component of Ark.

## Construct and Connect with configuration

It is recommended to build an instance of `ArkPDO` like that:

```php
$db = new \sinri\ark\database\ArkPDO();
$db->setPdoConfig($config); // $config is an instance of `\sinri\ark\database\ArkPDOConfig`
$db->setLogger($logger);// $logger is optional, and should be an instance of `\sinri\ark\core\ArkLogger` if you need log.
$db->connect(); // you must run this once to make the connection available.
```

As configuration and logger, refer to documents of each. 
By default, it uses a silent logger.

## Quote

To defence your database and program from SQL-Injection, you should use proper quotation on users' input.
This job is normally done by using PDO SQL parameter design, but we provide you two choices to manually do it.

* method `quote` of an instance, which implemented by PDO;
* static method `dryQuote`, which is a set of rules of replacement on special characters. 

## Direct Query with SQL

You can use `ArkPDO` to run SQL directly with methods:

1. getAll
1. getCol
1. getRow
1. getOne
1. exec
1. insert

## Safely Query with SQL and Parameters

You might run SQL with parameters for higher safety.

1. safeQueryAll
1. safeQueryRow
1. safeQueryOne
1. safeInsertOne
1. safeExecute

## Query with Manually Built Safe SQL

A special way to query complex SQL with parameters is to build a safe SQL and run it directly.
Commonly you should quote each parameter manually.
Besides, `ArkPDO` provided you an method called `safeBuildSQL`.
It parses a more terrific kind of SQL template into real SQL with parameters. 

### Template Embedding Rule

The rules of replacement are listed, the left are the embeded and the right are the operation on the parameter `$p`.

 * `?` => $p
 *  ?  => quote($p)
 * (?) => (quote($p[]),...)
 * [?] => integer_value($p)
 * {?} => float_value($p)
 
 ### Sample SQL:
 
 ```
 select key_field,value,`?`
 from `?`.`?`
 where key_field in (?)
 and status = ?
 limit [?] , [?]
 ```
 
 ## Assist Methods
 
 * getAffectedRowCount
 * getLastInsertID
 
 ## Transaction
 
 It is simple.
 
 * beginTransaction
 * commit
 * rollBack
 * inTransaction
 
 ## Fetch PDO Error
 
 You can read PDO raw errors with:
 
 * getPDOErrorInfo
 * getPDOErrorCode