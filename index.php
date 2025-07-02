<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Transport IMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-4xl w-full mx-4">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="flex flex-col md:flex-row">
                    <!-- Left Panel - Welcome Box -->
                    <div class="md:w-2/5 bg-gradient-to-br from-blue-900 to-blue-700 p-8 text-white">
                        <div class="text-4xl font-bold mb-6">
                            <i class="bi bi-truck"></i> SDATIMS
                        </div>
                        <h1 class="text-2xl font-bold mb-4">Welcome to SDATIMS</h1>
                        <p class="text-blue-100 mb-8">"Empowering Smart Fleet and Personnel Control"</p>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <i class="bi bi-shield-check text-xl mr-3"></i>
                                <span>Secure Access</span>
                            </div>
                            <div class="flex items-center">
                                <i class="bi bi-lightning-charge text-xl mr-3"></i>
                                <span>Fast Navigation</span>
                            </div>
                            <div class="flex items-center">
                                <i class="bi bi-clock text-xl mr-3"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Panel - Role Selection -->
                    <div class="md:w-3/5 p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Select Your Role</h2>
                        <p class="text-gray-600 mb-6">Choose your dashboard to continue</p>
                        
                        <div class="space-y-4">
                            <a href="admin/dashboard.php" class="block w-full">
                                <button class="w-full flex items-center justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="bi bi-shield-lock mr-2"></i>
                                    Admin Dashboard
                                </button>
                            </a>
                            
                            <a href="logistics/dashboard.php" class="block w-full">
                                <button class="w-full flex items-center justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="bi bi-box-seam mr-2"></i>
                                    Logistics Dashboard
                                </button>
                            </a>
                            
                            <a href="driver/index.php" class="block w-full">
                                <button class="w-full flex items-center justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <i class="bi bi-person-badge mr-2"></i>
                                    Driver Dashboard
                                </button>
                            </a>
                        </div>
                        
                        <div class="mt-6 text-center text-sm text-gray-600">
                            <i class="bi bi-info-circle mr-1"></i>
                            <a href="#" class="text-blue-600 hover:text-blue-800">
                                Need help? Contact support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>pp
    </div>
</body>
</html> 
