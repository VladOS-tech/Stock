<?php
declare(strict_types=1);

namespace Warehouse\Router;

use Exception;
use Warehouse\Cli\CliParser;
use Warehouse\Command\Action;
use Warehouse\Contracts\StockControllerInterface;

readonly class Router
{
    public function __construct(
        private StockControllerInterface $controller
    ) {}

    public function route(array $argv): void
    {
        $command = CliParser::parse($argv);

        match ($command->action) {
            Action::HOLD    => $this->controller->hold($command),
            Action::CONFIRM => $this->controller->confirm($command),
            default         => throw new Exception("Неизвестное действие")
        };
    }
}