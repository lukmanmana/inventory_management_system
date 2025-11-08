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
    // Support DELETE via query param or HTTP DELETE body
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents('php://input'), $delete_data);
        $id = isset($delete_data['id']) ? intval($delete_data['id']) : null;
    } else {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    }

    if (!$id) throw new Exception('Product ID is required');

    $result = $productManager->delete($id, $user_id);
    echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Product deleted' : 'Failed to delete product']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
