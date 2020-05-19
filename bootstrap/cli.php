<?php

declare(strict_types=1);

require_once './vendor/autoload.php';

$memcached = new \Memcached();
$memcached->addServer(getenv('MEMCACHE_HOST') ?: '127.0.0.1', getenv('MEMCACHE_PORT') ?: 11211);
$memcached->flush();

$redis = new \Redis();
$redis->pconnect(getenv('REDIS_HOST') ?: '127.0.0.1', getenv('REDIS_PORT') ?: 6379, 30);
$redis->flushAll();

$memcachedAdapter = RemotelyLiving\PHPCacheAdapter\SimpleCache\Memcached::create($memcached);
$redisAdapter = RemotelyLiving\PHPCacheAdapter\SimpleCache\Redis::create($redis);
$memoryAdapter = RemotelyLiving\PHPCacheAdapter\SimpleCache\Memory::create();
$chainAdapter = RemotelyLiving\PHPCacheAdapter\SimpleCache\Chain::create($memcachedAdapter, $redisAdapter, $memoryAdapter);
$cacheItemPool = RemotelyLiving\PHPCacheAdapter\CacheItemPool\CacheItemPool::createFromSimpleCache($chainAdapter);