<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\Filesystem;

use Elavora\Api\DataTypes\AbstractDataType;
use Elavora\Api\Framework\Contracts\DescribesType;

final readonly class FileName extends AbstractDataType implements DescribesType
{
    /**
     * Rejeita separadores, reservados do Windows, controles C0/DEL/C1, U+2028/2029,
     * marcas bidi (U+202A-202E, U+2066-2069) e UTF-8 malformado (flag /u => false).
     */
    private const string PATTERN = '/^[^\x00-\x1F\x7F\x{80}-\x{9F}\x{2028}\x{2029}\x{202A}-\x{202E}\x{2066}-\x{2069}\/:*?"<>|]+\z/u';

    /**
     * Descreve o tipo no OPTIONS publico da API.
     *
     * @return array<string, mixed>
     */
    public static function documentedType(): array
    {
        return [
            'type' => 'string',
            'example' => 'user.png',
            'description' => 'Nome de arquivo com 1 a 255 caracteres, sem os caracteres / : * ? " < > |, '
                . 'sem caracteres de controle (NUL, quebras de linha, DEL, C1, separadores e marcas bidi Unicode), '
                . 'sem comecar nem terminar com ponto e diferente de "." e "..".',
        ];
    }

    /**
     * Verifica se o valor e um nome de arquivo valido.
     */
    public static function isValid(mixed $value): bool
    {
        if (!is_string($value) || $value === '' || strlen($value) > 255) {
            return false;
        }

        if ($value === '.' || $value === '..' || str_starts_with($value, '.') || str_ends_with($value, '.')) {
            return false;
        }

        return preg_match(self::PATTERN, $value) === 1;
    }
}
