<?php
declare(strict_types=1);

namespace Warehouse\Controller;

use Warehouse\Command\StockCommand;
use Warehouse\Contracts\StockControllerInterface;
use Warehouse\Contracts\StockUseCaseInterface;
use Warehouse\Contracts\ViewInterface;
use Exception;

readonly class StockController implements StockControllerInterface
{
    public function __construct(
        private StockUseCaseInterface $useCase,
        private ViewInterface         $view
    ) {}

    public function handle(StockCommand $command): void
    {
        try {
            $result = $this->useCase->execute($command);
            $this->view->showResult($result);
        } catch (Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }
}