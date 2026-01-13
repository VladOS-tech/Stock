<?php
declare(strict_types=1);

namespace Warehouse\Repository;

use PDO;
use Warehouse\Command\StockResult;

readonly class IdempotencyRepository
{
    public function __construct(private PDO $pdo) {}

    public function find(string $key): ?StockResult
    {
        $stmt = $this->pdo->prepare("
            SELECT action, sku, order_id, price
            FROM idempotency_results 
            WHERE idempotency_key = :key AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? StockResult::fromArray($row) : null;
    }

    public function save(string $key, StockResult $result): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO idempotency_results 
            (idempotency_key, action, sku, order_id, price, expires_at)
            VALUES (:key, :action, :sku, :order_id, :price, NOW() + INTERVAL '24 hours')
            ON CONFLICT (idempotency_key) DO NOTHING
        ");
        $stmt->execute([
            'key' => $key,
            'action' => $result->action,
            'sku' => $result->sku,
            'order_id' => $result->orderId,
            'price' => $result->price
        ]);
    }

    public function acquireLock(string $key, int $timeoutSeconds = 30): bool
    {
        $lockedUntil = date('Y-m-d H:i:s', time() + $timeoutSeconds);

        $stmt = $this->pdo->prepare("
            INSERT INTO idempotency_locks (idempotency_key, locked_until)
            VALUES (:key, :locked_until)
            ON CONFLICT (idempotency_key) DO NOTHING
            RETURNING 1
        ");

        $stmt->execute([
            'key' => $key,
            'locked_until' => $lockedUntil
        ]);

        return $stmt->rowCount() > 0;
    }
}
