<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\FileName\Tests;

use Elavora\Api\DataTypes\Filesystem\FileName;
use Elavora\Api\Framework\Contracts\DescribesType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regras de docs/USO.md ("Caracteres de controle e de formatação", "Cenários de aceite (caracteres de controle)"
 * e "Documentação do tipo (OPTIONS)").
 *
 * Os testes de valores inválidos falham enquanto a validação ainda aceita caracteres de controle.
 */
final class FileNameControlCharactersTest extends TestCase
{
    private const MESSAGE = 'Valor invalido para Elavora\Api\DataTypes\Filesystem\FileName.';

    /** Cenário 1: NUL em qualquer posição. @return iterable<string, array{string}> */
    public static function nulProvider(): iterable
    {
        yield 'NUL no meio' => ["a\0b"];
        yield 'so NUL' => ["\0"];
        yield 'NUL no fim' => ["a\0"];
        yield 'NUL no inicio' => ["\0a"];
    }

    /** Cenário 2: controles C0 (U+0001 a U+001F). @return iterable<string, array{string}> */
    public static function c0Provider(): iterable
    {
        yield 'LF' => ["a\nb"];
        yield 'CR' => ["a\rb"];
        yield 'TAB' => ["a\tb"];
        yield 'VT' => ["a\x0Bb"];
        yield 'FF' => ["a\x0Cb"];
        yield 'ESC' => ["a\x1Bb"];
        yield 'US' => ["a\x1Fb"];
    }

    /** Cenário 3: quebra de linha no fim. @return iterable<string, array{string}> */
    public static function trailingNewlineProvider(): iterable
    {
        yield 'LF no fim' => ["arquivo\n"];
        yield 'CRLF no fim' => ["arquivo\r\n"];
    }

    /** Cenário 4: DEL. @return iterable<string, array{string}> */
    public static function delProvider(): iterable
    {
        yield 'DEL no meio' => ["a\x7Fb"];
    }

    /** Cenário 5: C1 (U+0080 a U+009F). @return iterable<string, array{string}> */
    public static function c1Provider(): iterable
    {
        yield 'U+0080' => ["a\u{80}b"];
        yield 'U+0085 NEL' => ["a\u{85}b"];
        yield 'U+009F' => ["a\u{9F}b"];
    }

    /** Cenário 6: separadores Unicode de linha e parágrafo. @return iterable<string, array{string}> */
    public static function lineSeparatorProvider(): iterable
    {
        yield 'U+2028' => ["a\u{2028}b"];
        yield 'U+2029' => ["a\u{2029}b"];
    }

    /** Cenário 7: marcas bidi de embutimento e override (U+202A a U+202E). @return iterable<string, array{string}> */
    public static function bidiEmbeddingProvider(): iterable
    {
        yield 'U+202A LRE' => ["a\u{202A}b"];
        yield 'U+202B RLE' => ["a\u{202B}b"];
        yield 'U+202C PDF' => ["a\u{202C}b"];
        yield 'U+202D LRO' => ["a\u{202D}b"];
        yield 'U+202E RLO' => ["a\u{202E}b"];
    }

    /** Cenário 8: marcas bidi de isolamento (U+2066 a U+2069). @return iterable<string, array{string}> */
    public static function bidiIsolateProvider(): iterable
    {
        yield 'U+2066 LRI' => ["a\u{2066}b"];
        yield 'U+2067 RLI' => ["a\u{2067}b"];
        yield 'U+2068 FSI' => ["a\u{2068}b"];
        yield 'U+2069 PDI' => ["a\u{2069}b"];
    }

    /** Controles em qualquer posição (início, meio ou fim). @return iterable<string, array{string}> */
    public static function positionProvider(): iterable
    {
        yield 'RLO no inicio' => ["\u{202E}relatorio.pdf"];
        yield 'RLO no fim' => ["relatorio.pdf\u{202E}"];
        yield 'LF no inicio' => ["\narquivo.txt"];
        yield 'NEL no fim' => ["arquivo.txt\u{85}"];
        yield 'U+2028 no inicio' => ["\u{2028}arquivo.txt"];
        yield 'PDI no fim' => ["arquivo.txt\u{2069}"];
    }

    /** Exemplo da doc: nome que aparece diferente do real. @return iterable<string, array{string}> */
    public static function spoofingProvider(): iterable
    {
        yield 'relatorio com RLO' => ["relatorio\u{202E}fdp.exe"];
    }

    /** Cenário 9: UTF-8 malformado. @return iterable<string, array{string}> */
    public static function malformedUtf8Provider(): iterable
    {
        yield 'byte FF no meio' => ["a\xFFb"];
        yield 'C2 isolado' => ["\xC2"];
    }

