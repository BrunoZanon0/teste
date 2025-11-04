<?php
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/middleware/CorsMiddleware.php';

use Middleware\CorsMiddleware;

$cors = new CorsMiddleware(); // API EXTERNA PARA O ANGULAR -> NAO MEXER NUNCA
$cors->handle();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    require_once __DIR__ . '/router/router.php';
}