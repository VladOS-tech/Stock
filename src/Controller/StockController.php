<?php
declare(strict_types=1);

namespace Warehouse\Controller;

use DomainException;
use InvalidArgumentException;
use RuntimeException;
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

    public function hold(StockCommand $command): void
    {
        try {
            $result = $this->service->executeHold($command);
            $this->view->showHoldSuccess($result->sku, $result->orderId);
        } catch (InvalidArgumentException|DomainException|RuntimeException $e) {
            $this->view->showError($e->getMessage());
        }
    }

    public function confirm(StockCommand $command): void
    {
        try {
            $result = $this->service->executeConfirm($command);
            $this->view->showConfirmSuccess($result->orderId, $result->sku);
        } catch (InvalidArgumentException|DomainException $e) {
            $this->view->showError($e->getMessage());
        }
    }

}