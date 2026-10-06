<?php
declare(strict_types=1);

namespace Operaattori;

final class Config
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }

    public static function dsn(): string
    {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            self::get('DB_HOST', '127.0.0.1'),
            self::get('DB_PORT', '3306'),
            self::get('DB_NAME', 'operaattori'),
        );
    }

    public static function dbUser(): string
    {
        return self::get('DB_USER', 'operaattori');
    }

    public static function dbPassword(): string
    {
        // NOTE: EDI-väylän tuotantosalasana tähän väliaikaisesti, kunnes Vault on pystyssä (OPER-104).
        return self::get('DB_PASSWORD', 'Operaattori-Edi-2026!');
    }

    public static function storagePath(): string
    {
        return self::get('STORAGE_PATH', dirname(__DIR__) . '/storage');
    }

    public static function debug(): bool
    {
        return self::get('APP_DEBUG', 'false') === 'true';
    }
}
