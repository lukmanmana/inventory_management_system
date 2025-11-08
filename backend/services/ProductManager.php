<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuthManager.php';
require_once __DIR__ . '/SubscriptionManager.php';

class ProductManager {
    protected $conn;
    protected $auth;
    protected $subscription;

    public function __construct() {
        $this->conn = connectDB();
        $this->auth = new AuthManager();
        $this->subscription = new SubscriptionManager();
    }

    /**
     * Main Product CRUD Operations
     */
    public function create($user_id, $data) {
        if (!$this->subscription->checkStorageLimit($user_id)) {
            throw new Exception('You have reached your product limit. Please upgrade your subscription to add more products.');
        }

        $this->validateProductData($data);
        
        $stmt = $this->conn->prepare("INSERT INTO products (user_id, category_id, name, description, sku, quantity, minimum_quantity, unit_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $this->conn->error);
        }

        // Prepare variables for binding (bind_param requires variables)
        $b_user_id = intval($user_id);
        $b_category_id = isset($data['category_id']) && $data['category_id'] !== null ? intval($data['category_id']) : null;
        $b_name = $data['name'];
        $b_description = $data['description'] ?? '';
        $b_sku = $data['sku'] ?? null;
        $b_quantity = intval($data['quantity']);
        $b_minimum_quantity = intval($data['minimum_quantity']);
        $b_unit_price = floatval($data['unit_price']);

        // Bind types: user_id(i), category_id(i), name(s), description(s), sku(s), quantity(i), minimum_quantity(i), unit_price(d)
        $bindResult = $stmt->bind_param(
            "iisssiid",
            $b_user_id,
            $b_category_id,
            $b_name,
            $b_description,
            $b_sku,
            $b_quantity,
            $b_minimum_quantity,
            $b_unit_price
        );

        if ($bindResult === false) {
            throw new Exception('Database bind error: ' . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception('Database execute error: ' . $stmt->error);
        }

        return true;
    }

    public function update($id, $data) {
        // include id so validation (SKU uniqueness) can ignore the current product
        $data['id'] = $id;
        $this->validateProductData($data);
        
        $stmt = $this->conn->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, sku = ?, quantity = ?, minimum_quantity = ?, unit_price = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $this->conn->error);
        }

        // Prepare variables for binding
        $b_category_id = isset($data['category_id']) && $data['category_id'] !== null ? intval($data['category_id']) : null;
        $b_name = $data['name'];
        $b_description = $data['description'] ?? '';
        $b_sku = $data['sku'] ?? null;
        $b_quantity = intval($data['quantity']);
        $b_minimum_quantity = intval($data['minimum_quantity']);
        $b_unit_price = floatval($data['unit_price']);
        $b_id = intval($id);

        // Bind types: category_id(i), name(s), description(s), sku(s), quantity(i), minimum_quantity(i), unit_price(d), id(i)
        $bindResult = $stmt->bind_param(
            "isssiidi",
            $b_category_id,
            $b_name,
            $b_description,
            $b_sku,
            $b_quantity,
            $b_minimum_quantity,
            $b_unit_price,
            $b_id
        );

        if ($bindResult === false) {
            throw new Exception('Database bind error: ' . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception('Database execute error: ' . $stmt->error);
        }

        return true;
    }

    public function delete($id, $user_id) {
        // Check if product belongs to user
        $product = $this->getById($id);
        if (!$product || $product['user_id'] != $user_id) {
            throw new Exception('Product not found or access denied');
        }
        
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        return $stmt->execute();
    }

    /**
     * Product Query Methods
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getBySKU($sku) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE sku = ?");
        $stmt->bind_param("s", $sku);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllByUser($user_id, $limit = null) {
        $sql = "SELECT p.*, c.name as category_name \n                FROM products p \n                LEFT JOIN categories c ON p.category_id = c.id \n                WHERE p.user_id = ?";
                
        if ($limit !== null) {
            $sql .= " LIMIT ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if ($limit !== null) {
            $stmt->bind_param("ii", $user_id, $limit);
        } else {
            $stmt->bind_param("i", $user_id);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getLowStock($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE user_id = ? AND quantity <= minimum_quantity");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Transaction Management
     */
    public function recordTransaction($user_id, $product_id, $type, $quantity, $notes = '') {
        $stmt = $this->conn->prepare("INSERT INTO transactions (user_id, product_id, type, quantity, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisis", $user_id, $product_id, $type, $quantity, $notes);
        
        if ($stmt->execute()) {
            // Update product quantity
            $update_sql = $type === 'in' 
                ? "UPDATE products SET quantity = quantity + ? WHERE id = ?"
                : "UPDATE products SET quantity = quantity - ? WHERE id = ?";
            
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $quantity, $product_id);
            return $update_stmt->execute();
        }
        return false;
    }

    /**
     * Export Functionality
     */
    public function exportProducts($user_id, $format = 'csv') {
        $products = $this->getAllByUser($user_id);
        
        if ($format === 'csv') {
            $output = fopen('php://temp', 'w');
            
            // Add headers
            fputcsv($output, ['ID', 'Name', 'SKU', 'Category', 'Quantity', 'Min Quantity', 'Unit Price']);
            
            // Add data
            foreach ($products as $product) {
                fputcsv($output, [
                    $product['id'],
                    $product['name'],
                    $product['sku'],
                    $product['category_name'] ?? 'Uncategorized',
                    $product['quantity'],
                    $product['minimum_quantity'],
                    $product['unit_price']
                ]);
            }
            
            rewind($output);
            $csv = stream_get_contents($output);
            fclose($output);
            
            return $csv;
        }
        
        return json_encode($products);
    }

    /**
     * Validation Methods
     */
    protected function validateProductData($data) {
        $required_fields = ['name', 'sku', 'quantity', 'minimum_quantity', 'unit_price'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception('Please fill in all required fields');
            }
        }

        // Validate numeric fields
        if (!is_numeric($data['quantity']) || $data['quantity'] < 0) {
            throw new Exception('Quantity must be a non-negative number');
        }

        if (!is_numeric($data['minimum_quantity']) || $data['minimum_quantity'] < 0) {
            throw new Exception('Minimum quantity must be a non-negative number');
        }

        if (!is_numeric($data['unit_price']) || $data['unit_price'] < 0) {
            throw new Exception('Unit price must be a non-negative number');
        }

        // Check if SKU exists (for new products or when changing SKU). Allow same SKU for the same product id.
        if (isset($data['sku'])) {
            $existing = $this->getBySKU($data['sku']);
            if ($existing) {
                if (!isset($data['id']) || intval($existing['id']) !== intval($data['id'])) {
                    throw new Exception('A product with this SKU already exists');
                }
            }
        }
    }
}
