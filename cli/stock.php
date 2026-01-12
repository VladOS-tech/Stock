<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Warehouse\Router\Router;
use Warehouse\Container;

Container::boot();
$router = Container::get(Router::class);

try {
    $router->route($GLOBALS['argv']);
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}