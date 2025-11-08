<?php
require_once __DIR__ . '/../config/database.php';

class ProfileManager {
    private $conn;

    public function __construct() {
        $this->conn = connectDB();
    }

    /**
     * Profile Management Methods
     */
    public function getProfile($user_id) {
        $stmt = $this->conn->prepare("\n            SELECT u.id, u.username, u.email, u.created_at, u.subscription_type,\n                   us.inventory_alerts, us.dashboard_preferences,\n                   s.plan_type, s.start_date, s.end_date, s.status\n            FROM users u\n            LEFT JOIN user_settings us ON u.id = us.user_id\n            LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status != 'cancelled'\n            WHERE u.id = ?\n        ");
        
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch profile: ' . $stmt->error);
        }

        $profile = $stmt->get_result()->fetch_assoc();
        
        if ($profile) {
            // Parse JSON preferences
            $profile['inventory_alerts'] = json_decode($profile['inventory_alerts'], true);
            $profile['dashboard_preferences'] = json_decode($profile['dashboard_preferences'], true);
        }

        return $profile;
    }

    public function updateProfile($user_id, $data) {
        $this->conn->begin_transaction();
        
        try {
            // Update user table
            if (isset($data['username']) || isset($data['email'])) {
                $updates = [];
                $types = '';
                $params = [];

                if (isset($data['username'])) {
                    $updates[] = 'username = ?';
                    $types .= 's';
                    $params[] = $data['username'];
                }

                if (isset($data['email'])) {
                    $updates[] = 'email = ?';
                    $types .= 's';
                    $params[] = $data['email'];
                }

                $params[] = $user_id;
                $types .= 'i';

                $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception('Failed to prepare user update: ' . $this->conn->error);
                }

                $stmt->bind_param($types, ...$params);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to update user: ' . $stmt->error);
                }
            }

            // Update settings
            if (isset($data['inventory_alerts']) || isset($data['dashboard_preferences'])) {
                $settings = $this->getCurrentSettings($user_id);
                
                if ($settings) {
                    // Update existing settings
                    $updates = [];
                    $types = '';
                    $params = [];

                    if (isset($data['inventory_alerts'])) {
                        $updates[] = 'inventory_alerts = ?';
                        $types .= 's';
                        $params[] = json_encode($data['inventory_alerts']);
                    }

                    if (isset($data['dashboard_preferences'])) {
                        $updates[] = 'dashboard_preferences = ?';
                        $types .= 's';
                        $params[] = json_encode($data['dashboard_preferences']);
                    }

                    $params[] = $user_id;
                    $types .= 'i';

                    $sql = "UPDATE user_settings SET " . implode(', ', $updates) . " WHERE user_id = ?";
                    $stmt = $this->conn->prepare($sql);
                } else {
                    // Insert new settings
                    $stmt = $this->conn->prepare("\n                        INSERT INTO user_settings (user_id, inventory_alerts, dashboard_preferences)\n                        VALUES (?, ?, ?)\n                    ");
                    $types = 'iss';
                    $params = [
                        $user_id,
                        json_encode($data['inventory_alerts'] ?? []),
                        json_encode($data['dashboard_preferences'] ?? [])
                    ];
                }

                if (!$stmt) {
                    throw new Exception('Failed to prepare settings update: ' . $this->conn->error);
                }

                $stmt->bind_param($types, ...$params);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to update settings: ' . $stmt->error);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Helper Methods
     */
    private function getCurrentSettings($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch settings: ' . $stmt->error);
        }

        return $stmt->get_result()->fetch_assoc();
    }
}
