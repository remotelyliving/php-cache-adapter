<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Unit;

use PHPUnit;

abstract class AbstractTestCase extends PHPUnit\Framework\TestCase
{
    protected function arrayToGenerator(array $array): \Generator
    {
        foreach ($array as $index => $value) {
            yield $index => $value;
        }
    }

    protected function generatorToArray(\Generator $generator): array
    {
        return iterator_to_array($generator);
    }
}
