[![Build Status](https://travis-ci.com/remotelyliving/php-cache-adapter.svg?branch=master)](https://travis-ci.org/remotelyliving/php-cache-adapter)
[![Total Downloads](https://poser.pugx.org/remotelyliving/php-cache-adapter/downloads)](https://packagist.org/packages/remotelyliving/php-cache-adapter)
[![Coverage Status](https://coveralls.io/repos/github/remotelyliving/php-cache-adapter/badge.svg?branch=master)](https://coveralls.io/github/remotelyliving/php-cache-adapter?branch=master) 
[![License](https://poser.pugx.org/remotelyliving/php-cache-adapter/license)](https://packagist.org/packages/remotelyliving/php-cache-adapter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/remotelyliving/php-cache-adapter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/remotelyliving/php-cache-adapter/?branch=master)

# php-cache-adapter: 

### 💰 A PSR-6 and PSR-16 Cache Implementation For PHP 💰

### Use Cases

If you want a lightweight, no frills PSR-6 / PSR-16 cache Memcache, Redis, or Memory adapter, this is for you.
This library as born out of the idea that many other libraries offer way too much functionality for cache adapters
and end up being overly complex or underperformant.

This library seeks to address the three most common cache storage mechanisms in the PHP ecosystem and not much more.

### Installation

```sh
composer require remotelyliving/php-cache-adapter
```

### Usage

#### [SimpleCache](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-16-simple-cache.md)

Simple Cache is the PSR-16 implementation of a simple cache adapter. 
Below are the different adapters you can create and use.

```php

// Memcached flavor Simple Cache
$memcached = new \Memcached();
$memcached->addServer($memcacheHost, $memcachePort);
$memcachedAdapter = RemotelyLiving\PHPCacheAdapter\SimpleCache\Memcached::create($memcached);

// Redis Simple Cache
$redis = new \Redis();
$redis->pconnect($redisHost, $redisPort, $timeout);
$redisAdapter = RemotelyLiving\PHPCacheAdapter\SimpleCache\Redis::create($redis);

// Memory / Runtime Simple Cache
$memoryAdapter = RemotelyLiving\PHPCacheAdapter\SimpleCache\Memory::create($maxItemsForArray); // can set max items to keep in array

// APCu
$apcu = RemotelyLiving\PHPCacheAdapter\SimpleCache\APCu::create();

// Chain adapter calls through all adapters until values are found in order of FIFO
// so here we would check memory first, then Memcache, then Redis
$chainAdapter = RemotelyLiving\PHPCacheAdapter\SimpleCache\Chain::create($memoryAdapter, $memcachedAdapter, $redisAdapter);
```

#### [CacheItemPool](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-6-cache.md)

Cache Item Pool is the PSR-6 implementation this library provides. You can create a CacheItemPool with a cache extension OR any of the PSR-16 Simple Cache adapters.

```php

$memcached = new \Memcached();
$memcached->addServer('127.0.0.1', 11211);


$redis = new \Redis();
$redis->pconnect('127.0.0.1', 6379, 30);

$memcacheCacheItemPool = RemotelyLiving\PHPCacheAdapter\CacheItemPool\CacheItemPool::createMemcached($memcached, 'namespace');
// OR
$redisCacheItemPool = RemotelyLiving\PHPCacheAdapter\CacheItemPool\CacheItemPool::createRedis($redis, 'namespace');
// OR
$inMemoryCacheItemPool = RemotelyLiving\PHPCacheAdapter\CacheItemPool\CacheItemPool::createMemory($maxItems, 'namespace');
// OR
$apcuCacheItemPool = RemotelyLiving\PHPCacheAdapter\CacheItemPool\CacheItemPool::createAPCu();
// OR
$cacheItemPool = RemotelyLiving\PHPCacheAdapter\CacheItemPool\CacheItemPool::createFromSimpleCache($chainAdapter, 'namespace');
// OR
$chain = RemotelyLiving\PHPCacheAdapter\CacheItemPool\ChainBuilder::create('namespace', 300)
    ->addMemory()
    ->addAPCu()
    ->addMemcached($memcached)
    ->addRedis($redis)
    ->build();
```

#### Future Development

- Consider adding a filesystem storage mechanism