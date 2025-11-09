<?php
require_once __DIR__ . '/../backend/services/ContactManager.php';
header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $contactManager = new ContactManager();

    if ($method === 'POST') {
        // Create new contact message
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception('Invalid JSON payload');
        }

        $result = $contactManager->create($input);
        echo json_encode(['success' => true, 'data' => $result]);
        exit;

    } elseif ($method === 'GET') {
        // Fetch all messages
        $messages = $contactManager->getAll();
        echo json_encode(['success' => true, 'data' => $messages]);
        exit;

    } elseif ($method === 'PUT') {
        // Mark as read
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        if (!$id) throw new Exception('Message ID required');

        $updated = $contactManager->markAsRead($id);
        echo json_encode(['success' => $updated]);
        exit;

    } elseif ($method === 'DELETE') {
        // Delete a message
        parse_str(file_get_contents("php://input"), $input);
        $id = $input['id'] ?? null;
        if (!$id) throw new Exception('Message ID required');

        $deleted = $contactManager->delete($id);
        echo json_encode(['success' => $deleted]);
        exit;

    } else {
        throw new Exception('Unsupported request method');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
