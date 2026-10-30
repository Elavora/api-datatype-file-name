<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\FileName\Tests;

use Elavora\Api\DataTypes\Filesystem\FileName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regras de docs/USO.md ("Validação do nome de arquivo" e "Criação e entrada inválida").
 */
final class FileNameTest extends TestCase
{
    private const MESSAGE = 'Valor invalido para Elavora\Api\DataTypes\Filesystem\FileName.';

    /** @return iterable<string, array{string}> */
    public static function validProvider(): iterable
    {
        yield 'com extensao' => ['user.png'];
        yield 'com espaco' => ['relatorio final.pdf'];
        yield 'sem extensao' => ['arquivo'];
        yield 'varias extensoes' => ['a.tar.gz'];
        yield '255 bytes' => [str_repeat('a', 255)];
        yield 'ponto no meio' => ['a.b'];
    }

    /** @return iterable<string, array{mixed}> */
    public static function invalidProvider(): iterable
    {
        yield 'vazio' => [''];
        yield '256 bytes' => [str_repeat('a', 256)];
        yield 'ponto' => ['.'];
        yield 'dois pontos' => ['..'];
        yield 'comeca com ponto' => ['.env'];
        yield 'termina com ponto' => ['arquivo.'];
        yield 'barra' => ['user/name.png'];
        yield 'dois-pontos' => ['a:b'];
        yield 'asterisco' => ['a*b'];
        yield 'interrogacao' => ['a?b'];
        yield 'aspas' => ['a"b'];
        yield 'menor' => ['a<b'];
        yield 'maior' => ['a>b'];
        yield 'pipe' => ['a|b'];
        yield 'int' => [123];
        yield 'null' => [null];
        yield 'false' => [false];
        yield 'true' => [true];
        yield 'float' => [1.5];
        yield 'array' => [['user.png']];
        yield 'objeto' => [new \stdClass()];
    }

    #[DataProvider('validProvider')]
    public function testIsValidAcceptsValidNames(string $value): void
    {
        self::assertTrue(FileName::isValid($value));
    }

    #[DataProvider('invalidProvider')]
    public function testIsValidRejectsInvalidValues(mixed $value): void
    {
        self::assertFalse(FileName::isValid($value));
    }

    #[DataProvider('validProvider')]
    public function testFromKeepsValueWithoutNormalization(string $value): void
    {
        $name = FileName::from($value);

        self::assertInstanceOf(FileName::class, $name);
        self::assertSame($value, $name->value());
        self::assertSame($value, (string) $name);
        self::assertSame($value, $name->jsonSerialize());
        self::assertSame(json_encode($value), json_encode($name));
    }

    public function testFromDoesNotTrimOrChangeCase(): void
    {
        self::assertSame(' User.PNG ', FileName::from(' User.PNG ')->value());
    }

    #[DataProvider('invalidProvider')]
    public function testFromThrowsForInvalidValues(mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::MESSAGE);

        FileName::from($value);
    }

    public function testIsValidDoesNotThrow(): void
    {
        self::assertFalse(FileName::isValid(new \stdClass()));
    }

    public function testConstructorIsNotPublic(): void
    {
        $constructor = (new \ReflectionClass(FileName::class))->getConstructor();

        self::assertNotNull($constructor);
        self::assertFalse($constructor->isPublic());
    }

    public function testClassIsFinalReadonly(): void
    {
        $class = new \ReflectionClass(FileName::class);

        self::assertTrue($class->isFinal());
        self::assertTrue($class->isReadOnly());
    }

    public function testTestsDoNotInstantiateWithNew(): void
    {
        $needle = 'new ' . 'FileName(';
        foreach (glob(__DIR__ . '/*.php') ?: [] as $file) {
            self::assertStringNotContainsString($needle, (string) file_get_contents($file), basename($file));
        }
    }
}
