<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Inventory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
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

        <!-- Registration Card -->
        <div class="max-w-md w-full space-y-8 p-8 glass-effect rounded-xl shadow-2xl animate-fadeIn" data-aos="fade-up" data-aos-delay="200">
            <div>
                <h2 class="text-center text-3xl font-extrabold text-gray-900">
                    Create your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Join us to manage your inventory efficiently
                </p>
            </div>
            <form class="mt-8 space-y-6" id="registerForm">
                <div class="space-y-4">
                    <div class="relative">
                        <label for="username" class="text-sm font-medium text-gray-700">Username</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input id="username" name="username" type="text" required 
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-150 ease-in-out sm:text-sm" 
                                placeholder="Choose a username">
                        </div>
                    </div>

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

                    <div class="relative">
                        <label for="confirm_password" class="text-sm font-medium text-gray-700">Confirm Password</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition duration-150 ease-in-out sm:text-sm" 
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-150 ease-in-out transform hover:scale-[1.02]">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-user-plus text-purple-300 group-hover:text-purple-200"></i>
                        </span>
                        Create Account
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="mt-6 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Already have an account?</span>
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="index.php" 
                    class="w-full inline-flex justify-center py-3 px-4 border border-purple-300 rounded-md shadow-sm bg-white text-sm font-medium text-purple-600 hover:bg-purple-50 transition duration-150 ease-in-out">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign in to your account
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

        // Form submission handling
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Password validation
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            const formData = new FormData(this);
            
            // Debug: Log form data before sending
            console.log('Form data being sent:', {
                username: formData.get('username'),
                email: formData.get('email'),
                password: 'REDACTED'
            });

            // Create an object with the form data for debugging
            const formDataObj = {};
            formData.forEach((value, key) => {
                formDataObj[key] = key === 'password' ? '***' : value;
            });
            console.log('Sending registration data:', formDataObj);

            fetch('../backend/controllers/auth_endpoints.php?action=register', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text().then(text => {
                    console.log('Raw server response:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed response:', data);
                        return data;
                    } catch (e) {
                        console.error('Failed to parse server response:', text);
                        throw new Error('Server returned invalid response: ' + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                console.log('Server response:', data);
                if (data.success) {
                    alert('Registration successful! Please login.');
                    window.location.href = 'index.php';
                } else {
                    alert(data.message || 'Registration failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                // Show more detailed error message
                const errorMessage = error.message || 'Unknown error occurred';
                console.log('Full error details:', {
                    message: errorMessage,
                    stack: error.stack
                });
                alert('Registration failed: ' + errorMessage + '\nPlease check the browser console for more details.');
            });
        });
    </script>
</body>
</html>
