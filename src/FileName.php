<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\Filesystem;

use Elavora\Api\DataTypes\AbstractDataType;

final readonly class FileName extends AbstractDataType
{
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

        return preg_match('/^[^\\/:*?"<>|]+$/', $value) === 1;
    }
}
