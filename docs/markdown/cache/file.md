# Ark File Cache

`ArkFileCache` is an implementation of `ArkCache` based on file system.

## Sample Code

Create an instance of File Cache Manager.

```php
$cache=new \sinri\ark\cache\implement\ArkFileCache(__DIR__.'/cache');

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