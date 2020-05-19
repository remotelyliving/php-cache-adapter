<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\SimpleCache;

use Psr\SimpleCache;
use RemotelyLiving\PHPCacheAdapter\Assertions;

abstract class AbstractAdapter implements SimpleCache\CacheInterface
{

    private ?int $defaultTTLSeconds = null;

    final public function setDefaultTTLSeconds(?int $seconds): self
    {

        $this->defaultTTLSeconds = $seconds;

        return $this;
    }

    final public function getDefaultTTLSeconds(): ?int
    {

        return $this->defaultTTLSeconds;
    }

    /**
     * @inheritDoc
     */
    final public function get($key, $default = null)
    {

        /** @var \Generator $results */
        $results = $this->getMultiple([$key], $default);
        return $results->current();
    }

    /**
     * @inheritDoc
     */
    final public function getMultiple($keys, $default = null)
    {

        Assertions::assertIterable($keys);
        $normalizedKeys = $this->iterableToArray($keys);
        Assertions::assertValidKeys($normalizedKeys);

        return $this->multiGet($normalizedKeys, $default);
    }

    /**
     * @inheritDoc
     */
    final public function set($key, $value, $ttl = null): bool
    {

        Assertions::assertValidKey($key);

        return $this->setMultiple([$key => $value], $ttl);
    }

    /**
     * @inheritDoc
     */
    final public function setMultiple($values, $ttl = null): bool
    {

        Assertions::assertIterable($values);
        $normalizedValues = $this->iterableToArray($values);
        Assertions::assertValidKeys(array_keys($normalizedValues));

        return $this->multiSave($normalizedValues, $this->normalizeTTLSeconds($ttl));
    }

    /**
     * @inheritDoc
     */
    final public function delete($key): bool
    {

        return $this->deleteMultiple([$key]);
    }

    /**
     * @inheritDoc
     */
    final public function deleteMultiple($keys): bool
    {

        Assertions::assertIterable($keys);
        $normalizedKeys = $this->iterableToArray($keys);
        Assertions::assertValidKeys($normalizedKeys);

        return $this->multiDelete($normalizedKeys);
    }

    /**
     * @inheritDoc
     */
    final public function has($key): bool
    {

        Assertions::assertValidKey($key);
        return $this->exists($key);
    }

    /**
     * @inheritDoc
     */
    final public function clear(): bool
    {

        return $this->flush();
    }

    abstract protected function flush(): bool;

    abstract protected function exists(string $key): bool;

    /**
     * @param array $keys
     * @param mixed $default
     *
     * @return \Generator
     */
    abstract protected function multiGet(array $keys, $default = null): \Generator;

    abstract protected function multiDelete(array $keys): bool;

    abstract protected function multiSave(array $values, ?int $ttl = null): bool;

    private function iterableToArray(iterable $iterable): array
    {

        $array = [];
        foreach ($iterable as $index => $value) {
            $array[$index] = $value;
        }

        return $array;
    }

    /**
     * @param mixed $ttl
     *
     * @throws \RemotelyLiving\PHPCacheAdapter\Exceptions\InvalidArgument
     */
    private function normalizeTTLSeconds($ttl): ?int
    {

        Assertions::assertValidTTL($ttl);

        if (is_null($ttl)) {
            return $this->getDefaultTTLSeconds();
        }

        if (is_int($ttl)) {
            return (int)$ttl;
        }

        return max(0, ($ttl->getTimestamp() - time()));
    }
}
