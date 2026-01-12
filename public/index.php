<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Warehouse\Controller\StockController;
use Warehouse\Database;
use Warehouse\Http\ApiRouter;
use Warehouse\Model\StockModel;
use Warehouse\Service\StockService;
use Warehouse\View\JsonView;

$config = require __DIR__ . '/../config/database.php';

$db = new Database($config['dsn']);
$model = new StockModel($db->getConnection());
$service = new StockService($model, $db);
$view = new JsonView();

$controller = new StockController($service, $view);

$router = new ApiRouter($controller);

try {
    $router->route($_SERVER, file_get_contents('php://input'));
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
