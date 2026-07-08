<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\FileName\Tests;

use Elavora\Api\DataTypes\Filesystem\FileName;
use PHPUnit\Framework\TestCase;

final class FileNameTest extends TestCase
{
    public function testValidatesFileName(): void
    {
        self::assertTrue(FileName::isValid('user.png'));
        self::assertFalse(FileName::isValid('.env'));
        self::assertFalse(FileName::isValid('user/name.png'));
    }
}
