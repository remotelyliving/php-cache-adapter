<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\SimpleCache;

use Psr\SimpleCache;
use RemotelyLiving\PHPCacheAdapter\Assertions;

final class APCu extends AbstractAdapter
{
    private function __construct()
    {
        Assertions::assertExtensionLoaded('apcu');
        ini_set('apc.use_request_time', '0');
    }

    public static function create(): SimpleCache\CacheInterface
    {
        return new self();
    }

    public function flush(): bool
    {
        return \apcu_clear_cache();
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidArgument MoreSpecificImplementedParamType
     */
    protected function multiGet(array $keys, $default = null): \Generator
    {
        $results = \apcu_fetch($keys);
        foreach ($keys as $key) {
            yield $key => $results[$key] ?? $default;
        }
    }

    protected function multiSave(array $values, int $ttl = null): bool
    {
        \apcu_store($values, null, (int) $ttl);

        return true;
    }

    protected function multiDelete(array $keys): bool
    {
        return \apcu_delete(new \APCUIterator($keys));
    }

    protected function exists(string $key): bool
    {
        return \apcu_exists($key);
    }
}
