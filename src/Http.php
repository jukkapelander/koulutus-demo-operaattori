<?php
declare(strict_types=1);

namespace Operaattori;

final class Http
{
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
        exit;
    }

    public static function raw(): string
    {
        $raw = file_get_contents('php://input');
        return $raw === false ? '' : $raw;
    }
}
