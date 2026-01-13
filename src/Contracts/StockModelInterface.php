<?php
namespace Warehouse\Contracts;

interface StockModelInterface
{
    public function hold(?float $price, string $orderId): void;
    public function confirm(): void;
}