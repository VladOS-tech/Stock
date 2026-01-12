<?php
declare(strict_types=1);

namespace Warehouse\Model;

use Warehouse\Command\StockResult;
use Warehouse\Contracts\StockModelInterface;
use PDO;

readonly class StockModel implements StockModelInterface
{
    public function __construct(private PDO $pdo) {}
    public function holdStock(string $sku, ?float $price, string $orderId): bool
    {
        $state = "Hold/$orderId";

        if ($price !== null) {
            $stmt = $this->pdo->prepare("
            UPDATE stock
            SET price = :price, state = :state
            WHERE ctid = (
                SELECT ctid FROM stock
                WHERE sku = :sku AND state = 'Stock' LIMIT 1
            )
            RETURNING id;
        ");

            $stmt->execute([
                'price' => $price,
                'state' => $state,
                'sku' => $sku
            ]);
        } else {
            $stmt = $this->pdo->prepare("
            UPDATE stock
            SET state = :state
            WHERE ctid = (
                SELECT ctid FROM stock
                WHERE sku = :sku AND state = 'Stock' LIMIT 1
            )
            RETURNING id;
        ");

            $stmt->execute([
                'state' => $state,
                'sku' => $sku
            ]);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false;
    }

    public function confirmStock(string $orderId): ?string
    {
        $stmt = $this->pdo->prepare("
           UPDATE stock
           SET state = 'Sold'
           WHERE state = :state
           RETURNING sku;
        ");

        $stmt->execute(['state' => "Hold/$orderId"]);
        $sku = $stmt->fetchColumn();
        return $sku !== false ? (string)$sku : null;
    }

    public function findAvailableStock(string $sku): ?array
    {
        $stmt = $this->pdo->prepare("
        SELECT ctid FROM stock 
        WHERE sku = :sku AND state = 'Stock' 
        LIMIT 1
    ");
        $stmt->execute(['sku' => $sku]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByIdempotencyKey(string $key): ?array
    {
        $stmt = $this->pdo->prepare("
        SELECT action, sku, order_id, price
        FROM idempotency_results
        WHERE idempotency_key = :key AND expires_at > NOW()
        LIMIT 1
    ");
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function saveIdempotencyResult(string $key, StockResult $result): void
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO idempotency_results (idempotency_key, action, sku, order_id, price)
        VALUES (:key, :action, :sku, :order_id, :price)
        ON CONFLICT (idempotency_key) DO NOTHING
    ");
        $stmt->execute([
            'key'      => $key,
            'action'   => $result->action,
            'sku'      => $result->sku,
            'order_id' => $result->orderId,
            'price'    => $result->price,
        ]);
    }
}