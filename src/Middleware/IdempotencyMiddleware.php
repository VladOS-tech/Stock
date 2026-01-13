<?php
declare(strict_types=1);

namespace Warehouse\Middleware;

use Warehouse\Contracts\MiddlewareInterface;
use Warehouse\Repository\IdempotencyRepository;
use Warehouse\Command\StockResult;

readonly class IdempotencyMiddleware implements MiddlewareInterface
{
    public function __construct(
        private IdempotencyRepository $repo
    ) {}

    public function wrap(callable $controller): callable
    {
        return function($command) use ($controller) {
            $key = $command->idempotencyKey;
            if (!$key) {
                return $controller($command);
            }
            $cached = $this->repo->find($key);
            if ($cached) {
                http_response_code(200);
                echo json_encode($cached->toArray());
                exit;
            }

            if (!$this->repo->acquireLock($key)) {
                http_response_code(409);
                header('Content-Type: application/json');
                header('Retry-After: 5');
                echo json_encode(['error' => 'Request already in progress']);
                exit;
            }

            ob_start();
            $controller($command);
            $jsonOutput = ob_get_clean();

            $body = json_decode($jsonOutput, true);
            if (isset($body['success']) && $body['success']) {
                $result = StockResult::fromArray($body);
                $this->repo->save($key, $result);
            }

            echo $jsonOutput;
        };
    }

}
