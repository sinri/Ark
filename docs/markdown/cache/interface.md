# ArkCache Interface

Ark provides an general cache handler interface called `ArkCache`.
Cached objects is organized as Key-Value pairs, 
the key should be an string matching regular expression `^[A-Za-z0-9_]+$` 
and the value should be representable by JSON string.
Each cached object should have one certain lifetime,
and should not be treated as valid cache after deadline, and should be removed.

It simply defined four operations on cache:

1. `saveObject` with key, value and lifetime.
1. `getObject` by key, those has passed the lifetime would not be sought.
1. `removeObject` by key.
1. `removeExpiredObjects` which has passed the lifetime.

## Implementations

There are three implementations of ArkCache provided:

* Dummy Cache
* File Cache
* Redis Cache

You might refer to document of each for details.
The dummy cache is designed for fallback use when other cache service is not available,
or used when you want to put a cache handler into code but not decide which yet.