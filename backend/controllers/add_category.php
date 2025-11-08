<?php
require_once __DIR__ . '/../services/CategoryManager.php';
require_once __DIR__ . '/../services/AuthManager.php';
require_once __DIR__ . '/../lib/error_handler.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    $auth = new AuthManager();
    if (!$auth->isLoggedIn()) {
        throw new Exception('User not authenticated');
    }

    $user_id = $auth->getCurrentUserId();
    if (!$user_id) {
        throw new Exception('Invalid user session');
    }

    // Validate input
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($name)) {
        throw new Exception('Category name is required');
    }

    // Create category
    $categoryManager = new CategoryManager();
    $result = $categoryManager->create([
        'user_id' => $user_id,
        'name' => $name,
        'description' => $description
    ]);
    
    $category_id = $result['id'];

    echo json_encode([
        'success' => true,
        'message' => 'Category added successfully',
        'category_id' => $category_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
