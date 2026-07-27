<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\FileName\Tests;

use Elavora\Api\DataTypes\Filesystem\FileName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FileNameFilesystemSafetyTest extends TestCase
{
    #[DataProvider('controlCharacters')]
    public function testRejectsControlCharactersInAnyPosition(string $control): void
    {
        self::assertFalse(FileName::isValid($control . 'arquivo.txt'));
        self::assertFalse(FileName::isValid('arquivo' . $control . '.txt'));
        self::assertFalse(FileName::isValid('arquivo.txt' . $control));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function controlCharacters(): iterable
    {
        yield 'NUL' => ["\0"];
        yield 'newline' => ["\n"];
        yield 'tab' => ["\t"];
        yield 'DEL' => ["\x7F"];
    }

    public function testRejectsWindowsDeviceNamesWithOrWithoutExtension(): void
    {
        $reservedNames = ['CON', 'prn', 'Aux', 'nul'];
        for ($number = 1; $number <= 9; $number++) {
            $reservedNames[] = 'COM' . $number;
            $reservedNames[] = 'lpt' . $number;
        }

        foreach ($reservedNames as $reservedName) {
            self::assertFalse(FileName::isValid($reservedName));
            self::assertFalse(FileName::isValid($reservedName . '.txt'));
        }
    }

    public function testAcceptsPortableNamesWithoutChangingThem(): void
    {
        self::assertSame('relatorio.pdf', FileName::from('relatorio.pdf')->value());
        self::assertSame('meu relatorio.pdf', FileName::from('meu relatorio.pdf')->value());
        self::assertSame('relatorio-東京.pdf', FileName::from('relatorio-東京.pdf')->value());
        self::assertTrue(FileName::isValid(str_repeat('a', 255)));
        self::assertFalse(FileName::isValid(str_repeat('a', 256)));
    }

    #[DataProvider('invalidNames')]
    public function testPreservesExistingInvalidNameRules(mixed $value): void
    {
        self::assertFalse(FileName::isValid($value));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function invalidNames(): iterable
    {
        yield 'empty' => [''];
        yield 'current directory' => ['.'];
        yield 'parent directory' => ['..'];
        yield 'hidden file' => ['.env'];
        yield 'trailing dot' => ['arquivo.'];
        yield 'trailing space' => ['arquivo.txt '];
        yield 'slash' => ['pasta/arquivo.txt'];
        yield 'backslash' => ['pasta\\arquivo.txt'];
        yield 'colon' => ['arquivo:txt'];
        yield 'asterisk' => ['arquivo*.txt'];
        yield 'question mark' => ['arquivo?.txt'];
        yield 'double quote' => ['arquivo".txt'];
        yield 'angle brackets' => ['arquivo<1>.txt'];
        yield 'pipe' => ['arquivo|1.txt'];
        yield 'non string' => [123];
    }
}
