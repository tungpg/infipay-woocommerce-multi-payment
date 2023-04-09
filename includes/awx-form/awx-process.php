<?php
use Airwallex\CardClient;

header('Content-Type: application/json');
http_response_code(200);

echo json_encode([
    'error' => 1,
]);
die;