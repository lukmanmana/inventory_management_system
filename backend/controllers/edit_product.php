<?php
require_once __DIR__ . '/../services/ProductManager.php';
require_once __DIR__ . '/../services/AuthManager.php';

header('Content-Type: application/json');

$auth = new AuthManager();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $auth->getCurrentUserId();
$productManager = new ProductManager();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_GET['id'])) {
            throw new Exception('Product ID is required');
        }
        $id = intval($_GET['id']);
        $product = $productManager->getById($id);
        if (!$product) {
            throw new Exception('Product not found');
        }
        // Verify ownership
        if ($product['user_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        echo json_encode(['success' => true, 'product' => $product]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Expect form fields including id
        $id = isset($_POST['id']) ? intval($_POST['id']) : null;
        if (!$id) {
            throw new Exception('Product ID is required for update');
        }

        // Ensure product exists and belongs to user
        $existing = $productManager->getById($id);
        if (!$existing) {
            throw new Exception('Product not found');
        }
        if ($existing['user_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        // Build data array expected by ProductManager::update
        $data = [];
        $data['category_id'] = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? intval($_POST['category_id']) : null;
        $data['name'] = $_POST['name'] ?? $existing['name'];
        $data['description'] = $_POST['description'] ?? $existing['description'];
        $data['sku'] = $_POST['sku'] ?? $existing['sku'];
        $data['quantity'] = isset($_POST['quantity']) ? intval($_POST['quantity']) : $existing['quantity'];
        $data['minimum_quantity'] = isset($_POST['minimum_quantity']) ? intval($_POST['minimum_quantity']) : $existing['minimum_quantity'];
        $data['unit_price'] = isset($_POST['unit_price']) ? floatval($_POST['unit_price']) : $existing['unit_price'];

        $result = $productManager->update($id, $data);
        echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Product updated' : 'Failed to update product']);
        exit;
    }

    throw new Exception('Method not allowed');
} catch (Exception $e) {
    http_response_code(400);
    // Log error for debugging
    $msg = '[' . date('Y-m-d H:i:s') . '] edit_product error: ' . $e->getMessage() . "\n";
    // Append to a debug log inside backend (will be created if not exists)
    @file_put_contents(__DIR__ . '/../error_debug.log', $msg, FILE_APPEND);
    // Also send to PHP error log
    error_log($msg);

    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
