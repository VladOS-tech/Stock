<?php
declare(strict_types=1);

namespace Warehouse\View;

use Warehouse\Contracts\ViewInterface;

class StockView implements ViewInterface
{
    public function showAction(string $action, ?string $sku, ?string $orderId, ?int $price): void
    {
        echo "Действие: $action, SKU: $sku, ORDER: " . ($orderId ?? 'N/A') . ", Price: " . ($price ?? 'N/A') . "\n";
    }

    public function showHoldSuccess(string $sku, string $orderId): void
    {
        echo "Зарезервирован $sku под заказ $orderId\n";
    }

    public function showConfirmSuccess(string $orderId, string $sku): void
    {
        echo "Подтверждён заказ $orderId для $sku\n";
    }

    public function showNotFound(): void
    {
        echo "Ничего не найдено для изменения\n";
    }

    public function showCompleted(): void
    {
        echo "Операция завершена\n";
    }

    public function showError(string $message): void
    {
        echo "Ошибка: $message\n";
    }
}