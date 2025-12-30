<?php
declare(strict_types=1);

namespace Warehouse\Service;
use Warehouse\Contracts\StockServiceInterface;
use Warehouse\Contracts\StockModelInterface;

readonly class StockService implements StockServiceInterface
{
    public function __construct(
        private StockModelInterface $stockModel
    ) {}

    public function hold(string $sku, ?int $price, ?string $orderId): bool
    {
        $orderId = $orderId ?? 'ORDER' . substr(uniqid(), -8);
        if ($price !== null) {
            return $this->stockModel->holdStock($sku, $price, $orderId);
        }
        return $this->stockModel->holdStock($sku, 0, $orderId);
    }

    public function confirm(string $orderId): ?string
    {
        return $this->stockModel->confirmStock($orderId);
    }
}