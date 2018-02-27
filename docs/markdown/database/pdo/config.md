# Configuration of Ark PDO Component

Ark defined class `ArkPDOConfig` to hold the configuration information to connect to a database.

There are 8 fields defined:

* host
* port
* username
* password
* database
* charset
* engine
* options

## Engine

Ark supports PDO based on the practice of MySQL, so only const `mysql` provided.
However, you can use any value else defined by PDO if supported.

## Database

From [PHP PDO MYSQL DOCUMENT](http://php.net/manual/en/ref.pdo-mysql.connection.php#89499):

> I have tested this and found that the "dbname" field is optional.  Which is a good thing if you must first create the db.
> After creating a db be sure to exec a "use dbname;"  command, or else use fully specified table references.

We use this strategic as well by calling `use DBNAME;` after constructed the PDO instance, 
rather than write scheme name into DSN directly.

## Charset

A const `utf8` is provided.
By default, it is empty, but class `ArkPDO` would treat empty charset setting as using `utf8`. 

## Options

By default it is `null`.

If the engine was set to be `mysql`, and the options was kept as `null`,
the options would be modified to be `[\PDO::ATTR_EMULATE_PREPARES => false]`.

Note, as Sinri commented on [PHP PDO Class Document](http://php.net/manual/en/class.pdo.php),

> For some Database Environment, such as Aliyun DRDS (Distributed Relational Database Service), cannot process preparing for SQL. 
> For such cases, the option `\PDO::ATTR_EMULATE_PREPARES` should be set to true. If you always got reports about "Failed to prepare SQL" while this option were set to false, you might try to turn on this option to emulate prepares for SQL.