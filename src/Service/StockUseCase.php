<?php
declare(strict_types=1);

namespace Warehouse\Service;

use Warehouse\Command\Action;
use Warehouse\Command\StockCommand;
use Warehouse\Command\StockResult;
use Warehouse\Contracts\StockModelInterface;
use Warehouse\Contracts\StockUseCaseInterface;
use Warehouse\Contracts\TransactionManagerInterface;
use Exception;

readonly class StockUseCase implements StockUseCaseInterface
{
    public function __construct(
        private StockModelInterface $model,
        private TransactionManagerInterface $transaction
    ) {}

    public function execute(StockCommand $command): StockResult
    {
        return $this->transaction->transaction(function () use ($command) {
            return match ($command->action) {
                Action::HOLD    => $this->executeHold($command),
                Action::CONFIRM => $this->executeConfirm($command),
                default         => throw new Exception("Неизвестное действие: " . $command->action->value)
            };
        });
    }

    private function executeHold(StockCommand $command): StockResult
    {
        if (!$command->sku) {
            throw new Exception("Hold требует SKU");
        }

        $orderId = $command->orderId ?? 'ORDER' . substr(uniqid(), -8);
        $success = $this->model->holdStock($command->sku, $command->price, $orderId);

        return new StockResult(
            success: $success,
            action: 'hold',
            sku: $command->sku,
            orderId: $orderId,
            price: $command->price
        );
    }

    private function executeConfirm(StockCommand $command): StockResult
    {
        if (!$command->orderId) {
            throw new Exception("Confirm требует orderId");
        }

        $sku = $this->model->confirmStock($command->orderId);

        return new StockResult(
            success: $sku !== null,
            action: 'confirm',
            sku: $sku,
            orderId: $command->orderId
        );
    }
}
