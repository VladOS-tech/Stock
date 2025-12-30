<?php
declare(strict_types=1);

namespace Warehouse\Controller;

use Warehouse\Command\Action;
use Warehouse\Command\StockCommand;
use Warehouse\Contracts\StockControllerInterface;
use Warehouse\Contracts\StockServiceInterface;
use Warehouse\Contracts\ViewInterface;
use Exception;

readonly class StockController implements StockControllerInterface
{
    public function __construct(
        private StockServiceInterface $service,
        private ViewInterface         $view
    ) {}

    public function handle(StockCommand $command): void
    {
        try {
            $result = match ($command->action) {
                Action::HOLD    => $this->service->executeHold($command),
                Action::CONFIRM => $this->service->executeConfirm($command),
                default         => throw new Exception("Неизвестное действие")
            };
            $this->view->showResult($result);
        } catch (Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }
}