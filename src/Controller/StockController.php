<?php
declare(strict_types=1);

namespace Warehouse\Controller;
use Warehouse\Command\StockCommand;
use Warehouse\Contracts\StockControllerInterface;
use Warehouse\Contracts\StockServiceInterface, Exception;
use Warehouse\Contracts\TransactionManagerInterface;
use Warehouse\Contracts\ViewInterface;
use Warehouse\Command\Action;

readonly class StockController implements StockControllerInterface
{
    public function __construct(
        private StockServiceInterface $service,
        private ViewInterface $view,
        private TransactionManagerInterface $db
    ) {}

    public function handle(StockCommand $command): void
    {

        $this->view->showAction(
            $command->action->value,
            $command->sku,
            $command->orderId ?? 'N/A',
            $command->price);

        try {
            $this->db->transaction(function () use ($command) {
                match($command->action) {
                    Action::HOLD   => $this->handleHold($command),
                    Action::CONFIRM => $this->handleConfirm($command)
                };
            });

            $this->view->showCompleted();
        } catch (Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }

    private function handleHold(StockCommand $command): void
    {
        if ($this->service->hold($command->sku, $command->price, $command->orderId)) {
            $this->view->showHoldSuccess($command->sku, $command->orderId ?? 'N/A');
        } else {
            $this->view->showNotFound();
        }
    }

    private function handleConfirm(StockCommand $command): void
    {
        $sku = $this->service->confirm($command->orderId);
        $sku ? $this->view->showConfirmSuccess($command->orderId, $sku) : $this->view->showNotFound();
    }
}