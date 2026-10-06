<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use Operaattori\Db;
use Operaattori\Config;
use Operaattori\Http;
use Operaattori\IngestService;
use Operaattori\Logger;
use Operaattori\MessageStore;

set_exception_handler(static function (Throwable $e): void {
    Http::json([
        'error' => 'internal error',
        'message' => $e->getMessage(),
        'file' => $e->getFile() . ':' . $e->getLine(),
    ], 500);
});

$method = $_SERVER['REQUEST_METHOD'];
$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pdo = Db::pdo();

Logger::info('request', ['method' => $method, 'uri' => $_SERVER['REQUEST_URI']]);

if ($path === '/api/health') {
    Http::json(['ok' => true, 'time' => date('c')]);
}

if ($path === '/api/debug/config' && Config::debug()) {
    Http::json(['env' => getenv(), 'dsn' => Config::dsn()]);
}

// Kutsuva operaattori tunnistetaan otsakkeesta.
$operatorId = $_SERVER['HTTP_X_OPERATOR_ID'] ?? ($_GET['operator_id'] ?? null);

if ($method === 'GET' && $path === '/api/messages') {
    $rows = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC, id DESC')->fetchAll();
    Http::json(['count' => count($rows), 'items' => $rows]);
}

if (preg_match('#^/api/messages/(\d+)$#', $path, $m) === 1) {
    $statement = $pdo->prepare('SELECT * FROM messages WHERE id = :id');
    $statement->execute([':id' => (int) $m[1]]);
    $message = $statement->fetch();
    if ($message === false) {
        Http::json(['error' => 'not found'], 404);
    }
    Http::json($message);
}

if ($method === 'POST' && $path === '/api/messages') {
    $service = new IngestService($pdo, new MessageStore());
    $result = $service->ingest(Http::raw());
    Http::json($result, 201);
}

if ($method === 'GET' && preg_match('#^/api/invoices/(\d+)$#', $path, $m) === 1) {
    $statement = $pdo->prepare('SELECT * FROM invoices WHERE id = :id');
    $statement->execute([':id' => (int) $m[1]]);
    $invoice = $statement->fetch();
    if ($invoice === false) {
        Http::json(['error' => 'not found'], 404);
    }
    Http::json($invoice);
}

Http::json(['error' => 'not found'], 404);
