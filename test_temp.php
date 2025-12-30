<?php

$options = getopt("", ["hold:", "price:", "order:", "confirm:"]);
$sku = $price = $orderId = $action = null;

if (isset($options['hold'])) {
    $action = 'hold';
    $sku = $options['hold'];
    $price = $options['price'] ?? null;
    $orderId = $options['order'] ?? 'ORDER' . substr(uniqid(), -8);

} elseif (isset($options['confirm'])) {
    $action = 'confirm';
    $orderId = $options['confirm'];
}

echo "Действие: $action, SKU: $sku, ORDER: $orderId, Price: $price\n";

$dsn = "pgsql:host=localhost;port=5432;dbname=warehouse;user=warehouse_user;password=warehouse123";
$db = new PDO($dsn);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->beginTransaction();

try {
    $modified = false;

    if ($action === 'hold') {
        $stmt = $db->prepare("
            UPDATE stock 
            SET price = :price, 
                state = :state
            WHERE ctid = (
                SELECT ctid FROM stock 
                WHERE sku = :sku AND state = 'Stock' 
                LIMIT 1
            )
            RETURNING id
        ");
        $state = "Hold/$orderId";
        $stmt->execute([
            'price' => $price,
            'state' => $state,
            'sku' => $sku
        ]);

        if ($stmt->fetch()) {
            echo "Зарезервирован $sku под заказ $orderId\n";
            $modified = true;
        } else {
            echo "Ничего не найдено для изменения\n";
        }

    } elseif ($action === 'confirm') {
        $stmt = $db->prepare("
            UPDATE stock 
            SET state = 'Sold'
            WHERE state = :state
            RETURNING sku
        ");
        $stmt->execute(['state' => "Hold/$orderId"]);

        if ($stmt->fetch()) {
            echo "Подтверждён заказ $orderId для $sku\n";
            $modified = true;
        } else {
            echo "Ничего не найдено для изменения\n";
        }
    }

    $db->commit();

} catch (Exception $e) {
    $db->rollBack();
    die("Ошибка: " . $e->getMessage() . "\n");
}

$db = null;
echo "Операция завершена\n";
