<?php
declare(strict_types=1);

namespace Operaattori;

final class Logger
{
    public static function info(string $message, array $context = []): void
    {
        $dir = dirname(__DIR__) . '/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $line = date('c') . ' INFO ' . $message . ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents($dir . '/edi.log', $line, FILE_APPEND);
    }
}
