<?php
require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

// try {
    require_once __DIR__ . '/router/router.php';
// } catch (Exception $e) {
//     http_response_code(404);
//     echo json_encode([
//         'status' => 'error',
//         'message' => 'Not Found',
//     ]);
//     exit;
// }