<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCacheAdapter\Tests\Unit;

use RemotelyLiving\PHPCacheAdapter\Assertions;
use RemotelyLiving\PHPCacheAdapter\Exceptions;

class AssertionsTest extends AbstractTestCase
{
    public function testAssertExtensionLoaded(): void
    {
        $this->expectException(Exceptions\RuntimeError::class);
        $this->expectExceptionMessage('Extension nonexistentextension is not loaded');

        Assertions::assertExtensionLoaded('nonexistentextension');
    }
}
