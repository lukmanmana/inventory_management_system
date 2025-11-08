<?php
require_once __DIR__ . '/../config/database.php';

class CategoryManager {
    private $conn;

    public function __construct() {
        $this->conn = connectDB();
    }

    /**
     * Category CRUD Operations
     */
    public function create($data) {
        $user_id = $data['user_id'];
        $name = trim($data['name']);
        $description = trim($data['description'] ?? '');

        if (empty($name)) {
            throw new Exception('Category name is required');
        }

        // Check if category exists
        if ($this->existsByName($user_id, $name)) {
            throw new Exception('A category with this name already exists');
        }

        $stmt = $this->conn->prepare("INSERT INTO categories (user_id, name, description) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("iss", $user_id, $name, $description);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create category: ' . $stmt->error);
        }

        return [
            'id' => $stmt->insert_id,
            'name' => $name,
            'description' => $description
        ];
    }

    public function update($user_id, $id, $name, $description = '') {
        $name = trim($name);
        $description = trim($description);

        if (empty($name)) {
            throw new Exception('Category name is required');
        }

        // Check if name exists for other categories
        $stmt = $this->conn->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ? AND id != ?");
        $stmt->bind_param("isi", $user_id, $name, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('A category with this name already exists');
        }

        $stmt = $this->conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("ssii", $name, $description, $id, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update category: ' . $stmt->error);
        }

        return $stmt->affected_rows > 0;
    }

    public function delete($user_id, $id) {
        // Check if category has products
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['count'] > 0) {
            throw new Exception('Cannot delete category with associated products');
        }

        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("ii", $id, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete category: ' . $stmt->error);
        }

        return $stmt->affected_rows > 0;
    }

    /**
     * Category Query Methods
     */
    public function getAll($user_id) {
        $stmt = $this->conn->prepare("\n            SELECT c.*, COUNT(p.id) as product_count \n            FROM categories c \n            LEFT JOIN products p ON c.id = p.category_id \n            WHERE c.user_id = ? \n            GROUP BY c.id \n            ORDER BY c.name ASC\n        ");
        
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch categories: ' . $stmt->error);
        }

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($user_id, $id) {
        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("ii", $id, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch category: ' . $stmt->error);
        }

        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Helper Methods
     */
    private function existsByName($user_id, $name) {
        $stmt = $this->conn->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ?");
        $stmt->bind_param("is", $user_id, $name);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
