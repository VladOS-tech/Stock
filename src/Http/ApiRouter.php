<?php
declare(strict_types=1);

namespace Warehouse\Http;

use Warehouse\Command\Action;
use Warehouse\Command\StockCommand;
use Warehouse\Contracts\StockControllerInterface;

readonly class ApiRouter
{
    public function __construct(
        private StockControllerInterface $controller
    ) {}

    public function route(array $server, string $rawBody): void
    {
        header('Access-Control-Allow-Origin: http://localhost:3000');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        if (($server['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(200);
            return;
        }
        $method = $server['REQUEST_METHOD'] ?? 'GET';
        $path   = parse_url($server['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        header('Content-Type: application/json');

        $data = [];
        if (in_array($method, ['POST', 'PUT', 'PATCH'], true) && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        } elseif ($method === 'GET') {
            $data = $_GET;
        }

        if ($path === '/api/stock/hold' && $method === 'POST') {
            $idempotencyKey = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ?? uniqid('req_', true);
            $data['idempotencyKey'] = $idempotencyKey;
            $command = StockCommand::fromArray($data, Action::HOLD);
            $this->controller->hold($command);
            return;
        }

        if ($path === '/api/stock/confirm' && $method === 'POST') {
            $idempotencyKey = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ?? uniqid('req_', true);
            $data['idempotencyKey'] = $idempotencyKey;
            $command = StockCommand::fromArray($data, Action::CONFIRM);
            $this->controller->confirm($command);
            return;
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not found', 'path' => $path]);
    }
}
