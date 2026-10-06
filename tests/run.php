<?php
declare(strict_types=1);

/** Riippuvukseton testiajuri. Ajetaan: php tests/run.php */
require dirname(__DIR__) . '/src/bootstrap.php';

function assert_same(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            ($message !== '' ? $message . ': ' : '')
            . 'odotettiin ' . var_export($expected, true) . ', saatiin ' . var_export($actual, true)
        );
    }
}

function assert_true(bool $condition, string $message = ''): void
{
    if (!$condition) {
        throw new RuntimeException($message !== '' ? $message : 'ehto ei toteutunut');
    }
}

foreach (glob(__DIR__ . '/*Test.php') ?: [] as $file) {
    require $file;
}

$passed = 0;
$failed = 0;
foreach (get_defined_functions()['user'] as $function) {
    if (!str_starts_with($function, 'test_')) {
        continue;
    }
    try {
        $function();
        $passed++;
        echo "PASS  $function\n";
    } catch (Throwable $e) {
        $failed++;
        echo "FAIL  $function\n      {$e->getMessage()}\n";
    }
}

echo "\n$passed läpi, $failed epäonnistui\n";
exit($failed > 0 ? 1 : 0);
