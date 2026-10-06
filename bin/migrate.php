<?php
declare(strict_types=1);

/** Luo skeeman ja lataa testiaineiston. Ajetaan: php bin/migrate.php (tuhoaa taulut). */
require dirname(__DIR__) . '/src/bootstrap.php';

use Operaattori\Db;

$pdo = Db::pdo();
foreach (['schema.sql', 'seed.sql'] as $file) {
    $sql = file_get_contents(dirname(__DIR__) . '/db/' . $file);
    if ($sql === false) {
        fwrite(STDERR, "Tiedostoa db/$file ei voitu lukea\n");
        exit(1);
    }
    // MariaDB-ajuri suorittaa useita lauseita yhdellä exec-kutsulla.
    $pdo->exec($sql);
    echo "OK  db/$file\n";
}

echo "Operaattoreita: " . (int) $pdo->query('SELECT count(*) FROM operators')->fetchColumn() . "\n";
echo "Osapuolia:      " . (int) $pdo->query('SELECT count(*) FROM parties')->fetchColumn() . "\n";
echo "Sanomia:        " . (int) $pdo->query('SELECT count(*) FROM messages')->fetchColumn() . "\n";
echo "Laskuja:        " . (int) $pdo->query('SELECT count(*) FROM invoices')->fetchColumn() . "\n";
