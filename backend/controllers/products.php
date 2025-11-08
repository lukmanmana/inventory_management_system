<?php
require_once __DIR__ . '/../services/ProductManager.php';
require_once __DIR__ . '/../services/AuthManager.php';

session_start();
header('Content-Type: application/json');

// Check authentication
$auth = new AuthManager();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$productManager = new ProductManager();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            // Handle product creation
            $result = $productManager->create($user_id, $_POST);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Product added successfully' : 'Failed to add product'
            ]);
            break;
            
        case 'PUT':
            // Handle product update
            parse_str(file_get_contents("php://input"), $put_data);
            if (!isset($put_data['id'])) {
                throw new Exception('Product ID is required');
            }
            
            $result = $productManager->update($put_data['id'], $put_data);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Product updated successfully' : 'Failed to update product'
            ]);
            break;
            
        case 'DELETE':
            // Handle product deletion
            parse_str(file_get_contents("php://input"), $delete_data);
            if (!isset($delete_data['id'])) {
                throw new Exception('Product ID is required');
            }
            
            $result = $productManager->delete($delete_data['id'], $user_id);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Product deleted successfully' : 'Failed to delete product'
            ]);
            break;
            
        case 'GET':
            // Handle various GET operations
            $operation = $_GET['operation'] ?? 'list';
            
            switch ($operation) {
                case 'export':
                    $format = $_GET['format'] ?? 'csv';
                    $data = $productManager->exportProducts($user_id, $format);
                    
                    if ($format === 'csv') {
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="products.csv"');
                    }
                    
                    echo $data;
                    break;
                    
                case 'low_stock':
                    $products = $productManager->getLowStock($user_id);
                    echo json_encode([
                        'success' => true,
                        'data' => $products
                    ]);
                    break;
                    
                case 'get':
                    if (!isset($_GET['id'])) {
                        throw new Exception('Product ID is required');
                    }
                    $product = $productManager->getById($_GET['id']);
                    echo json_encode([
                        'success' => true,
                        'data' => $product
                    ]);
                    break;
                    
                case 'list':
                default:
                    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
                    $products = $productManager->getAllByUser($user_id, $limit);
                    echo json_encode([
                        'success' => true,
                        'data' => $products
                    ]);
                    break;
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
