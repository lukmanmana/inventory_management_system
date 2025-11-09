<?php
require_once __DIR__ . '/../config/database.php';

class ContactManager {
    private $conn;

    public function __construct() {
        $this->conn = connectDB();
    }

    /**
     * Create a new contact message (e.g. from Contact Us form)
     */
    public function create($data) {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $message = trim($data['message'] ?? '');
        $user_id = $data['user_id'] ?? null; // optional if logged in

        if (empty($name) || empty($email) || empty($message)) {
            throw new Exception('All fields are required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }

        $stmt = $this->conn->prepare("
            INSERT INTO contact_messages (user_id, name, email, message, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("isss", $user_id, $name, $email, $message);

        if (!$stmt->execute()) {
            throw new Exception('Failed to create contact message: ' . $stmt->error);
        }

        return [
            'id' => $stmt->insert_id,
            'name' => $name,
            'email' => $email,
            'message' => $message
        ];
    }

    /**
     * Mark a message as read
     */
    public function markAsRead($id) {
        $stmt = $this->conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to mark message as read: ' . $stmt->error);
        }

        return $stmt->affected_rows > 0;
    }

    /**
     * Delete a message
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete contact message: ' . $stmt->error);
        }

        return $stmt->affected_rows > 0;
    }

    /**
     * Fetch all contact messages (admin view)
     */
    public function getAll() {
        $stmt = $this->conn->prepare("
            SELECT id, user_id, name, email, message, is_read, created_at
            FROM contact_messages
            ORDER BY created_at DESC
        ");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch contact messages: ' . $stmt->error);
        }

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetch a single message by ID
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch message: ' . $stmt->error);
        }

        return $stmt->get_result()->fetch_assoc();
    }
}
