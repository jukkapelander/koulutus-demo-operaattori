<?php
declare(strict_types=1);

/** Reitittää kaikki tilassa 'received' olevat sanomat. Ajetaan: php bin/route.php */
require dirname(__DIR__) . '/src/bootstrap.php';

use Operaattori\Db;
use Operaattori\OperatorRegistry;
use Operaattori\Router;

$pdo = Db::pdo();
$router = new Router($pdo, new OperatorRegistry($pdo));

$messages = $pdo->query("SELECT * FROM messages WHERE status = 'received' ORDER BY id")->fetchAll();
foreach ($messages as $message) {
    $decision = $router->route($message);
    $router->apply((int) $message['id'], $decision);
    printf("%-10s %s -> operaattori %s (%s)\n",
        $message['message_id'], $message['recipient_ovt'], $decision['to_operator'], $decision['reason']);
}
echo count($messages) . " sanomaa reititetty\n";
