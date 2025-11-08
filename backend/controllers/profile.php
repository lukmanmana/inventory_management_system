<?php
require_once __DIR__ . '/../services/ProfileManager.php';
require_once __DIR__ . '/../services/AuthManager.php';

header('Content-Type: application/json');

$auth = new AuthManager();
$profileManager = new ProfileManager();

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
            $profile = $profileManager->getProfile($user_id);
            if (!$profile) {
                throw new Exception('Profile not found');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $profile
            ]);
            break;

        case 'PUT':
        case 'POST':
            parse_str(file_get_contents("php://input"), $data);
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = $_POST;
            }

            $result = $profileManager->updateProfile($user_id, $data);
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
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
