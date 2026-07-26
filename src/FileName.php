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

        if (
            str_ends_with($value, ' ')
            || preg_match('/[\x00-\x1F\x7F]/', $value) === 1
            || strpbrk($value, '/\\:*?"<>|') !== false
        ) {
            return false;
        }

        $basename = explode('.', $value, 2)[0];

        return !self::isReservedDeviceName($basename);
    }

    private static function isReservedDeviceName(string $basename): bool
    {
        return preg_match('/^(?:CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])$/i', $basename) === 1;
    }
}
