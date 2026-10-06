<?php
declare(strict_types=1);

/** Vastaanottaa yhden Finvoice-sanoman. Ajetaan: php bin/ingest.php fixtures/messages/finvoice_valid.xml */
require dirname(__DIR__) . '/src/bootstrap.php';

use Operaattori\Db;
use Operaattori\IngestService;
use Operaattori\MessageStore;

$file = $argv[1] ?? null;
if ($file === null || !is_file($file)) {
    fwrite(STDERR, "Käyttö: php bin/ingest.php <finvoice.xml>\n");
    exit(1);
}

$raw = file_get_contents($file);
$service = new IngestService(Db::pdo(), new MessageStore());
$result = $service->ingest($raw);

printf("Vastaanotettu sanoma %s (id %d), arkistoitu: %s, liite %d tavua\n",
    $result['message_id'], $result['id'], $result['raw_path'], $result['attachment_bytes']);
