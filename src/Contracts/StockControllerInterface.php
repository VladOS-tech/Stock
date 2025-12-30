<?php
declare(strict_types=1);

namespace Warehouse\Contracts;

use Warehouse\Command\StockCommand;

interface StockControllerInterface
{
    public function handle(StockCommand $command): void;
}