    /** Cenário 10: caracteres que continuam válidos. @return iterable<string, array{string}> */
    public static function validProvider(): iterable
    {
        yield 'U+200B ZWSP' => ["a\u{200B}b"];
        yield 'U+200C ZWNJ' => ["a\u{200C}b"];
        yield 'U+200D ZWJ' => ["a\u{200D}b"];
        yield 'U+200E LRM' => ["a\u{200E}b"];
        yield 'U+200F RLM' => ["a\u{200F}b"];
        yield 'U+061C ALM' => ["a\u{061C}b"];
        yield 'U+FEFF BOM' => ["a\u{FEFF}b"];
        yield 'U+00A0 espaco sem quebra' => ["a\u{A0}b"];
        yield 'arabe' => ["\u{627}\u{644}.txt"];
        yield 'hebraico' => ["\u{5E9}\u{5DC}\u{5D5}\u{5DD}.txt"];
        yield 'CJK' => ['日本語.txt'];
        yield 'acento' => ['relatório.pdf'];
        yield 'espaco comum' => ['a b.txt'];
        yield 'user.png' => ['user.png'];
        yield 'a.tar.gz' => ['a.tar.gz'];
    }

    /** Cenário 12: limite em bytes. @return iterable<string, array{string, bool}> */
    public static function byteLimitProvider(): iterable
    {
        yield '255 bytes ASCII' => [str_repeat('a', 255), true];
        yield '256 bytes ASCII' => [str_repeat('a', 256), false];
        yield '128 vezes e-acento (256 bytes)' => [str_repeat('é', 128), false];
        yield '127 vezes e-acento + a (255 bytes)' => [str_repeat('é', 127) . 'a', true];
    }

    #[DataProvider('nulProvider')]
    #[DataProvider('c0Provider')]
    #[DataProvider('trailingNewlineProvider')]
    #[DataProvider('delProvider')]
    #[DataProvider('c1Provider')]
    #[DataProvider('lineSeparatorProvider')]
    #[DataProvider('bidiEmbeddingProvider')]
    #[DataProvider('bidiIsolateProvider')]
    #[DataProvider('positionProvider')]
    #[DataProvider('spoofingProvider')]
    #[DataProvider('malformedUtf8Provider')]
    public function testIsValidRejectsControlAndFormattingCharacters(string $value): void
    {
        self::assertFalse(FileName::isValid($value));
    }

    #[DataProvider('nulProvider')]
    #[DataProvider('c0Provider')]
    #[DataProvider('trailingNewlineProvider')]
    #[DataProvider('delProvider')]
    #[DataProvider('c1Provider')]
    #[DataProvider('lineSeparatorProvider')]
    #[DataProvider('bidiEmbeddingProvider')]
    #[DataProvider('bidiIsolateProvider')]
    #[DataProvider('positionProvider')]
    #[DataProvider('spoofingProvider')]
    #[DataProvider('malformedUtf8Provider')]
    public function testConstructorThrowsForControlAndFormattingCharacters(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::MESSAGE);

        new FileName($value);
    }

    #[DataProvider('validProvider')]
    public function testIsValidAcceptsInvisibleAndScriptCharacters(string $value): void
    {
        self::assertTrue(FileName::isValid($value));
    }

    #[DataProvider('validProvider')]
    public function testConstructorKeepsInvisibleAndScriptCharactersWithoutChange(string $value): void
    {
        self::assertSame($value, (new FileName($value))->value());
    }

    #[DataProvider('byteLimitProvider')]
    public function testByteLimitIsCountedInBytes(string $value, bool $expected): void
    {
        self::assertSame($expected, FileName::isValid($value));
    }

    public function testIsValidKeepsBoolReturnType(): void
    {
        $returnType = (new \ReflectionMethod(FileName::class, 'isValid'))->getReturnType();

        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
        self::assertSame('bool', $returnType->getName());
    }

    public function testDocumentedTypeDescriptionMentionsControlCharacters(): void
    {
        self::assertTrue(is_a(FileName::class, DescribesType::class, true));

        $method = new \ReflectionMethod(FileName::class, 'documentedType');
        $documented = $method->isStatic()
            ? $method->invoke(null)
            : $method->invoke(new FileName('user.png'));

        self::assertIsArray($documented);
        self::assertArrayHasKey('description', $documented);
        self::assertIsString($documented['description']);
        self::assertStringContainsString('caracteres de controle', $documented['description']);
    }
}
