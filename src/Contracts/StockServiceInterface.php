<?php
namespace Warehouse\Contracts;

use Warehouse\Command\StockCommand;
use Warehouse\Command\StockResult;

interface StockServiceInterface
{
    public function executeHold(StockCommand $command): StockResult;
    public function executeConfirm(StockCommand $command): StockResult;
}