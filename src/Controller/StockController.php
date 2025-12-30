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

    public function hold(StockCommand $command): void
    {
        $result = $this->service->executeHold($command);
        $this->view->showResult($result);
    }

    public function confirm(StockCommand $command): void
    {
        $result = $this->service->executeConfirm($command);
        $this->view->showResult($result);
    }
}