# Ark Redis Cache

If you use Redis for cache, you might be able to use `ArkRedisCache`.

Note, you might require another library `predis/predis` in your own project.
This library is an dev-level requirement of Ark.

## Code Sample

```php
$cache=new \sinri\ark\cache\implement\ArkRedisCache("redis.sample.com",6379,255,"password");

$cache->saveObject("key","value",3600);
echo $cache->getObject("key").PHP_EOL;
$cache->removeObject("key");

// it matters not as Redis does this job automatically.
$cache->removeExpiredObjects();

// besides the above methods defined by the interface
// the methods below are special for Redis.

$cache->append("key","value_appended_to_the_tail_of_old_value");
$cache->increase("key",1);
$cache->decrease("key",1);
$cache->increaseFloat("key",0.5);
```