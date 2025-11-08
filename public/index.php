<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        .gradient-background {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body class="gradient-background">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <!-- Logo and Name -->
        <div class="mb-8 text-center animate-fadeIn" data-aos="fade-down">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-boxes text-white text-5xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Inventory Management System</h1>
            <p class="text-gray-200">Project Web Application Development</p>
        </div>

        <!-- Login Card -->
        <div class="max-w-md w-full space-y-8 p-8 glass-effect rounded-xl shadow-2xl animate-fadeIn" data-aos="fade-up" data-aos-delay="200">
            <div>
                <h2 class="text-center text-3xl font-extrabold text-gray-900">
                    Welcome Back
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Sign in to manage your inventory
                </p>
            </div>
            <form class="mt-8 space-y-6" action="#" method="POST" id="loginForm">
                <div class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div class="relative">
                            <label for="email" class="text-sm font-medium text-gray-700">Email address</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input id="email" name="email" type="email" required 
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-150 ease-in-out sm:text-sm" 
                                    placeholder="your@email.com">
                            </div>
                        </div>
                        <div class="relative">
                            <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="password" name="password" type="password" required 
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-150 ease-in-out sm:text-sm" 
                                    placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-150 ease-in-out transform hover:scale-[1.02]">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-sign-in-alt text-purple-300 group-hover:text-purple-200"></i>
                            </span>
                            Sign in
                        </button>
                    </div>
                </div>
            </form>

            <!-- Divider -->
            <div class="mt-6 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">New to Inventory Management System?</span>
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="register.php" 
                    class="w-full inline-flex justify-center py-3 px-4 border border-purple-300 rounded-md shadow-sm bg-white text-sm font-medium text-purple-600 hover:bg-purple-50 transition duration-150 ease-in-out">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create an account
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-200" data-aos="fade-up" data-aos-delay="400">
            <p>© 2025 Inventory Management System. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../backend/controllers/auth_endpoints.php?action=login', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    alert(data.message || 'Login failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html>
