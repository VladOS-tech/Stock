<?php
declare(strict_types=1);

namespace Warehouse\Repository;

use PDO;
use Warehouse\Contracts\StockRepositoryInterface;
use Warehouse\Model\StockModel;

readonly class StockRepository implements StockRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function findAvailable(string $sku): ?StockModel
    {
        $stmt = $this->pdo->prepare("
            SELECT id, ctid, sku, state, price 
            FROM stock WHERE sku = :sku AND state = 'Stock' LIMIT 1
        ");
        $stmt->execute(['sku' => $sku]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? StockModel::fromArray($row) : null;
    }

    // StockRepository::save()
    public function save(StockModel $stock): void
    {
        $params = ['id' => $stock->id, 'state' => $stock->state];
        $sets = ['state = :state'];

        if ($stock->price !== null) {
            $sets[] = 'price = :price';
            $params['price'] = $stock->price;
        }
        // ❌ НЕТ price=:price!

        $sql = "UPDATE stock SET " . implode(', ', $sets) . " WHERE id = :id";

        error_log("SQL: $sql");  // DEBUG
        error_log("Params: " . json_encode($params));

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }



    public function findByOrderId(string $orderId): ?StockModel
    {
        $stmt = $this->pdo->prepare("
        SELECT id, ctid, sku, state, price 
        FROM stock 
        WHERE state = :state 
        LIMIT 1  -- 1 товар на заказ
    ");
        $stmt->execute(['state' => "Hold/$orderId"]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? StockModel::fromArray($row) : null;
    }
}
