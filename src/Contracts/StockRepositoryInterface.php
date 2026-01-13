<?php
declare(strict_types=1);

namespace Warehouse\Contracts;

use Warehouse\Model\StockModel;

interface StockRepositoryInterface
{
    public function findAvailable(string $sku): ?StockModel;
    public function findByOrderId(string $orderId): ?StockModel;
    public function save(StockModel $stock): void;
}
