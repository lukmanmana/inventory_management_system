<?php
require_once __DIR__ . '/../services/SubscriptionManager.php';
require_once __DIR__ . '/../services/AuthManager.php';

header('Content-Type: application/json');

$auth = new AuthManager();
$subscription = new SubscriptionManager();

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
            $action = $_GET['action'] ?? 'status';
            
            switch ($action) {
                case 'status':
                    $sub_info = $subscription->getCurrentSubscription($user_id);
                    echo json_encode([
                        'success' => true,
                        'data' => $sub_info
                    ]);
                    break;

                case 'check_feature':
                    $feature = $_GET['feature'] ?? '';
                    if (empty($feature)) {
                        throw new Exception('Feature name is required');
                    }
                    
                    $hasAccess = $subscription->checkFeatureAccess($user_id, $feature);
                    echo json_encode([
                        'success' => true,
                        'has_access' => $hasAccess
                    ]);
                    break;

                case 'storage_limit':
                    $withinLimit = $subscription->checkStorageLimit($user_id);
                    echo json_encode([
                        'success' => true,
                        'within_limit' => $withinLimit
                    ]);
                    break;

                default:
                    throw new Exception('Invalid action');
            }
            break;

        case 'POST':
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'upgrade':
                    $months = isset($_POST['months']) ? max(1, (int)$_POST['months']) : 1;
                    
                    // In a real app, handle payment processing here
                    $mock = isset($_POST['mock_payment']) ? (bool)$_POST['mock_payment'] : true;
                    if (!$mock) {
                        $token = $_POST['payment_token'] ?? '';
                        if (empty($token)) {
                            throw new Exception('Payment token required for non-mock flow');
                        }
                        // Process payment with payment provider here
                    }

                    $subscription->upgrade($user_id, $months);
                    $_SESSION['subscription_type'] = 'paid';
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Subscription upgraded successfully',
                        'months' => $months
                    ]);
                    break;

                case 'cancel':
                    $subscription->cancel($user_id);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Subscription cancelled successfully'
                    ]);
                    break;

                case 'downgrade':
                    $subscription->downgrade($user_id);
                    $_SESSION['subscription_type'] = 'free';
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Subscription downgraded to free plan'
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
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
