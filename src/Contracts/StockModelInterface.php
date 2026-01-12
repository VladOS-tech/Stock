<?php
namespace Warehouse\Contracts;

interface StockModelInterface
{
    public function holdStock(string $sku, ?float $price, string $orderId): bool;
    public function confirmStock(string $orderId): ?string;
}