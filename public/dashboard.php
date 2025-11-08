<?php
session_start();
require_once __DIR__ . '/../backend/services/AuthManager.php';
require_once __DIR__ . '/../backend/services/ProductManager.php';
require_once __DIR__ . '/../backend/services/SubscriptionManager.php';
require_once __DIR__ . '/../backend/services/CategoryManager.php';

$auth = new AuthManager();
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user_id = $auth->getCurrentUserId();
if (!$user_id) {
    header('Location: index.php');
    exit;
}

// Get user profile and details
$user = $auth->getCurrentUser($user_id);
if (!$user) {
    header('Location: index.php');
    exit;
}

$productManager = new ProductManager();
$subscriptionManager = new SubscriptionManager();

$products = $productManager->getAllByUser($user_id);
$lowStock = $productManager->getLowStock($user_id);
$subscriptionDetails = $subscriptionManager->getCurrentSubscription($user_id);

// Set subscription type from subscription details if available
$user['subscription_type'] = $subscriptionDetails['plan_type'] ?? $user['subscription_type'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #e6e9f0 0%, #eef1f5 100%);
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        .gradient-background {
            background: var(--primary-gradient);
            min-height: 100vh;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        }
        
        .navbar-glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
        }
        
        .gradient-text {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.5);
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(102, 126, 234, 0.8);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }
        
        .blur-backdrop {
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.8);
        }
        
        .table-container {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(31, 38, 135, 0.1);
        }
        
        .table-row-hover:hover {
            background: rgba(102, 126, 234, 0.05);
        }
        
        .button-gradient {
            background: var(--primary-gradient);
            transition: opacity 0.3s ease;
        }
        
        .button-gradient:hover {
            opacity: 0.9;
        }
        
        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>
    <!-- Add Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="gradient-background min-h-screen">
    <!-- Navigation -->
    <nav class="navbar-glass fixed w-full z-10 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-20">
                <div class="flex items-center space-x-8">
                    <div class="flex-shrink-0 flex items-center space-x-3">
                        <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-2.5 rounded-lg shadow-lg">
                            <i class="fas fa-boxes text-white text-2xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold gradient-text tracking-tight">Inventory Management System</h1>
                    </div>
                    <div class="hidden md:flex space-x-6">
                        <button onclick="reloadDashboard()" class="text-gray-600 hover:text-purple-600 font-medium transition-colors duration-150 flex items-center space-x-2 transform transition-transform active:scale-95">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </button>
                        <button onclick="openCategoryModal()" class="text-gray-600 hover:text-purple-600 font-medium transition-colors duration-150 flex items-center space-x-2">
                            <i class="fas fa-tags"></i>
                            <span>Categories</span>
                        </button>
                        <button onclick="showStockAlert()" class="text-gray-600 hover:text-purple-600 font-medium transition-colors duration-150 flex items-center space-x-2">
                            <i class="fas fa-bell"></i>
                            <span>Alerts</span>
                        </button>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <?php if ($user['subscription_type'] === 'free'): ?>
                    <button onclick="showUpgradeModal()" 
                        class="hidden md:flex items-center space-x-2 bg-gradient-to-r from-yellow-400 to-yellow-500 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-150 transform hover:-translate-y-0.5">
                        <i class="fas fa-crown text-yellow-100"></i>
                        <span>Upgrade to Pro</span>
                    </button>
                    <?php else: ?>
                    <button onclick="showUpgradeModal()" 
                        class="hidden md:flex items-center space-x-2 bg-gray-100 text-gray-800 px-4 py-2 rounded-lg shadow-sm hover:shadow-md transition-all duration-150">
                        <i class="fas fa-cog text-gray-600"></i>
                        <span>Manage Plan</span>
                    </button>
                    <?php endif; ?>
                    <div class="flex items-center space-x-4">
                        <div class="flex flex-col items-end">
                            <span class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($user['username']); ?></span>
                            <span class="text-xs text-gray-500"><?php echo ucfirst($user['subscription_type']); ?> Plan</span>
                        </div>
                        <button onclick="openProfileModal()" title="Edit profile" class="text-gray-400 hover:text-purple-600 transition-colors duration-150">
                            <i class="fas fa-user-circle text-xl"></i>
                        </button>
                        <a href="../backend/controllers/logout.php" 
                            class="text-gray-400 hover:text-red-500 transition-colors duration-150">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto pt-24 px-4 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="glass-effect rounded-2xl shadow-xl p-8 mb-8 relative overflow-hidden" data-aos="fade-down">
            <div class="relative z-10">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    Welcome back, <?php echo htmlspecialchars($user['username']); ?>! 👋
                </h1>
                <p class="text-gray-600 mb-6">Here's what's happening with your inventory today.</p>
                
                    <div class="flex flex-wrap gap-4">
                    <button onclick="openAddProductModal()" 
                        class="flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Product
                    </button>
                    <button onclick="generateReport()" 
                        class="flex items-center px-6 py-3 bg-white text-purple-600 border border-purple-200 rounded-lg hover:bg-purple-50 transition-all duration-200">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Generate Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" data-aos="fade-up">
            <!-- Total Products -->
            <div class="stat-card card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-green-100">
                        <i class="fas fa-box text-green-600 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">Products</span>
                </div>
                <h3 class="text-4xl font-bold text-gray-800 mb-1"><?php echo count($products); ?></h3>
                <p class="text-sm text-gray-500">Total Products</p>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <button onclick="openAddProductModal()" class="text-green-600 text-sm font-medium hover:text-green-700">
                        <i class="fas fa-plus mr-1"></i> Add New
                    </button>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="stat-card card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-yellow-100">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full">Alerts</span>
                </div>
                <h3 class="text-4xl font-bold text-gray-800 mb-1"><?php echo count($lowStock); ?></h3>
                <p class="text-sm text-gray-500">Low Stock Items</p>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <button onclick="showStockAlert()" class="text-yellow-600 text-sm font-medium hover:text-yellow-700">
                        <i class="fas fa-bell mr-1"></i> View Alerts
                    </button>
                </div>
            </div>

            <!-- Total Value -->
            <div class="stat-card card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-blue-100">
                        <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">Value</span>
                </div>
                <h3 class="text-4xl font-bold text-gray-800 mb-1">RM <?php 
                    $totalValue = array_reduce($products, function($carry, $item) {
                        return $carry + ($item['quantity'] * $item['unit_price']);
                    }, 0);
                    echo number_format($totalValue, 2);
                ?></h3>
                <p class="text-sm text-gray-500">Total Inventory Value</p>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <button onclick="generateReport()" class="text-blue-600 text-sm font-medium hover:text-blue-700">
                        <i class="fas fa-file-alt mr-1"></i> Generate Report
                    </button>
                </div>
            </div>

            <!-- Subscription -->
            <div class="stat-card card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-purple-100">
                        <i class="fas fa-crown text-purple-600 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">
                        <?php echo ucfirst($subscriptionDetails['status']); ?>
                    </span>
                </div>
                <h3 class="text-4xl font-bold text-gray-800 mb-1"><?php echo ucfirst($user['subscription_type']); ?></h3>
                <p class="text-sm text-gray-500">Current Plan</p>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <!-- upgrade/manage button removed (kept only at top navbar) -->
                </div>
            </div>
        </div>

        <!-- Compact premium banner removed per user request -->

        <!-- Actions (duplicate Add New Product button removed per request) -->
        <div class="mb-8" data-aos="fade-up" data-aos-delay="100">
            <!-- Duplicate action button was removed to avoid redundancy. Use the top navbar "Add New Product" or the "Add First Product" empty-state button. -->
        </div>

        <!-- Low Stock Alerts -->
        <?php if (!empty($lowStock)): ?>
        <div class="glass-effect rounded-xl shadow-xl mb-8 overflow-hidden" data-aos="fade-up" data-aos-delay="200">
            <div class="bg-gradient-to-r from-yellow-500 to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-white font-bold text-lg">Stock Alert Center</h3>
                            <p class="text-white/80 text-sm">Items requiring immediate attention</p>
                        </div>
                    </div>
                    <div>
                        <span class="bg-white/20 text-white px-4 py-1 rounded-full text-sm">
                            <?php echo count($lowStock); ?> items need attention
                        </span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($lowStock as $item): ?>
                    <div class="bg-white border-2 border-yellow-100 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center">
                                <div class="bg-yellow-50 p-2 rounded-lg mr-3">
                                    <i class="fas fa-box text-yellow-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p class="text-xs text-gray-500">SKU: <?php echo htmlspecialchars($item['sku']); ?></p>
                                </div>
                            </div>
                            <?php 
                                $percentage = ($item['quantity'] / $item['minimum_quantity']) * 100;
                                $colorClass = $item['quantity'] === 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700';
                            ?>
                            <span class="<?php echo $colorClass; ?> px-3 py-1 rounded-full text-xs font-medium">
                                <?php echo $item['quantity']; ?> units left
                            </span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Current Stock:</span>
                                <span class="font-medium text-gray-900"><?php echo $item['quantity']; ?> units</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Minimum Required:</span>
                                <span class="font-medium text-gray-900"><?php echo $item['minimum_quantity']; ?> units</span>
                            </div>
                            <div class="mt-2">
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full <?php echo $percentage <= 0 ? 'bg-red-500' : 'bg-yellow-500'; ?> transition-all duration-300"
                                        style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button onclick="editProduct(<?php echo $item['id']; ?>)" 
                                class="inline-flex items-center justify-center px-4 py-2 border border-yellow-200 rounded-lg text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 transition-colors duration-200">
                                <i class="fas fa-pen mr-2"></i>
                                Update Stock
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="glass-effect rounded-xl shadow-xl p-6 mb-8" data-aos="fade-up">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-bolt text-purple-600 mr-2"></i>
                    Quick Actions
                </h3>
                <div class="flex flex-wrap gap-2">
                    <button onclick="openCategoryModal()" class="inline-flex items-center px-4 py-2 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100">
                        <i class="fas fa-tags mr-2"></i>
                        Manage Categories
                    </button>
                    <button onclick="exportToCSV()" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100">
                        <i class="fas fa-file-export mr-2"></i>
                        Export CSV
                    </button>
                    <?php if ($user['subscription_type'] === 'paid'): ?>
                    <button onclick="exportFullCSV()" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                        <i class="fas fa-file-download mr-2"></i>
                        Full Export
                    </button>
                    <?php endif; ?>
                    <button onclick="generateReport()" class="inline-flex items-center px-4 py-2 rounded-lg bg-green-50 text-green-700 hover:bg-green-100">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Report
                    </button>
                    <button onclick="showStockAlert()" class="inline-flex items-center px-4 py-2 rounded-lg bg-yellow-50 text-yellow-700 hover:bg-yellow-100">
                        <i class="fas fa-bell mr-2"></i>
                        Alerts
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="glass-effect rounded-2xl shadow-xl overflow-hidden" data-aos="fade-up" data-aos-delay="200">
            <!-- Table Header -->
            <div class="bg-white px-8 py-6 border-b border-gray-100">
                <!-- Header Title and Search -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Products Overview</h3>
                        <p class="text-sm text-gray-500">Manage and monitor your inventory</p>
                    </div>
                    <div class="w-full lg:w-auto flex flex-col sm:flex-row gap-4">
                        <div class="relative flex-1 sm:min-w-[300px]">
                            <input type="text" id="searchProductHeader" placeholder="Search products..." 
                                class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        <div class="relative flex-1 sm:min-w-[200px]">
                            <select id="filterCategory" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                                <option value="">All Categories</option>
                                <!-- Categories will be populated dynamically -->
                            </select>
                            <i class="fas fa-filter absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Pills -->
                <div class="flex flex-wrap gap-2">
                    <button onclick="applyFilter('all')" class="px-4 py-2 rounded-full bg-purple-100 text-purple-700 hover:bg-purple-200 transition-colors duration-200">
                        <i class="fas fa-layer-group mr-1"></i> All Products
                    </button>
                    <button onclick="applyFilter('low-stock')" class="px-4 py-2 rounded-full bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition-colors duration-200">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock
                    </button>
                    <button onclick="applyFilter('out-of-stock')" class="px-4 py-2 rounded-full bg-red-100 text-red-700 hover:bg-red-200 transition-colors duration-200">
                        <i class="fas fa-times-circle mr-1"></i> Out of Stock
                    </button>
                </div>
            </div>

                    </div>
                </div>
            </div>
            
            <!-- Product Cards List (stacked) -->
            <div class="p-6 table-container">
                <div class="space-y-4">
                    <?php foreach ($products as $product): ?>
                    <?php
                        $pct = $product['minimum_quantity'] > 0 ? round(($product['quantity'] / $product['minimum_quantity']) * 100) : 0;
                        $pct = max(0, min(100, $pct));
                        $status = ($product['quantity'] <= 0) ? 'out' : (($product['quantity'] <= $product['minimum_quantity']) ? 'low' : 'ok');
                    ?>
                    <div class="product-item bg-white border rounded-xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between shadow-sm hover:shadow-md transition-all duration-150" data-id="<?php echo intval($product['id']); ?>" data-min-quantity="<?php echo intval($product['minimum_quantity']); ?>" data-quantity="<?php echo intval($product['quantity']); ?>" data-category-id="<?php echo intval($product['category_id'] ?? 0); ?>">
                        <div class="flex items-start md:items-center gap-4 md:flex-1">
                            <div class="h-14 w-14 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-box text-purple-600 text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-3">
                                    <h4 class="text-md font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <span class="text-xs text-gray-400">SKU: <?php echo htmlspecialchars($product['sku']); ?></span>
                                </div>
                                <div class="text-sm text-gray-500 mt-1 truncate" style="max-width:48rem"><?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?><?php if(!empty($product['description'])) echo ' · ' . htmlspecialchars($product['description']); ?></div>
                            </div>
                        </div>

                        <div class="mt-4 md:mt-0 md:ml-6 md:flex md:items-center md:gap-6">
                            <div class="text-right md:text-left">
                                <div class="text-sm text-gray-500">Quantity</div>
                                <div class="text-lg font-medium text-gray-900"><?php echo number_format($product['quantity']); ?></div>
                                <div class="text-xs text-gray-400">Min: <?php echo intval($product['minimum_quantity']); ?></div>
                            </div>
                            <div class="w-40 md:w-48">
                                <div class="text-sm text-gray-500">Price</div>
                                <div class="text-lg font-medium text-gray-900">RM <?php echo number_format($product['unit_price'], 2); ?></div>
                            </div>
                            <div class="w-48 md:w-56">
                                <div class="text-sm text-gray-500">Status</div>
                                <div class="mt-1">
                                    <?php if ($status === 'out'): ?>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-2"></i> Out of Stock
                                        </span>
                                    <?php elseif ($status === 'low'): ?>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-2"></i> Low Stock
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-2"></i> In Stock
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 <?php echo ($status === 'out') ? 'bg-red-500' : ($status === 'low' ? 'bg-yellow-500' : 'bg-green-500'); ?> transition-all duration-300" style="width: <?php echo $pct; ?>%"></div>
                                </div>
                            </div>
                            <div class="flex-shrink-0 mt-4 md:mt-0">
                                <div class="flex items-center space-x-2">
                                    <button onclick="editProduct(<?php echo $product['id']; ?>)" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-sm text-gray-700 hover:bg-purple-50 hover:border-purple-200 transition-colors">
                                        <i class="fas fa-pen mr-2 text-purple-600"></i> Edit
                                    </button>
                                    <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-sm text-gray-700 hover:bg-red-50 hover:border-red-200 transition-colors">
                                        <i class="fas fa-trash-alt mr-2 text-red-600"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
    <!-- Empty State -->
        <?php if (empty($products)): ?>
        <div class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-100 mb-4">
                <i class="fas fa-box text-purple-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No products yet</h3>
            <p class="text-gray-500 mb-6">Get started by adding your first product to the inventory.</p>
            <button onclick="openAddProductModal()" 
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                <i class="fas fa-plus mr-2"></i>
                Add First Product
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center text-sm text-gray-200">
            <p>© 2025 Inventory Management System. Project Web Application Development</p>
        </div>
    </div>

    <!-- Category Management Modal -->
    <div id="categoryModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-30 backdrop-blur-sm"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-lg transform transition-all" data-aos="zoom-in">
                <div class="relative bg-white rounded-t-2xl px-6 py-4 flex items-center justify-between border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <i class="fas fa-tags text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Manage Categories</h3>
                            <p class="text-sm text-gray-500">Organize your products efficiently</p>
                        </div>
                    </div>
                    <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <!-- Add Category Form -->
                    <form id="addCategoryForm" class="mb-6">
                        <div class="mb-4">
                            <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <input type="text" id="categoryName" name="name" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="categoryDescription" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea id="categoryDescription" name="description" rows="2"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full px-4 py-2 border border-transparent rounded-md text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Add Category
                        </button>
                    </form>

                    <!-- Categories List -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Existing Categories</h4>
                        <div id="categoriesList" class="space-y-2 max-h-60 overflow-y-auto">
                            <!-- Categories will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-30 backdrop-blur-sm"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all" data-aos="zoom-in">
                <div class="relative bg-white rounded-t-2xl px-6 py-4 flex items-center justify-between border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <i class="fas fa-edit text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Edit Product</h3>
                            <p class="text-sm text-gray-500">Update product information</p>
                        </div>
                    </div>
                    <button onclick="closeEditProductModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="editProductForm" class="p-6">
                    <input type="hidden" id="edit_product_id" name="id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-box text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_name" name="name" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="edit_sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_sku" name="sku" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="edit_quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-cubes text-gray-400"></i>
                                </div>
                                <input type="number" id="edit_quantity" name="quantity" min="0" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="edit_minimum_quantity" class="block text-sm font-medium text-gray-700 mb-1">Minimum Quantity</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-exclamation-triangle text-gray-400"></i>
                                </div>
                                <input type="number" id="edit_minimum_quantity" name="minimum_quantity" min="0" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="edit_unit_price" class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400">RM</span>
                                </div>
                                <input type="number" id="edit_unit_price" name="unit_price" min="0" step="0.01" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="edit_category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <select id="edit_category_id" name="category_id"
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">No Category</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="edit_description" name="description" rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeEditProductModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-30 backdrop-blur-sm"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all" data-aos="zoom-in">
                <div class="relative bg-white rounded-t-2xl px-6 py-4 flex items-center justify-between border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <i class="fas fa-plus text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Add New Product</h3>
                            <p class="text-sm text-gray-500">Add a new product to your inventory</p>
                        </div>
                    </div>
                    <button onclick="closeAddProductModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="addProductForm" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-box text-gray-400"></i>
                                </div>
                                <input type="text" id="name" name="name" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-400"></i>
                                </div>
                                <input type="text" id="sku" name="sku" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-cubes text-gray-400"></i>
                                </div>
                                <input type="number" id="quantity" name="quantity" min="0" value="0" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="minimum_quantity" class="block text-sm font-medium text-gray-700 mb-1">Minimum Quantity</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-exclamation-triangle text-gray-400"></i>
                                </div>
                                <input type="number" id="minimum_quantity" name="minimum_quantity" min="0" value="5" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400">RM</span>
                                </div>
                                <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" value="0.00" required
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <select id="category_id" name="category_id"
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">No Category</option>
                                    <!-- Categories will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeAddProductModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dashboard reload function
        function reloadDashboard() {
            const loadingToast = document.createElement('div');
            loadingToast.className = 'fixed top-4 right-4 bg-purple-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
            loadingToast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-sync-alt fa-spin"></i>
                    <span>Refreshing dashboard...</span>
                </div>
            `;
            document.body.appendChild(loadingToast);

            // Reload the page with a small delay to show the loading animation
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }

        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });

        // Load Categories
        function loadCategories() {
            fetch('../backend/controllers/get_categories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update categories list in the modal
                        const categoriesList = document.getElementById('categoriesList');
                        categoriesList.innerHTML = data.categories.map(category => `
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">${category.name}</p>
                                    ${category.description ? `<p class="text-xs text-gray-500">${category.description}</p>` : ''}
                                </div>
                                <button onclick="deleteCategory(${category.id})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `).join('');

                        // Update category select in add product modal
                        const categorySelect = document.getElementById('category_id');
                        categorySelect.innerHTML = '<option value="">No Category</option>' +
                            data.categories.map(category => `
                                <option value="${category.id}">${category.name}</option>
                            `).join('');

                        // Update filter select
                        const filterCategory = document.getElementById('filterCategory');
                        filterCategory.innerHTML = '<option value="">All Categories</option>' +
                            data.categories.map(category => `
                                <option value="${category.id}">${category.name}</option>
                            `).join('');
                    }
                });
        }

        // Category Modal Functions
        function openCategoryModal() {
            document.getElementById('categoryModal').classList.remove('hidden');
            loadCategories();
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').classList.add('hidden');
        }

        // Add Category Form Handler
        document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../backend/controllers/add_category.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.reset();
                    loadCategories();
                }
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the category');
            });
        });

        // Delete Category Function
        function deleteCategory(id) {
            if (confirm('Are you sure you want to delete this category? Products in this category will be set to "No Category".')) {
                fetch(`../backend/controllers/categories.php`, {
                    method: 'DELETE',
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCategories();
                    }
                    alert(data.message);
                });
            }
        }

        // Load categories when page loads
        document.addEventListener('DOMContentLoaded', loadCategories);

        // Local filter state
        let currentFilterType = 'all';
        let currentSearchTerm = '';
        let currentCategory = '';

        // Product Search and Filter (works on card list) — update state and refresh
        function performSearch(searchTerm) {
            currentSearchTerm = String(searchTerm || '').toLowerCase().trim();
            refreshProductVisibility();
        }

        // Add search event listeners to both search inputs
        ['searchProductHeader', 'searchProductFilter'].forEach(id => {
            const searchInput = document.getElementById(id);
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    performSearch(e.target.value);
                    // Sync other search input
                    const otherId = id === 'searchProductHeader' ? 'searchProductFilter' : 'searchProductHeader';
                    const otherInput = document.getElementById(otherId);
                    if (otherInput && otherInput.value !== e.target.value) {
                        otherInput.value = e.target.value;
                    }
                });
            }
        });

        // Filter Functions (update state and refresh)
        function applyFilter(type) {
            currentFilterType = type || 'all';
            refreshProductVisibility();
        }

        // Category change handler (update state and refresh)
        const filterCategoryEl = document.getElementById('filterCategory');
        if (filterCategoryEl) {
            filterCategoryEl.addEventListener('change', function(e) {
                currentCategory = String(e.target.value || '').trim();
                refreshProductVisibility();
            });
        }

        // Centralized function to refresh visibility of product cards based on search, category, and pill filters
        function refreshProductVisibility() {
            const items = document.querySelectorAll('.product-item');

            items.forEach(item => {
                const text = (item.textContent || '').toLowerCase();
                const matchesSearch = !currentSearchTerm || text.indexOf(currentSearchTerm) !== -1;

                const catId = String(item.getAttribute('data-category-id') || '').trim();
                const matchesCategory = !currentCategory || currentCategory === '' || catId === currentCategory;

                const quantity = parseInt(item.getAttribute('data-quantity') || '0', 10);
                const minQuantity = parseInt(item.getAttribute('data-min-quantity') || '0', 10);
                let matchesPill = true;
                if (currentFilterType === 'low-stock') matchesPill = (quantity <= minQuantity && quantity > 0);
                else if (currentFilterType === 'out-of-stock') matchesPill = (quantity === 0);

                item.style.display = (matchesSearch && matchesCategory && matchesPill) ? '' : 'none';
            });
        }

        // Export to CSV (reads from product cards)
        function exportToCSV() {
            const items = document.querySelectorAll('.product-item');
            let csv = 'Name,SKU,Quantity,Price\n';

            items.forEach(item => {
                const nameEl = item.querySelector('h4');
                const skuEl = item.querySelector('.min-w-0 .flex.items-center span');
                const qty = item.getAttribute('data-quantity') || '';
                const priceEl = item.querySelector('.text-lg.font-medium.text-gray-900');

                const name = nameEl ? nameEl.textContent.trim() : '';
                let sku = skuEl ? skuEl.textContent.trim() : '';
                sku = sku.replace(/^SKU:\s*/i, '');
                const price = priceEl ? priceEl.textContent.trim() : '';

                csv += `"${name}","${sku}","${qty}","${price}"\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', 'inventory_report.csv');
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        // Premium export (server-side) - triggers file download
        function exportFullCSV() {
            // Use the existing products endpoint which supports server-side export via query params
            // This file (`products.php`) handles `operation=export&format=csv` and will return a CSV download.
            const exportUrl = '../backend/controllers/products.php?operation=export&format=csv';

            // open the endpoint in a new tab/window to trigger download with session cookie
            const w = window.open(exportUrl, '_blank');
            if (!w) {
                // Popup blocked, fallback to navigating current window
                window.location.href = exportUrl;
            }
        }

        // Generate Product Overview Report
        function generateReport() {
            // Create a new window for the report
            const reportWindow = window.open('', '_blank');
            
            // Get current date and time
            const now = new Date().toLocaleString('en-MY', { 
                timeZone: 'Asia/Kuala_Lumpur',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Calculate totals
            const totalProducts = <?php echo count($products); ?>;
            const totalValue = <?php echo array_reduce($products, function($carry, $item) {
                return $carry + ($item['quantity'] * $item['unit_price']);
            }, 0); ?>;
            const lowStockCount = <?php echo count($lowStock); ?>;
            const outOfStockCount = <?php echo count(array_filter($products, function($p) { return $p['quantity'] <= 0; })); ?>;

            // Generate the report HTML
            const reportHtml = `
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Product Overview Report</title>
                    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
                    <style>
                        body {
                            font-family: 'Inter', system-ui, -apple-system, sans-serif;
                            line-height: 1.5;
                            margin: 0;
                            padding: 2rem;
                        }
                        .report-container {
                            max-width: 1000px;
                            margin: 0 auto;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 2rem;
                            padding-bottom: 1rem;
                            border-bottom: 2px solid #e5e7eb;
                        }
                        .logo {
                            font-size: 1.5rem;
                            font-weight: 700;
                            color: #4f46e5;
                            margin-bottom: 0.5rem;
                        }
                        .timestamp {
                            color: #6b7280;
                            font-size: 0.875rem;
                        }
                        .stats-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                            gap: 1rem;
                            margin-bottom: 2rem;
                        }
                        .stat-card {
                            background: #f9fafb;
                            padding: 1rem;
                            border-radius: 0.5rem;
                            border: 1px solid #e5e7eb;
                        }
                        .stat-label {
                            color: #6b7280;
                            font-size: 0.875rem;
                            margin-bottom: 0.5rem;
                        }
                        .stat-value {
                            font-size: 1.5rem;
                            font-weight: 600;
                            color: #111827;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 2rem;
                        }
                        th {
                            background: #f9fafb;
                            padding: 0.75rem 1rem;
                            text-align: left;
                            font-size: 0.75rem;
                            text-transform: uppercase;
                            color: #6b7280;
                            border-bottom: 1px solid #e5e7eb;
                        }
                        td {
                            padding: 0.75rem 1rem;
                            border-bottom: 1px solid #e5e7eb;
                        }
                        .status-badge {
                            display: inline-block;
                            padding: 0.25rem 0.75rem;
                            border-radius: 9999px;
                            font-size: 0.75rem;
                            font-weight: 500;
                        }
                        .status-instock { background: #dcfce7; color: #166534; }
                        .status-lowstock { background: #fef3c7; color: #92400e; }
                        .status-outofstock { background: #fee2e2; color: #991b1b; }
                        @media print {
                            body { padding: 1rem; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="report-container">
                        <div class="header">
                            <div class="logo">Inventory Management System</div>
                            <div class="timestamp">Product Overview Report - Generated on ${now}</div>
                        </div>

                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-label">Total Products</div>
                                <div class="stat-value">${totalProducts}</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Total Value</div>
                                <div class="stat-value">RM ${totalValue.toFixed(2)}</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Low Stock Items</div>
                                <div class="stat-value">${lowStockCount}</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Out of Stock</div>
                                <div class="stat-value">${outOfStockCount}</div>
                            </div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?></td>
                                    <td><?php echo number_format($product['quantity']); ?></td>
                                    <td>RM <?php echo number_format($product['unit_price'], 2); ?></td>
                                    <td>RM <?php echo number_format($product['quantity'] * $product['unit_price'], 2); ?></td>
                                    <td>
                                        <?php if ($product['quantity'] <= 0): ?>
                                            <span class="status-badge status-outofstock">Out of Stock</span>
                                        <?php elseif ($product['quantity'] <= $product['minimum_quantity']): ?>
                                            <span class="status-badge status-lowstock">Low Stock</span>
                                        <?php else: ?>
                                            <span class="status-badge status-instock">In Stock</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="no-print" style="text-align: center; margin-top: 2rem;">
                            <button onclick="window.print()" style="
                                padding: 0.75rem 1.5rem;
                                background: #4f46e5;
                                color: white;
                                border: none;
                                border-radius: 0.5rem;
                                font-weight: 500;
                                cursor: pointer;
                            ">Print Report</button>
                        </div>
                    </div>
                </body>
                </html>
            `;

            // Write the report to the new window
            reportWindow.document.write(reportHtml);
            reportWindow.document.close();
        }

        // Stock Alert Settings
        function showStockAlert() {
            // Open the stock alert modal and load current settings
            const modal = document.getElementById('stockAlertModal');
            const lowStockInput = document.getElementById('sa_low_stock');
            const expiryInput = document.getElementById('sa_expiry_warning');

            // Fetch current profile to get settings
            fetch('../backend/controllers/profile.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data) {
                        const alerts = data.data.inventory_alerts || {};
                        lowStockInput.value = alerts.low_stock ?? 5;
                        expiryInput.value = alerts.expiry_warning ?? 30;
                        modal.classList.remove('hidden');
                    } else {
                        alert('Failed to load settings');
                    }
                })
                .catch(err => {
                    console.error('Failed to load profile:', err);
                    alert('An error occurred while loading settings');
                });
        }

        // View Toggle
        function toggleView(type) {
            const container = document.querySelector('.products-container');
            if (type === 'grid') {
                container.classList.remove('table-view');
                container.classList.add('grid-view');
            } else {
                container.classList.remove('grid-view');
                container.classList.add('table-view');
            }
        }

        function openAddProductModal() {
            // Load categories first
            fetch('../backend/controllers/get_categories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate category dropdown
                        const categorySelect = document.getElementById('category_id');
                        categorySelect.innerHTML = '<option value="">No Category</option>' +
                            data.categories.map(category => `
                                <option value="${category.id}">${category.name}</option>
                            `).join('');
                        
                        // Show modal
                        document.getElementById('addProductModal').classList.remove('hidden');
                    } else {
                        alert('Failed to load categories. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading categories');
                });
        }

        function showUpgradeModal() {
            // Show upgrade modal and populate details
            try {
                const modal = document.getElementById('upgradeModal');
                const details = <?php echo json_encode($subscriptionDetails ?? []); ?> || {};
                document.getElementById('currentPlan').textContent = (details.plan_type || '<?php echo $user['subscription_type']; ?>').toUpperCase();
                document.getElementById('currentStatus').textContent = (details.status || 'N/A').toUpperCase();
                document.getElementById('subscriptionEnd').textContent = details.end_date ? details.end_date : 'N/A';
                // default months
                document.getElementById('upgradeMonths').value = 1;
                modal.classList.remove('hidden');
            } catch (err) {
                console.error('Failed to open upgrade modal', err);
                alert('Could not open upgrade dialog');
            }
        }

        function editProduct(id) {
            // First load categories
            fetch('../backend/controllers/get_categories.php')
                .then(response => response.json())
                .then(categoryData => {
                    if (categoryData.success) {
                        // Populate category dropdown
                        const categorySelect = document.getElementById('edit_category_id');
                        categorySelect.innerHTML = '<option value="">No Category</option>' +
                            categoryData.categories.map(category => `
                                <option value="${category.id}">${category.name}</option>
                            `).join('');
                        
                        // Then fetch product details
                        return fetch(`../backend/controllers/edit_product.php?id=${id}`);
                    } else {
                        throw new Error('Failed to load categories');
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        // Populate form fields
                        document.getElementById('edit_product_id').value = product.id;
                        document.getElementById('edit_name').value = product.name;
                        document.getElementById('edit_sku').value = product.sku;
                        document.getElementById('edit_quantity').value = product.quantity;
                        document.getElementById('edit_minimum_quantity').value = product.minimum_quantity;
                        document.getElementById('edit_unit_price').value = product.unit_price;
                        document.getElementById('edit_category_id').value = product.category_id || '';
                        document.getElementById('edit_description').value = product.description || '';
                        
                        // Show modal
                        document.getElementById('editProductModal').classList.remove('hidden');
                    } else {
                        alert(data.message || 'Failed to load product details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading product details');
                });
        }

        function closeEditProductModal() {
            document.getElementById('editProductModal').classList.add('hidden');
        }

        // Edit Product Form Handler
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../backend/controllers/edit_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeEditProductModal();
                    location.reload();
                }
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the product');
            });
        });

        // Close modal when clicking outside
        document.getElementById('editProductModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditProductModal();
            }
        });

        function closeAddProductModal() {
            document.getElementById('addProductModal').classList.add('hidden');
        }

        // Add event listener for the Add Product form
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../backend/controllers/add_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddProductModal();
                    location.reload();
                } else {
                    alert(data.message || 'Failed to add product');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the product');
            });
        });

        // Close modal when clicking outside
        document.getElementById('addProductModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddProductModal();
            }
        });

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch(`../backend/controllers/delete_product.php`, {
                    method: 'DELETE',
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete product');
                    }
                });
            }
        }

        // Allow double-click on a product card to open edit modal (delegation)
        const productsContainer = document.querySelector('.table-container');
        if (productsContainer) {
            productsContainer.addEventListener('dblclick', function(e) {
                const card = e.target.closest('.product-item');
                if (card && card.dataset && card.dataset.id) {
                    editProduct(card.dataset.id);
                }
            });
        }

        // Handle Escape key to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddProductModal();
            }
        });

        // Upgrade modal helpers
        function closeUpgradeModal() {
            document.getElementById('upgradeModal').classList.add('hidden');
        }

        function performUpgrade() {
            const months = parseInt(document.getElementById('upgradeMonths').value) || 1;
            const fd = new FormData();
            fd.append('action', 'upgrade');  // Add required action parameter
            fd.append('mock_payment', '1');
            fd.append('months', months);

            fetch('../backend/controllers/subscriptions.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Upgraded');
                    closeUpgradeModal();
                    location.reload();
                } else {
                    alert(data.message || 'Upgrade failed');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error performing upgrade');
            });
        }

        function performCancel() {
            // Check if user is on free plan
            const currentPlan = document.getElementById('currentPlan').textContent.toLowerCase();
            if (currentPlan === 'free') {
                alert('You are already on the free plan.');
                return;
            }

            if (!confirm('Are you sure you want to cancel your subscription and downgrade to free? This action cannot be undone.')) return;

            const formData = new FormData();
            formData.append('action', 'downgrade');

            fetch('../backend/controllers/subscriptions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Subscription cancelled successfully');
                    closeUpgradeModal();
                    location.reload();
                } else {
                    throw new Error(data.message || 'Failed to cancel subscription');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while cancelling the subscription');
            });
        }

        // Stock Alert modal helpers
        function closeStockAlert() {
            document.getElementById('stockAlertModal').classList.add('hidden');
        }

        function performSaveStockAlert() {
            const lowStock = parseInt(document.getElementById('sa_low_stock').value, 10);
            const expiry = parseInt(document.getElementById('sa_expiry_warning').value, 10);

            if (isNaN(lowStock) || lowStock < 0) {
                alert('Please enter a valid low stock threshold');
                return;
            }
            if (isNaN(expiry) || expiry < 0) {
                alert('Please enter a valid expiry warning (days)');
                return;
            }

            const fd = new FormData();
            fd.append('inventory_alerts', JSON.stringify({
                low_stock: lowStock,
                expiry_warning: expiry
            }));

            fetch('../backend/controllers/profile.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Stock alert settings saved');
                    closeStockAlert();
                } else {
                    alert(data.message || 'Failed to save settings');
                }
            })
            .catch(err => {
                console.error('Failed to save settings:', err);
                alert('An error occurred while saving settings');
            });
        }

        // Profile modal helpers
        function openProfileModal() {
            const modal = document.getElementById('profileModal');
            // load profile
            fetch('../backend/controllers/profile.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data) {
                        const profile = data.data;
                        document.getElementById('pf_username').value = profile.username || '';
                        document.getElementById('pf_email').value = profile.email || '';
                        const alerts = profile.inventory_alerts || {};
                        document.getElementById('pf_low_stock').value = alerts.low_stock ?? 5;
                        document.getElementById('pf_expiry_warning').value = alerts.expiry_warning ?? 30;
                        modal.classList.remove('hidden');
                    } else {
                        alert('Failed to load profile');
                    }
                })
                .catch(err => {
                    console.error('Failed to load profile:', err);
                    alert('An error occurred while loading profile');
                });
        }

        function closeProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
        }

        function saveProfile() {
            const username = document.getElementById('pf_username').value.trim();
            const email = document.getElementById('pf_email').value.trim();
            const lowStock = parseInt(document.getElementById('pf_low_stock').value, 10) || 5;
            const expiry = parseInt(document.getElementById('pf_expiry_warning').value, 10) || 30;

            if (!username || !email) {
                alert('Username and email are required');
                return;
            }

            const fd = new FormData();
            fd.append('username', username);
            fd.append('email', email);
            // send inventory_alerts as array fields so backend treats them as array
            fd.append('inventory_alerts[low_stock]', String(lowStock));
            fd.append('inventory_alerts[expiry_warning]', String(expiry));

            fetch('../backend/controllers/profile.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Profile updated');
                    closeProfileModal();
                    // update username shown in navbar
                    const usernameElt = document.querySelector('.flex.flex-col.items-end span');
                    if (usernameElt) usernameElt.textContent = username;
                } else {
                    alert(data.message || 'Failed to update profile');
                }
            })
            .catch(err => {
                console.error('Failed to save profile:', err);
                alert('An error occurred while saving profile');
            });
        }
    </script>
    <!-- Stock Alert Modal -->
    <div id="stockAlertModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="glass-effect rounded-xl shadow-2xl w-full max-w-md transform transition-all" data-aos="zoom-in">
                <div class="bg-white rounded-t-xl px-6 py-4 flex items-center justify-between border-b border-gray-100">
                    <h3 class="text-gray-900 font-bold">Stock Alert Settings</h3>
                    <button onclick="closeStockAlert()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">Set default thresholds for low-stock and expiry warnings. These are used as user-wide defaults when adding products.</p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Low stock threshold (units)</label>
                        <input id="sa_low_stock" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry warning (days)</label>
                        <input id="sa_expiry_warning" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button onclick="closeStockAlert()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                        <button onclick="performSaveStockAlert()" class="px-4 py-2 rounded-md text-white bg-gradient-to-r from-purple-600 to-purple-700">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Modal (polished to match login/dashboard) -->
    <div id="profileModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="glass-effect rounded-xl shadow-2xl w-full max-w-md transform transition-all" data-aos="zoom-in">
                <div class="px-8 py-8 bg-white rounded-xl">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-3 rounded-lg bg-purple-50">
                                <i class="fas fa-user text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Edit Profile</h3>
                                <p class="text-sm text-gray-500">Update your account information and alert defaults</p>
                            </div>
                        </div>
                        <button onclick="closeProfileModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form id="profileForm" class="space-y-4">
                        <div>
                            <label for="pf_username" class="text-sm font-medium text-gray-700">Username</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="pf_username" name="username" type="text" required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-150 ease-in-out sm:text-sm" />
                            </div>
                        </div>

                        <div>
                            <label for="pf_email" class="text-sm font-medium text-gray-700">Email</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input id="pf_email" name="email" type="email" required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-150 ease-in-out sm:text-sm" />
                            </div>
                        </div>

                        <hr class="my-4" />

                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Inventory Alerts</h4>

                        <div>
                            <label for="pf_low_stock" class="text-sm font-medium text-gray-700">Low stock threshold (units)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-exclamation-triangle text-gray-400"></i>
                                </div>
                                <input id="pf_low_stock" name="inventory_alerts[low_stock]" type="number" min="0" value="5"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-150 ease-in-out sm:text-sm" />
                            </div>
                        </div>

                        <div>
                            <label for="pf_expiry_warning" class="text-sm font-medium text-gray-700">Expiry warning (days)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-day text-gray-400"></i>
                                </div>
                                <input id="pf_expiry_warning" name="inventory_alerts[expiry_warning]" type="number" min="0" value="30"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-150 ease-in-out sm:text-sm" />
                            </div>
                        </div>

                        <div class="flex justify-end mt-2">
                            <button type="button" onclick="closeProfileModal()" class="mr-3 group relative inline-flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">Cancel</button>
                            <button type="button" onclick="saveProfile()" class="group relative inline-flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Upgrade Modal -->
    <div id="upgradeModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="glass-effect rounded-xl shadow-2xl w-full max-w-lg transform transition-all" data-aos="zoom-in">
                <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-t-xl px-6 py-4 flex items-center justify-between">
                    <h3 class="text-white font-bold text-lg">Upgrade Subscription</h3>
                    <button onclick="closeUpgradeModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 mb-3">Current Plan: <strong id="currentPlan"></strong></p>
                    <p class="text-gray-700 mb-3">Status: <strong id="currentStatus"></strong></p>
                    <p class="text-gray-700 mb-4">Current End Date: <strong id="subscriptionEnd"></strong></p>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Extend by (months)</label>
                        <input id="upgradeMonths" type="number" min="1" max="12" value="1" class="w-32 pl-3 pr-3 py-2 border border-gray-300 rounded-md">
                    </div>

                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Premium features you get</h4>
                        <ul class="list-disc pl-5 text-gray-700 text-sm space-y-1 mb-3">
                            <li><strong>Full CSV Export</strong> — server-side, full metadata export.</li>
                            <li><strong>Scheduled Exports</strong> — automated reports (coming soon).</li>
                            <li><strong>Advanced Reports</strong> — charts and analytics.</li>
                            <li><strong>Priority Support</strong> — faster help desk response.</li>
                        </ul>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button onclick="closeUpgradeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">Close</button>
                        <button onclick="performUpgrade()" class="px-4 py-2 rounded-md text-white bg-gradient-to-r from-yellow-400 to-yellow-600">Confirm Upgrade</button>
                    </div>
                    <hr class="my-4">
                    <div>
                        <?php if ($user['subscription_type'] === 'paid'): ?>
                        <button id="cancelSubscriptionBtn" onclick="performCancel()" class="w-full px-4 py-2 rounded-md text-white bg-red-500">Cancel / Downgrade to Free</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile editing UI removed per request -->
</body>
</html>
