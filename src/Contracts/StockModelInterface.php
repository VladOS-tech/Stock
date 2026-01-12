<?php
namespace Warehouse\Contracts;

use Warehouse\Command\StockResult;

interface StockModelInterface
{
    public function holdStock(string $sku, ?float $price, string $orderId): bool;
    public function confirmStock(string $orderId): ?string;
    public function findAvailableStock(string $sku): ?array;
    public function findByIdempotencyKey(string $key): ?array;
    public function saveIdempotencyResult(string $key, StockResult $result): void;
}