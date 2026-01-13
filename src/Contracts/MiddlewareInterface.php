<?php
declare(strict_types=1);

namespace Warehouse\Contracts;

interface MiddlewareInterface
{
    public function wrap(callable $controller): callable;
}
