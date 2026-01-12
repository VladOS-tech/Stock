<?php
declare(strict_types=1);

namespace Warehouse\Contracts;

use Warehouse\Command\StockResult;

interface ViewInterface
{
    public function showAction(string $action, ?string $sku, ?string $orderId, ?float $price): void;
    public function showHoldSuccess(string $sku, string $orderId): void;
    public function showConfirmSuccess(string $orderId, string $sku): void;
    public function showNotFound(): void;
    public function showCompleted(): void;
    public function showError(string $message): void;
    public function showResult(StockResult $result): void;

}