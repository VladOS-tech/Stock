<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Warehouse\Controller\StockController;
use Warehouse\Database;
use Warehouse\Model\StockModel;
use Warehouse\Service\StockService;
use Warehouse\View\StockView;
use Warehouse\Cli\CliParser;

$config = require __DIR__ . '/../config/database.php';
$db = new Database($config['dsn']);
$model      = new StockModel($db->getConnection());
$service    = new StockService($model);
$view       = new StockView();
$controller = new StockController($service, $view, $db);

try {
    $command = CliParser::parse($GLOBALS['argv']);
    $controller->handle($command);
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}