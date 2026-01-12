<?php
declare(strict_types=1);

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

return [
    'database' => [
        'dsn' => $_ENV['DATABASE_DSN'] ?? 'pgsql:host=localhost;dbname=stock',
    ],
    'app' => [
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        'name'  => 'Stock API',
    ],
];
