<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Warehouse\Controller\StockController;
use Warehouse\Database;
use Warehouse\Model\StockModel;
use Warehouse\Router\Router;
use Warehouse\Service\StockService;
use Warehouse\View\StockView;

$config = require __DIR__ . '/../config/database.php';
$db = new Database($config['dsn']);
$model = new StockModel($db->getConnection());
$service = new StockService($model, $db);
$view = new StockView();
$controller = new StockController($service, $view);
$router = new Router($controller);

try {
    $router->route($GLOBALS['argv']);
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}