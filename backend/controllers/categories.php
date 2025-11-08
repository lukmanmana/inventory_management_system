<?php
require_once __DIR__ . '/../services/CategoryManager.php';
require_once __DIR__ . '/../services/AuthManager.php';

header('Content-Type: application/json');

$auth = new AuthManager();
$categoryManager = new CategoryManager();

try {
    // Check authentication for all requests
    if (!$auth->isLoggedIn()) {
        throw new Exception('Not authenticated');
    }

    $user_id = $auth->getCurrentUserId();
    if (empty($user_id)) {
        throw new Exception('Invalid user session');
    }

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) {
                $category = $categoryManager->getById($user_id, $_GET['id']);
                if (!$category) {
                    throw new Exception('Category not found');
                }
                echo json_encode([
                    'success' => true,
                    'data' => $category
                ]);
            } else {
                $categories = $categoryManager->getAll($user_id);
                echo json_encode([
                    'success' => true,
                    'data' => $categories
                ]);
            }
            break;

        case 'POST':
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            
            $category = $categoryManager->create($user_id, $name, $description);
            echo json_encode([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ]);
            break;

        case 'PUT':
            parse_str(file_get_contents("php://input"), $put_data);
            
            if (empty($put_data['id'])) {
                throw new Exception('Category ID is required');
            }

            $result = $categoryManager->update(
                $user_id,
                $put_data['id'],
                $put_data['name'] ?? '',
                $put_data['description'] ?? ''
            );

            echo json_encode([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
            break;

        case 'DELETE':
            parse_str(file_get_contents("php://input"), $delete_data);
            
            if (empty($delete_data['id'])) {
                throw new Exception('Category ID is required');
            }

            $result = $categoryManager->delete($user_id, $delete_data['id']);
            echo json_encode([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
            break;

        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
