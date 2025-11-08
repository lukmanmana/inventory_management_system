<?php
require_once __DIR__ . '/../config/database.php';

class AuthManager {
    private $conn;

    public function __construct() {
        try {
            $this->conn = connectDB();
            if (!$this->conn) {
                error_log("Database connection failed in AuthManager constructor");
                throw new Exception("Failed to establish database connection");
            }
            error_log("Database connection successful in AuthManager");
        } catch (Exception $e) {
            error_log("Exception in AuthManager constructor: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Session Management
     */
    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        return true;
    }

    /**
     * Authentication Methods
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            throw new Exception('Please provide both email and password');
        }

        $stmt = $this->conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Invalid email or password');
        }
        
        $user = $result->fetch_assoc();
        
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Invalid email or password');
        }
        
        // Start session and store user data
        session_start();
        $_SESSION['user_id'] = $user['id'];
        
        return true;
    }

    public function register($username, $email, $password) {
        error_log("Starting registration process for email: " . $email);
        
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            error_log("Registration validation failed: empty fields");
            throw new Exception('All fields are required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        // Check for existing username/email
        if ($this->existsUsername($username)) {
            throw new Exception('Username already taken');
        }

        if ($this->existsEmail($email)) {
            throw new Exception('Email already registered');
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Use transaction to avoid partial inserts
        $this->conn->begin_transaction();
        try {
            // Create user
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create user: ' . $this->conn->error);
            }

            $user_id = $stmt->insert_id;

            // Create free subscription
            $stmt = $this->conn->prepare("INSERT INTO subscriptions (user_id, plan_type) VALUES (?, 'free')");
            if (!$stmt) {
                throw new Exception('Failed to prepare subscription insert');
            }
            
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to create subscription');
            }

            // Initialize user settings
            $this->initializeUserSettings($user_id);

            $this->conn->commit();
            return $user_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Helper Methods
     */
    private function existsUsername($username) {
        $normalized = mb_strtolower(trim($username));
        $res = $this->conn->query("SELECT username FROM users");
        if (!$res) return false;
        
        while ($row = $res->fetch_assoc()) {
            if (mb_strtolower(trim($row['username'])) === $normalized) {
                return true;
            }
        }
        return false;
    }

    private function existsEmail($email) {
        $normalized = mb_strtolower(trim($email));
        
        // Try direct lookup first
        $stmt = $this->conn->prepare("SELECT email FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $r = $stmt->get_result();
            if ($r && $r->num_rows > 0) return true;
        }

        // Fallback: scan and compare in PHP to avoid collation issues
        $res = $this->conn->query("SELECT email FROM users");
        if (!$res) return false;
        
        while ($row = $res->fetch_assoc()) {
            if (mb_strtolower(trim($row['email'])) === $normalized) {
                return true;
            }
        }
        return false;
    }

    /**
     * User Profile Methods
     */
    public function getCurrentUser($user_id = null) {
        if ($user_id === null) {
            $user_id = $this->getCurrentUserId();
        }

        if (!$user_id) {
            return null;
        }

        $stmt = $this->conn->prepare("\n            SELECT id, username, email, subscription_type, created_at \n            FROM users \n            WHERE id = ?\n        ");

        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch user: ' . $stmt->error);
        }

        return $stmt->get_result()->fetch_assoc();
    }

    private function initializeUserSettings($user_id) {
        // Initialize user preferences with default values
        $defaultInventoryAlerts = json_encode([
            'low_stock' => 5,
            'expiry_warning' => 30
        ]);
        
        $defaultDashboard = json_encode([
            'default_view' => 'list',
            'show_stats' => true
        ]);
        
        $stmt = $this->conn->prepare("\n            INSERT INTO user_settings \n                (user_id, language, currency, inventory_alerts, dashboard_preferences) \n            VALUES \n                (?, 'en', 'MYR', ?, ?)\n        ");

        if (!$stmt) {
            throw new Exception('Failed to prepare settings insert: ' . $this->conn->error);
        }
        
        $stmt->bind_param("iss", $user_id, $defaultInventoryAlerts, $defaultDashboard);
        if (!$stmt->execute()) {
            throw new Exception('Failed to initialize user settings: ' . $stmt->error);
        }
    }
}
