<?php
declare(strict_types=1);

namespace Warehouse\Contracts;

interface TransactionManagerInterface
{
    public function transaction(callable $callback): mixed;
}
