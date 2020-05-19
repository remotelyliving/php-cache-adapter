<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Exceptions;

use Psr\Cache;
use Psr\SimpleCache;

class RuntimeError extends \RuntimeException implements Cache\CacheException, SimpleCache\CacheException
{
    public static function extensionNotLoaded(string $extension): self
    {
        return new self("Extension $extension is not loaded");
    }
}
