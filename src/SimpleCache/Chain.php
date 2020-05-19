<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\SimpleCache;

use Psr\SimpleCache;

final class Chain extends AbstractAdapter
{
    /**
     * @var \Psr\SimpleCache\CacheInterface[]
     */
    private $adapters;

    private function __construct(SimpleCache\CacheInterface ...$adapters)
    {
        $this->adapters = $adapters;
    }

    public static function create(SimpleCache\CacheInterface ...$adapters): self
    {
        return new self(...$adapters);
    }

    public function addAdapter(SimpleCache\CacheInterface $adapter): self
    {
        $this->adapters[] = $adapter;
        return $this;
    }

    public function flush(): bool
    {
        foreach ($this->adapters as $adapter) {
            $adapter->clear();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function multiGet(array $keys, $default = null): \Generator
    {
        $results = [];
        foreach ($this->adapters as $adapter) {
            $results = iterator_to_array($adapter->getMultiple($keys, $default));
            if (array_filter($results, fn($val) => $val == $default) === []) {
                break;
            }
        }

        foreach ($results as $key => $result) {
            yield $key => $result;
        }
    }

    protected function multiSave(array $values, int $ttl = null): bool
    {
        $statuses = [];
        foreach ($this->adapters as $adapter) {
            $statuses[] = $adapter->setMultiple($values, $ttl);
        }

        return !in_array(false, $statuses);
    }

    protected function multiDelete(array $keys): bool
    {
        foreach ($this->adapters as $adapter) {
            $adapter->deleteMultiple($keys);
        }

        return true;
    }

    protected function exists(string $key): bool
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->has($key)) {
                return true;
            }
        }

        return false;
    }
}
