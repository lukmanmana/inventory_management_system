<?php
// Define consistent error handling for all backend files
function handleError($e) {
    $status = $e->getCode() ?: 400;
    http_response_code($status);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

// Set consistent error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header for all API responses
header('Content-Type: application/json');

// Handle CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
