<?php
namespace Warehouse\Contracts;

interface StockServiceInterface
{
    public function hold(string $sku, ?int $price, ?string $orderId): bool;
    public function confirm(string $orderId): ?string;
}