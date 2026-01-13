<?php
declare(strict_types=1);

namespace Warehouse\Command;

class StockResult
{
    public function __construct(
        public bool $success,
        public string $action = 'hold',
        public ?string $sku,
        public ?string $orderId,
        public ?float $price = null,
    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            success: (bool)($data['success'] ?? true),
            action: (string)$data['action'],
            sku: $data['sku'] ?? null,
            orderId: $data['order_id'] ?? null,
            price: isset($data['price']) ? (float)$data['price'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'action' => $this->action,
            'sku' => $this->sku,
            'orderId' => $this->orderId,
            'price' => $this->price
        ];
    }
}