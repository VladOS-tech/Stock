<?php
declare(strict_types=1);

namespace Warehouse\Contracts;

use Warehouse\Command\StockCommand;

interface StockControllerInterface
{
    public function hold(StockCommand $command): void;
    public function confirm(StockCommand $command): void;

}
