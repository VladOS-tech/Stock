<?php
declare(strict_types=1);

namespace Warehouse\Service;
use DomainException;
use InvalidArgumentException;
use Warehouse\Command\StockCommand;
use Warehouse\Command\StockResult;
use Warehouse\Contracts\StockRepositoryInterface;
use Warehouse\Contracts\StockServiceInterface;
use Warehouse\Contracts\TransactionManagerInterface;

readonly class StockService implements StockServiceInterface
{
    public function __construct(
        private StockRepositoryInterface $stockRepository,
        private TransactionManagerInterface $db
    ) {}

    public function executeHold(StockCommand $command): StockResult
    {
        if (!$command->sku) {
            throw new InvalidArgumentException("Hold требует SKU");
        }
        return $this->db->transaction(function () use ($command) {
            $orderId = $command->orderId ?? 'ORDER' . substr(uniqid(), -8);

            $stock = $this->stockRepository->findAvailable($command->sku);
            if (!$stock) {
                throw new DomainException("Товар $command->sku недоступен на складе");
            }
            $stock->hold($command->price, $orderId);
            $this->stockRepository->save($stock);

            return new StockResult(
                success: true,
                action: 'hold',
                sku: $stock->sku,
                orderId: $orderId,
                price: $command->price == null ? $stock->price : $command->price
            );
        });
    }

    public function executeConfirm(StockCommand $command): StockResult
    {
        if (!$command->orderId) {
            throw new InvalidArgumentException("Confirm требует orderId");
        }

        return $this->db->transaction(function () use ($command) {
            $stock = $this->stockRepository->findByOrderId($command->orderId);

            if (!$stock) {
                throw new DomainException("Заказ $command->orderId не найден");
            }
            if (!str_starts_with($stock->state, 'Hold/')) {
                throw new DomainException("Заказ уже подтверждён или отменён");
            }

            $stock->confirm();

            $this->stockRepository->save($stock);

            return new StockResult(
                success: true,
                action: 'confirm',
                sku: $stock->sku,
                orderId: $command->orderId,
                price: $stock->price
            );
        });
    }
}