<?php
declare(strict_types=1);

namespace Warehouse\Cli;

use InvalidArgumentException;
use Warehouse\Command\Action;
use Warehouse\Command\StockCommand;

readonly class CliParser
{
    public static function parse(array $argv): StockCommand
    {
        if (count($argv) < 2) {
            throw new InvalidArgumentException("Требуется action: hold или confirm");
        }

        $actionName = $argv[1];

        return match ($actionName) {
            'hold' => self::parseHold(array_slice($argv, 2)),
            'confirm' => self::parseConfirm(array_slice($argv, 2)),
            default => throw new InvalidArgumentException("Action должен быть 'hold' или 'confirm'")
        };
    }
    private static function parseHold(array $args): StockCommand
    {
        if (empty($args)) {
            throw new InvalidArgumentException("hold требует SKU");
        }

        $sku = $args[0];
        $price = isset($args[1]) ? (int)$args[1] : null;
        $orderId = $args[2] ?? null;

        return new StockCommand(
            Action::HOLD,
            sku: $sku,
            price: $price,
            orderId: $orderId
        );
    }

    private static function parseConfirm(array $args): StockCommand
    {
        if(empty($args)) {
            throw new \http\Exception\InvalidArgumentException("confirm требует orderId");
        }
        return new StockCommand(Action::CONFIRM, orderId: $args[0]);
    }
}
