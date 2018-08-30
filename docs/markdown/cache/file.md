# Ark File Cache

`ArkFileCache` is an implementation of `ArkCache` based on file system.

## Sample Code

Create an instance of File Cache Manager.
Since 1.7.1, you can choose the file mode of the generated cache object file to make them editable by process run by another user, such as `0777`.

```php
$cache=new \sinri\ark\cache\implement\ArkFileCache(__DIR__.'/cache');

// Or, since 1.7.1, you might use
// $cache=new \sinri\ark\cache\implement\ArkFileCache(__DIR__.'/cache',0777);

// if your cache storage directory is not available for reading and writing, it behaves like dummy
// $cache=new \sinri\ark\cache\implement\ArkDummyCache();
```



Common Usage

```php
$cache->saveObject("key","value",3600);
echo $cache->getObject("key").PHP_EOL;
$cache->removeObject("key");
$cache->removeExpiredObjects();
```