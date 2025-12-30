<?php
declare(strict_types=1);

namespace Warehouse\Controller;

use Warehouse\Command\StockCommand;
use Warehouse\Contracts\StockControllerInterface;
use Warehouse\Contracts\StockServiceInterface;
use Warehouse\Contracts\StockUseCaseInterface;
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
            $result = $this->service->execute($command);
            $this->view->showResult($result);
        } catch (Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }
}