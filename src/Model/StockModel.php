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

        return $stmt->fetch() !== false;
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
        return $stmt->fetchColumn();
    }
}