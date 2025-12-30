<?php
declare(strict_types=1);

namespace Warehouse\Command;

readonly class StockCommand
{
    public function __construct(
        public Action   $action,
        public ?string  $sku     = null,
        public ?int     $price   = null,
        public ?string  $orderId = null
    ) {}
}
