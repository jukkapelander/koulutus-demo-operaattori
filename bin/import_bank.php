<?php
declare(strict_types=1);

/** Tuo pankin viitemaksuaineiston ja kohdistaa maksut laskuihin. Ajetaan: php bin/import_bank.php fixtures/viitemaksut.csv */
require dirname(__DIR__) . '/src/bootstrap.php';

use Operaattori\Amount;
use Operaattori\Db;
use Operaattori\ReferenceMatcher;

$file = $argv[1] ?? (dirname(__DIR__) . '/fixtures/viitemaksut.csv');
if (!is_file($file)) {
    fwrite(STDERR, "Tiedostoa ei löydy: $file\n");
    exit(1);
}

$pdo = Db::pdo();
$matcher = new ReferenceMatcher($pdo);
$booked = 0;
$handle = fopen($file, 'r');
$header = true;
while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }
    if ($header) {
        $header = false;
        continue;
    }
    [$reference, $amount, $paidDate, $archiveId] = array_pad(explode(';', $line), 4, null);
    $payment = [
        'reference' => trim((string) $reference),
        'amount' => Amount::parse((string) $amount),
        'paid_date' => trim((string) $paidDate),
        'archive_id' => $archiveId !== null ? trim($archiveId) : null,
    ];
    $result = $matcher->match($payment);
    $matcher->book($payment, $result['invoice_id']);
    $booked++;
    printf("%-14s %10.2f  %s\n", $payment['reference'], $payment['amount'], $result['note']);
}
fclose($handle);
echo "$booked maksua kirjattu\n";
