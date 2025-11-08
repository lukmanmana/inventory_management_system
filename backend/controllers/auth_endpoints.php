<?php
// Prevent any output before headers
ob_start();

require_once __DIR__ . '/../services/AuthManager.php';
require_once __DIR__ . '/../lib/error_handler.php';

// Ensure no errors are displayed, only logged
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../php_errors.log');

// Set JSON header
header('Content-Type: application/json');

// Debug: Log all POST data
error_log('POST data: ' . print_r($_POST, true));
error_log('GET data: ' . print_r($_GET, true));

// Function to send JSON response
function sendJsonResponse($success, $message, $data = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $data);
    
    echo json_encode($response);
    exit;
}

$auth = new AuthManager();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $action = $_GET['action'] ?? 'login';
            error_log("Action requested: " . $action);
            
            switch ($action) {
                case 'login':
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? '';
                    error_log("Login attempt for email: " . $email);
                    
                    $auth->login($email, $password);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful'
                    ]);
                    break;
                    
                case 'register':
                    try {
                        $username = $_POST['username'] ?? '';
                        $email = $_POST['email'] ?? '';
                        $password = $_POST['password'] ?? '';
                        
                        error_log("Registration attempt - Username: " . $username . ", Email: " . $email);
                        
                        if (empty($username) || empty($email) || empty($password)) {
                            sendJsonResponse(false, 'All fields are required. Please fill in all fields.');
                        }
                        
                        $user_id = $auth->register($username, $email, $password);
                        error_log("Registration successful for user ID: " . $user_id);
                        sendJsonResponse(true, 'Registration successful', ['user_id' => $user_id]);
                    } catch (Exception $e) {
                        error_log("Registration error: " . $e->getMessage());
                        sendJsonResponse(false, $e->getMessage());
                    }
                    break;
                    
                case 'logout':
                    $auth->logout();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Logged out successfully'
                    ]);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    error_log("Main error handler caught: " . $e->getMessage());
    sendJsonResponse(false, $e->getMessage());
}
