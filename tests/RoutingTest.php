<?php
declare(strict_types=1);

use Operaattori\Db;
use Operaattori\OperatorRegistry;
use Operaattori\Router;

/** Vaatii käynnissä olevan tietokannan ja ajetun migraation. */
function test_route_uses_message_header_operator(): void
{
    $pdo = Db::pdo();
    $router = new Router($pdo, new OperatorRegistry($pdo));
    $message = [
        'id' => 999999,
        'recipient_ovt' => '003712345671',
        'to_operator' => 'VLASFIH2',
    ];
    $decision = $router->route($message);
    assert_same('VLASFIH2', $decision['to_operator']);
    assert_same('routed', $decision['status']);
}

function test_registry_finds_known_party(): void
{
    $registry = new OperatorRegistry(Db::pdo());
    $party = $registry->findParty('003712345671');
    assert_true($party !== null);
    assert_same('Kuusamon Kahvila Oy', $party['name']);
}
