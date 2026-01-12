<?php
declare(strict_types=1);

namespace Warehouse\Model;

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

}