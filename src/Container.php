<?php
declare(strict_types=1);

namespace Warehouse;

use InvalidArgumentException;
use Warehouse\Contracts\MiddlewareInterface;
use Warehouse\Contracts\StockControllerInterface;
use Warehouse\Contracts\StockRepositoryInterface;
use Warehouse\Contracts\StockServiceInterface;
use Warehouse\Contracts\TransactionManagerInterface;
use Warehouse\Contracts\ViewInterface;
use Warehouse\Controller\StockController;
use Warehouse\Http\ApiRouter;
use Warehouse\Middleware\IdempotencyMiddleware;
use Warehouse\Repository\IdempotencyRepository;
use Warehouse\Repository\StockRepository;
use Warehouse\Service\StockService;
use Warehouse\View\JsonView;

class Container
{
    private static array $singletons = [];
    private static array $config = [];

    public static function boot(): void
    {
        self::$config = array_merge(
            require __DIR__ . '/../config/app.php',
        );
    }

    public static function config(string $key = null, mixed $default = null): mixed
    {
        return $key === null ? self::$config : (self::$config[$key] ?? $default);
    }

    public static function param(string $path, mixed $default = null): mixed
    {
        $parts = explode('.', $path);
        $value = self::$config;

        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }


    /**
     * @template T of object
     * @param class-string<T> $abstract
     * @return T
     */
    public static function get(string $abstract): object
    {
        if (isset(self::$singletons[$abstract])) {
            return self::$singletons[$abstract];
        }

        $instance = match ($abstract) {
            Database::class => new Database(self::param('database.dsn')),
            JsonView::class => new JsonView(),
            StockRepository::class, StockRepositoryInterface::class => new StockRepository(
                self::get(Database::class)->getConnection()
            ),
            StockService::class, StockServiceInterface::class, TransactionManagerInterface::class => new StockService(
                self::get(StockRepositoryInterface::class),
                self::get(Database::class)
            ),
            IdempotencyRepository::class => new IdempotencyRepository(
                self::get(Database::class)->getConnection()
            ),
            IdempotencyMiddleware::class, MiddlewareInterface::class => new IdempotencyMiddleware(
                self::get(IdempotencyRepository::class)
            ),
            StockController::class, StockControllerInterface::class => new StockController(
                self::get(StockServiceInterface::class),
                self::get(ViewInterface::class)
            ),
            ApiRouter::class => new ApiRouter(
                self::get(StockControllerInterface::class),
                self::get(MiddlewareInterface::class)
            ),
            ViewInterface::class => self::get(JsonView::class),

            default => throw new InvalidArgumentException("Сервис не найден: $abstract")
        };

        self::$singletons[$abstract] = $instance;
        return $instance;
    }
}
