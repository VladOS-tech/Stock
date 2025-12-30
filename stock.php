<?php

$options = getopt("", ["hold:", "price:", "order:", "confirm:"]);
$sku = $price = $orderId = $action = null;

if (isset($options['hold'])) {
    $action = 'hold';
    $sku = $options['hold'];
    $price = $options['price'] ?? null;
    $orderId = $options['order'] ?? uniqid('ORDER');

} elseif (isset($options['confirm'])) {
    $action = 'confirm';
    $orderId = $options['confirm'];
}

echo "Действие: $action, SKU: $sku, ORDER: $orderId, Price: $price\n";

$file = 'stock.csv';
if (!file_exists($file)) {
    die("Файл stock.csv не найден!\n");
}

$fd = fopen($file, 'r+') or die("Ошибка открытия $file\n");
if (!flock($fd, LOCK_EX)) {
    die("Ошибка блокировки файла! Файл заблокирован другим процессом.\n");
}
echo "Файл заблокирован\n";

$lines = [];
while (($line = fgets($fd)) !== false) {
    $lines[] = rtrim($line, "\n");
}
rewind($fd);

$modified = false;
foreach ($lines as $i => &$line) {
    $parts = str_getcsv($line);
    if (count($parts) < 3) continue;
    $parts = array_map('trim', $parts);
    [$currentSku, $currentPrice, $currentState] = $parts;
    
    if ($action === 'hold' && $sku === $currentSku && $currentState === 'Stock') {
        $parts[1] = $price;
        $parts[2] = "Hold/$orderId";
        $lines[$i] = implode(',', $parts);
        echo "Зарезервирован $sku под заказ $orderId\n";
        $modified = true;
        break;
    }
    
    if ($action === 'confirm' && str_starts_with($currentState, "Hold/$orderId")) {
        $parts[2] = 'Sold';
        $lines[$i] = implode(',', $parts);
        echo "Подтверждён заказ $orderId для $currentSku\n";
        $modified = true;
        break;
    }
}

if (!$modified) {
    echo "Ничего не найдено для изменения\n";
}

ftruncate($fd, 0);  // Очищаем файл
fwrite($fd, implode("\n", $lines) . "\n");

flock($fd, LOCK_UN);
fclose($fd);

echo "Файл разблокирован\n";