<?php
declare(strict_types=1);

namespace Warehouse\Service;
use DomainException;
use Exception;
use InvalidArgumentException;
use RuntimeException;
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
        if (!$command->sku) {
            throw new InvalidArgumentException("Hold требует SKU");
        }

        return $this->db->transaction(function () use ($command) {
            $orderId = $command->orderId ?? 'ORDER' . substr(uniqid(), -8);

            $stock = $this->stockModel->findAvailableStock($command->sku);
            if (!$stock) {
                throw new DomainException("Товар {$command->sku} недоступен на складе");
            }

            $success = $this->stockModel->holdStock($command->sku, $command->price, $orderId);

            if (!$success) {
                throw new RuntimeException("Не удалось зарезервировать товар {$command->sku}");
            }

            return new StockResult(
                success: true,
                action: 'hold',
                sku: $command->sku,
                orderId: $orderId,
                price: $command->price
            );
        });
    }

    public function executeConfirm(StockCommand $command): StockResult
    {
        if (!$command->orderId) {
            throw new InvalidArgumentException("Confirm требует orderId");
        }

        return $this->db->transaction(function () use ($command) {
            $sku = $this->stockModel->confirmStock($command->orderId);

            if ($sku === null) {
                throw new DomainException("Заказ {$command->orderId} не найден или уже подтверждён");
            }

            return new StockResult(
                success: true,
                action: 'confirm',
                sku: $sku,
                orderId: $command->orderId,
                price: null
            );
        });
    }
}