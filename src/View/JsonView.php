<?php
declare(strict_types=1);

namespace Warehouse\View;

use JsonException;
use Warehouse\Command\StockResult;
use Warehouse\Contracts\ViewInterface;

class JsonView implements ViewInterface
{
    public function showAction(string $action, ?string $sku, ?string $orderId, ?float $price): void
    {
        $this->sendJson([
            'action' => $action,
            'sku'    => $sku,
            'orderId' => $orderId,
            'price'  => $price,
        ]);
    }

    public function showHoldSuccess(string $sku, string $orderId): void
    {
        $this->sendJson([
            'success' => true,
            'message' => "Hold success",
            'sku'     => $sku,
            'orderId' => $orderId,
        ]);
    }

    public function showConfirmSuccess(string $orderId, string $sku): void
    {
        $this->sendJson([
            'success' => true,
            'message' => "Confirm success",
            'orderId' => $orderId,
            'sku'     => $sku,
        ]);
    }

    public function showNotFound(): void
    {
        http_response_code(404);
        $this->sendJson(['error' => 'Not found']);
    }

    public function showCompleted(): void
    {
        $this->sendJson(['message' => 'Completed']);
    }

    public function showError(string $message): void
    {
        http_response_code(400);
        $this->sendJson(['error' => $message]);
    }

    public function showResult(StockResult $result): void
    {
        $this->sendJson([
            'success' => $result->success,
            'action'  => $result->action,
            'sku'     => $result->sku,
            'orderId' => $result->orderId,
            'price'   => $result->price,
        ]);
    }

    private function sendJson(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            echo json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            error_log("JsonView error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error'], JSON_UNESCAPED_UNICODE);
        }
    }
}