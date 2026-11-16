<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\Filesystem;

use Elavora\Api\DataTypes\AbstractDataType;
use Elavora\Api\Framework\Contracts\DescribesType;

final readonly class FileName extends AbstractDataType implements DescribesType
{
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

        return preg_match('/^[^\/:*?"<>|]+$/', $value) === 1;
    }
}
