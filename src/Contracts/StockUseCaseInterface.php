<?php
declare(strict_types=1);
namespace Warehouse\Contracts;

use Warehouse\Command\StockCommand;
use Warehouse\Command\StockResult;

interface StockUseCaseInterface
{
    public function execute(StockCommand $command): StockResult;
}