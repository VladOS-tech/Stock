<?php
declare(strict_types=1);

namespace Warehouse\Command;

readonly class StockCommand
{
    public function __construct(
        public Action $action,
        public ?string $sku = null,
        public ?float $price = null,
        public ?string $orderId = null,
        public string $idempotencyKey = ''
    ) {}

    public static function fromArray(array $data, Action $action): self
    {
        return new self(
            action:  $action,
            sku:     $data['sku'] ?? null,
            price:   array_key_exists('price', $data)
                ? (float)$data['price']
                : null,
            orderId: $data['orderId'] ?? null,
            idempotencyKey: $data['idempotencyKey'] ?? uniqid('req_', true),
        );
    }
}
