<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Warehouse\Container;
use Warehouse\Http\ApiRouter;


Container::boot();
$router = Container::get(ApiRouter::class);

try {
    $router->route($_SERVER, file_get_contents('php://input'));
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
