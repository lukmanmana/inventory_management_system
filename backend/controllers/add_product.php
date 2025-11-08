<?php
require_once __DIR__ . '/../services/ProductManager.php';
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

    // Get and validate input data
    $productData = [
        'name' => $_POST['name'] ?? '',
        'sku' => $_POST['sku'] ?? '',
        'category_id' => empty($_POST['category_id']) ? null : (int)$_POST['category_id'],
        'quantity' => (int)($_POST['quantity'] ?? 0),
        'minimum_quantity' => (int)($_POST['minimum_quantity'] ?? 5),
        'unit_price' => (float)($_POST['unit_price'] ?? 0),
        'description' => $_POST['description'] ?? ''
    ];

    // Create product
    $productManager = new ProductManager();
    $success = $productManager->create($user_id, $productData);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Product added successfully'
        ]);
    } else {
        throw new Exception('Failed to add product');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
