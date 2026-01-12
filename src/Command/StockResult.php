<?php
declare(strict_types=1);

namespace Warehouse\Command;

class StockResult
{
    public function __construct(
        public bool $success,
        public string $action,
        public ?string $sku,
        public ?string $orderId,
        public ?float $price = null,
    ){}
}