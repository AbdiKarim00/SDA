<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transport IMS - API Documentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="bi bi-truck text-primary text-3xl mr-3"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Transport IMS</h1>
                        <p class="text-sm text-gray-600">Transport Information Management System</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="bi bi-check-circle mr-1"></i>
                        API Ready
                    </span>
                    <span class="text-sm text-gray-500">v1.0.0</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-primary to-green-600 rounded-lg shadow-lg p-8 text-white mb-8">
            <h2 class="text-3xl font-bold mb-4">Welcome to Transport IMS API</h2>
            <p class="text-lg mb-6">A comprehensive RESTful API for managing vehicles, drivers, trips, fuel cards, maintenance, and incidents.</p>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                    <i class="bi bi-car-front text-2xl mb-2"></i>
                    <h3 class="font-semibold">Vehicle Management</h3>
                    <p class="text-sm opacity-90">Complete fleet management with real-time tracking</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                    <i class="bi bi-person-badge text-2xl mb-2"></i>
                    <h3 class="font-semibold">Driver Portal</h3>
                    <p class="text-sm opacity-90">Driver profiles, compliance, and performance tracking</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                    <i class="bi bi-graph-up text-2xl mb-2"></i>
                    <h3 class="font-semibold">Analytics</h3>
                    <p class="text-sm opacity-90">Comprehensive reporting and dashboard analytics</p>
                </div>
            </div>
        </div>

        <!-- Quick Start -->
        <div class="grid lg:grid-cols-2 gap-8 mb-8">
            <!-- Authentication -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="bi bi-key text-primary mr-2"></i>
                    Authentication
                </h3>
                <p class="text-gray-600 mb-4">Use these credentials to test the API endpoints:</p>

                <div class="space-y-3">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="font-medium text-gray-900">Admin Access</div>
                        <div class="text-sm text-gray-600">Email: admin@transportims.com</div>
                        <div class="text-sm text-gray-600">Password: password123</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="font-medium text-gray-900">Logistics Manager</div>
                        <div class="text-sm text-gray-600">Email: logistics@transportims.com</div>
                        <div class="text-sm text-gray-600">Password: password123</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="font-medium text-gray-900">Driver</div>
                        <div class="text-sm text-gray-600">Email: james.wilson@transportims.com</div>
                        <div class="text-sm text-gray-600">Password: password123</div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <div class="text-sm text-blue-800">
                        <strong>API Base URL:</strong> {{ url('/api/v1') }}
                    </div>
                </div>
            </div>

            <!-- Sample Request -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="bi bi-code-slash text-primary mr-2"></i>
                    Sample Login Request
                </h3>
                <div class="bg-gray-900 rounded-lg p-4 text-sm text-gray-100 overflow-x-auto">
                    <pre><code>POST {{ url('/api/v1/login') }}
Content-Type: application/json

{
  "email": "admin@transportims.com",
  "password": "password123",
  "device_name": "Web Browser"
}</code></pre>
                </div>
                <div class="mt-4">
                    <h4 class="font-medium text-gray-900 mb-2">Response:</h4>
                    <div class="bg-green-50 rounded-lg p-3 text-sm">
                        <div class="text-green-800">
                            Returns: <code>{ "success": true, "data": { "user": {...}, "token": "..." } }</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Endpoints -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="bi bi-list-ul text-primary mr-2"></i>
                    API Endpoints
                </h3>
            </div>

            <div class="divide-y divide-gray-200">
                <!-- Authentication -->
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Authentication</h4>
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-3">POST</span>
                                <code class="text-sm">/api/v1/login</code>
                            </div>
                            <span class="text-sm text-gray-600">User login</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-3">POST</span>
                                <code class="text-sm">/api/v1/auth/logout</code>
                            </div>
                            <span class="text-sm text-gray-600">User logout</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/auth/profile</code>
                            </div>
                            <span class="text-sm text-gray-600">Get user profile</span>
                        </div>
                    </div>
                </div>

                <!-- Dashboard -->
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Dashboard</h4>
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/dashboard</code>
                            </div>
                            <span class="text-sm text-gray-600">Role-based dashboard data</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/dashboard/admin</code>
                            </div>
                            <span class="text-sm text-gray-600">Admin dashboard (Admin only)</span>
                        </div>
                    </div>
                </div>

                <!-- Vehicles -->
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Vehicle Management</h4>
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/vehicles</code>
                            </div>
                            <span class="text-sm text-gray-600">List all vehicles</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-3">POST</span>
                                <code class="text-sm">/api/v1/vehicles</code>
                            </div>
                            <span class="text-sm text-gray-600">Create new vehicle</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/vehicles/{id}</code>
                            </div>
                            <span class="text-sm text-gray-600">Get vehicle details</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-3">PUT</span>
                                <code class="text-sm">/api/v1/vehicles/{id}</code>
                            </div>
                            <span class="text-sm text-gray-600">Update vehicle</span>
                        </div>
                    </div>
                </div>

                <!-- Drivers -->
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Driver Management</h4>
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/drivers</code>
                            </div>
                            <span class="text-sm text-gray-600">List all drivers</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/drivers/available</code>
                            </div>
                            <span class="text-sm text-gray-600">Get available drivers</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/drivers/{id}/compliance</code>
                            </div>
                            <span class="text-sm text-gray-600">Driver compliance status</span>
                        </div>
                    </div>
                </div>

                <!-- Trips -->
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Trip Management</h4>
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/trips</code>
                            </div>
                            <span class="text-sm text-gray-600">List all trips</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-3">POST</span>
                                <code class="text-sm">/api/v1/trips</code>
                            </div>
                            <span class="text-sm text-gray-600">Create new trip</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/trips/active</code>
                            </div>
                            <span class="text-sm text-gray-600">Get active trips</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-3">POST</span>
                                <code class="text-sm">/api/v1/trips/{id}/approve</code>
                            </div>
                            <span class="text-sm text-gray-600">Approve trip</span>
                        </div>
                    </div>
                </div>

                <!-- Reports -->
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Reports & Analytics</h4>
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/reports/vehicle-utilization</code>
                            </div>
                            <span class="text-sm text-gray-600">Vehicle utilization report</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/reports/driver-performance</code>
                            </div>
                            <span class="text-sm text-gray-600">Driver performance report</span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">GET</span>
                                <code class="text-sm">/api/v1/reports/financial-summary</code>
                            </div>
                            <span class="text-sm text-gray-600">Financial summary report</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Getting Started</h3>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Step 1: Authenticate</h4>
                    <p class="text-sm text-gray-600 mb-4">Send a POST request to <code>/api/v1/login</code> with valid credentials to get an authentication token.</p>

                    <h4 class="font-medium text-gray-900 mb-2">Step 2: Include Token</h4>
                    <p class="text-sm text-gray-600">Include the token in the Authorization header: <code>Bearer YOUR_TOKEN</code></p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Step 3: Make Requests</h4>
                    <p class="text-sm text-gray-600 mb-4">Use the token to access protected endpoints based on your role permissions.</p>

                    <h4 class="font-medium text-gray-900 mb-2">Content Type</h4>
                    <p class="text-sm text-gray-600">All requests should include: <code>Content-Type: application/json</code></p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Transport IMS API v1.0.0 - {{ date('Y') }}
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="text-gray-400 hover:text-gray-600">
                        <i class="bi bi-github"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-600">
                        <i class="bi bi-book"></i>
                    </a>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="bi bi-server mr-1"></i>
                        {{ config('app.env') }}
                    </span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
