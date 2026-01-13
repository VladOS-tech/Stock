<?php
declare(strict_types=1);

namespace Warehouse\Controller;

use DomainException;
use Exception;
use InvalidArgumentException;
use Warehouse\Command\StockCommand;
use Warehouse\Contracts\StockControllerInterface;
use Warehouse\Contracts\StockServiceInterface;
use Warehouse\View\JsonView;

readonly class StockController implements StockControllerInterface
{
    public function __construct(
        private StockServiceInterface $service,
        private JsonView $view
    ) {}

    public function hold(StockCommand $command): void
    {
        try {
            $result = $this->service->executeHold($command);
            $this->view->showResult($result);
        } catch (InvalidArgumentException $e) {
            $this->view->showError($e->getMessage());
        } catch (DomainException $e) {
            $this->view->showError($e->getMessage());  // 400
        }
    }

    public function confirm(StockCommand $command): void
    {
        try {
            $result = $this->service->executeConfirm($command);
            $this->view->showResult($result);
        } catch (InvalidArgumentException|DomainException|Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }
}
