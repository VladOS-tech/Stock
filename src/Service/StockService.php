<?php
declare(strict_types=1);

namespace Warehouse\Service;
use Exception;
use Warehouse\Command\Action;
use Warehouse\Command\StockCommand;
use Warehouse\Command\StockResult;
use Warehouse\Contracts\StockServiceInterface;
use Warehouse\Contracts\StockModelInterface;
use Warehouse\Contracts\TransactionManagerInterface;

readonly class StockService implements StockServiceInterface
{
    public function __construct(
        private StockModelInterface $stockModel,
        private TransactionManagerInterface $db
    ) {}



    public function executeHold(StockCommand $command): StockResult
    {
        return $this->db->transaction(function () use ($command) {
            if (!$command->sku) throw new Exception("Hold требует SKU");
            $orderId = $command->orderId ?? 'ORDER' . substr(uniqid(), -8);
            $success = $this->stockModel->holdStock($command->sku, $command->price, $orderId);

            return new StockResult(
                success: $success,
                action: 'hold',
                sku: $command->sku,
                orderId: $orderId,
                price: $command->price
            );
        });
    }

    public function executeConfirm(StockCommand $command): StockResult
    {
        return $this->db->transaction(function () use ($command) {
            if (!$command->orderId) throw new Exception("Confirm требует orderId");
            $sku = $this->stockModel->confirmStock($command->orderId);

            return new StockResult(
                success: $sku !== null,
                action: 'confirm',
                sku: $sku,
                orderId: $command->orderId
            );
        });
    }
}