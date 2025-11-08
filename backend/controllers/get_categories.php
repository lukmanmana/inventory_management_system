<?php
require_once __DIR__ . '/../services/CategoryManager.php';
require_once __DIR__ . '/../services/AuthManager.php';

header('Content-Type: application/json');

$auth = new AuthManager();
$categoryManager = new CategoryManager();

try {
    // Check authentication
    if (!$auth->isLoggedIn()) {
        throw new Exception('Not authenticated');
    }

    $user_id = $auth->getCurrentUserId();
    if (empty($user_id)) {
        throw new Exception('Invalid user session');
    }

    // Get all categories for the user
    $categories = $categoryManager->getAll($user_id);
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
