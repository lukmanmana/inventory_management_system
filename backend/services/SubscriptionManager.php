<?php
require_once __DIR__ . '/../config/database.php';

class SubscriptionManager {
    private $conn;
    private $free_storage_limit = 50; // Number of products allowed for free users
    private $premium_features = [
        'export_data' => true,
        'api_access' => true,
        'advanced_analytics' => true,
        'bulk_operations' => true,
        'custom_branding' => true
    ];

    public function __construct() {
        $this->conn = connectDB();
    }

    /**
     * Initialize subscription for a user if it doesn't exist
     */
    private function initializeSubscription($user_id) {
        $check = $this->conn->prepare("SELECT id FROM subscriptions WHERE user_id = ? LIMIT 1");
        if (!$check) throw new Exception('Prepare failed: ' . $this->conn->error);
        $check->bind_param('i', $user_id);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
            $insert = $this->conn->prepare("INSERT INTO subscriptions (user_id, plan_type, start_date, status) VALUES (?, 'free', NOW(), 'active')");
            if (!$insert) throw new Exception('Prepare failed: ' . $this->conn->error);
            $insert->bind_param('i', $user_id);
            $insert->execute();
        }
    }

    /**
     * Plan Management Methods
     */
    public function upgrade($user_id, $months = 1) {
        $this->conn->begin_transaction();
        try {
            // Initialize subscription if needed
            $this->initializeSubscription($user_id);
            
            // Mark user as paid
            $stmt = $this->conn->prepare("UPDATE users SET subscription_type = 'paid' WHERE id = ?");
            if (!$stmt) throw new Exception('Prepare failed: ' . $this->conn->error);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();

            // Check existing subscription
            $existing = $this->getCurrentSubscription($user_id);

            if ($existing) {
                // extend from current end_date if in future, otherwise from now
                $endDate = $existing['end_date'] ? new DateTime($existing['end_date']) : new DateTime();
                $now = new DateTime();
                if ($endDate < $now) $endDate = $now;
                $endDate->modify("+{$months} month");
                $newEnd = $endDate->format('Y-m-d H:i:s');

                $update = $this->conn->prepare("UPDATE subscriptions SET plan_type = 'paid', start_date = NOW(), end_date = ?, status = 'active' WHERE user_id = ?");
                if (!$update) throw new Exception('Prepare failed: ' . $this->conn->error);
                $update->bind_param('si', $newEnd, $user_id);
                $update->execute();
            } else {
                // create subscription row
                $newEnd = (new DateTime())->modify("+{$months} month")->format('Y-m-d H:i:s');
                $insert = $this->conn->prepare("INSERT INTO subscriptions (user_id, plan_type, start_date, end_date, status) VALUES (?, 'paid', NOW(), ?, 'active')");
                if (!$insert) throw new Exception('Prepare failed: ' . $this->conn->error);
                $insert->bind_param('is', $user_id, $newEnd);
                $insert->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('SubscriptionManager::upgrade failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function cancel($user_id) {
        $this->conn->begin_transaction();
        try {
            // Mark subscription as cancelled but let it run until end_date
            $stmt = $this->conn->prepare("\n                UPDATE subscriptions \n                SET status = 'cancelled'\n                WHERE user_id = ? AND status = 'active'\n            ");
            if (!$stmt) throw new Exception('Prepare failed: ' . $this->conn->error);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('SubscriptionManager::cancel failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function downgrade($user_id) {
        $this->conn->begin_transaction();
        try {
            // Check product count before downgrading
            if (!$this->checkDowngradeEligibility($user_id)) {
                throw new Exception('Cannot downgrade: Too many products for free plan');
            }

            $stmt = $this->conn->prepare("UPDATE users SET subscription_type = 'free' WHERE id = ?");
            if (!$stmt) throw new Exception('Prepare failed: ' . $this->conn->error);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();

            $stmt2 = $this->conn->prepare("UPDATE subscriptions SET plan_type = 'free', status = 'active', end_date = NULL WHERE user_id = ?");
            if (!$stmt2) throw new Exception('Prepare failed: ' . $this->conn->error);
            $stmt2->bind_param('i', $user_id);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('SubscriptionManager::downgrade failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Subscription Information Methods
     */
    public function getCurrentSubscription($user_id) {
        $stmt = $this->conn->prepare("\n            SELECT s.*, u.subscription_type \n            FROM subscriptions s\n            JOIN users u ON s.user_id = u.id\n            WHERE s.user_id = ?\n            ORDER BY s.id DESC\n            LIMIT 1\n        ");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function checkStorageLimit($user_id) {
        $stmt = $this->conn->prepare("\n            SELECT COUNT(*) as product_count, u.subscription_type \n            FROM products p\n            JOIN users u ON p.user_id = u.id\n            WHERE p.user_id = ?\n        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['subscription_type'] !== 'free' || 
               $result['product_count'] < $this->free_storage_limit;
    }

    public function checkFeatureAccess($user_id, $feature) {
        if (!isset($this->premium_features[$feature])) {
            throw new Exception('Invalid feature specified');
        }

        $subscription = $this->getCurrentSubscription($user_id);
        return $subscription && $subscription['subscription_type'] === 'paid' && 
               $subscription['status'] === 'active';
    }

    /**
     * Helper Methods
     */
    private function checkDowngradeEligibility($user_id) {
        $stmt = $this->conn->prepare("\n            SELECT COUNT(*) as product_count\n            FROM products\n            WHERE user_id = ?\n        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['product_count'] <= $this->free_storage_limit;
    }
}
