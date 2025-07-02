<?php
require_once '../components/layout/DashboardHeader.php';
require_once '../components/layout/DashboardSidebar.php';
require_once '../components/common/ReportHeader.php';
require_once '../components/common/KpiCard.php';
require_once '../components/common/ChartCard.php';

// Mock user data
$users = [
    [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'personal_number' => 'EMP001',
        'role' => 'admin',
        'status' => 'active',
        'is_online' => true,
        'last_login' => '2024-03-15 14:30:00',
        'created_at' => '2024-01-01',
        'department' => 'IT',
        'phone' => '+254 712 345 678'
    ],
    [
        'id' => 2,
        'name' => 'Jane Smith',
        'email' => 'jane.smith@example.com',
        'personal_number' => 'EMP002',
        'role' => 'driver',
        'status' => 'active',
        'is_online' => false,
        'last_login' => '2024-03-14 09:15:00',
        'created_at' => '2024-01-15',
        'department' => 'Transport',
        'phone' => '+254 723 456 789'
    ],
    [
        'id' => 3,
        'name' => 'Mike Johnson',
        'email' => 'mike.johnson@example.com',
        'personal_number' => 'EMP003',
        'role' => 'logistics',
        'status' => 'inactive',
        'is_online' => false,
        'last_login' => '2024-03-10 16:45:00',
        'created_at' => '2024-02-01',
        'department' => 'Logistics',
        'phone' => '+254 734 567 890'
    ],
    // Add more mock users as needed
];

// Mock roles and permissions
$roles = [
    'admin' => [
        'name' => 'Administrator',
        'permissions' => ['all']
    ],
    'driver' => [
        'name' => 'Driver',
        'permissions' => ['view_trips', 'update_trips', 'view_vehicle']
    ],
    'logistics' => [
        'name' => 'Logistics Officer',
        'permissions' => ['manage_trips', 'manage_vehicles', 'view_reports']
    ]
];

// Mock departments
$departments = ['IT', 'Transport', 'Logistics', 'HR', 'Finance'];

// User metrics
$userMetrics = [
    'total_users' => count($users),
    'active_users' => count(array_filter($users, fn($u) => $u['status'] === 'active')),
    'online_users' => count(array_filter($users, fn($u) => $u['is_online'])),
    'inactive_users' => count(array_filter($users, fn($u) => $u['status'] === 'inactive'))
];

// Department distribution
$departmentDistribution = array_count_values(array_column($users, 'department'));

// Add deactivated users data
$deactivatedUsers = [
    [
        'id' => 4,
        'name' => 'Sarah Wilson',
        'email' => 'sarah.wilson@example.com',
        'personal_number' => 'EMP004',
        'role' => 'driver',
        'status' => 'deactivated',
        'is_online' => false,
        'last_login' => '2024-02-15 10:30:00',
        'created_at' => '2024-01-20',
        'deactivated_at' => '2024-03-01',
        'department' => 'Transport',
        'phone' => '+254 745 678 901'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #10B981;
            color: white;
        }

        .btn-primary:hover {
            background-color: #059669;
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .status-badge {
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
        }

        .status-badge.active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-badge.inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .role-badge {
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
            background-color: #e0e7ff;
            color: #4338ca;
        }

        .online-badge {
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
        }

        .online-badge.online {
            background-color: #dcfce7;
            color: #166534;
        }

        .online-badge.offline {
            background-color: #e5e7eb;
            color: #4b5563;
        }

        .deactivate-badge {
            background-color: #fef3c7;
            color: #92400e;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-content {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 32rem;
            width: 100%;
            padding: 1.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #10B981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.1);
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
        }

        .form-select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            background-color: white;
        }

        .form-select:focus {
            outline: none;
            border-color: #10B981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="app-container" x-data="{ 
        sidebarOpen: true,
        isLoading: true,
        activeTab: 'overview',
        showAddUserModal: false,
        showEditUserModal: false,
        showDeactivateUserModal: false,
        showReactivateUserModal: false,
        showDeactivatedUsers: false,
        selectedUser: null,
        searchQuery: '',
        selectedRole: '',
        selectedDepartment: '',
        selectedStatus: '',
        sortBy: 'name',
        sortDirection: 'asc'
    }" x-init="setTimeout(() => isLoading = false, 500)">
        <?php DashboardSidebar::render('users'); ?>

        <div class="main-content">
            <?php DashboardHeader::render(); ?>

            <?php ReportHeader::render(
                'User Management',
                'Manage system users, roles, and permissions'
            ); ?>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>

            <!-- Content -->
            <div x-show="!isLoading">
                <!-- Tab Navigation -->
                <div class="mb-6 border-b border-gray-200">
                    <nav class="flex space-x-8">
                        <button @click="activeTab = 'overview'" 
                                :class="{'border-primary text-primary': activeTab === 'overview'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Overview
                        </button>
                        <button @click="activeTab = 'users'" 
                                :class="{'border-primary text-primary': activeTab === 'users'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Users
                        </button>
                        <button @click="activeTab = 'roles'" 
                                :class="{'border-primary text-primary': activeTab === 'roles'}"
                                class="px-1 py-4 border-b-2 font-medium text-sm">
                            Roles & Permissions
                        </button>
                    </nav>
                </div>

                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'">
                    <!-- User Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <?php
                        KpiCard::render('Total Users', $userMetrics['total_users'], 'people');
                        KpiCard::render('Active Users', $userMetrics['active_users'], 'person-check');
                        KpiCard::render('Online Users', $userMetrics['online_users'], 'person-video');
                        KpiCard::render('Inactive Users', $userMetrics['inactive_users'], 'person-x');
                        ?>
                    </div>

                    <!-- Department Distribution -->
                    <?php
                    ChartCard::render(
                        'Department Distribution',
                        'departmentDistribution',
                        'donut',
                        [
                            'series' => array_values($departmentDistribution)
                        ],
                        [
                            'labels' => array_keys($departmentDistribution),
                            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                            'legend' => ['position' => 'bottom'],
                            'dataLabels' => [
                                'enabled' => true,
                                'formatter' => 'function(val) { return val.toFixed(0) + "%"; }'
                            ]
                        ]
                    );
                    ?>
                </div>

                <!-- Users Tab -->
                <div x-show="activeTab === 'users'">
                    <!-- Filters -->
                    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="form-label">Search</label>
                                <input type="text" 
                                       x-model="searchQuery" 
                                       placeholder="Search users..." 
                                       class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Role</label>
                                <select x-model="selectedRole" class="form-select">
                                    <option value="">All Roles</option>
                                    <?php foreach ($roles as $key => $role): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $role['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Department</label>
                                <select x-model="selectedDepartment" class="form-select">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select x-model="selectedStatus" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">User List</h3>
                            <div class="flex items-center gap-2">
                                <button @click="showDeactivatedUsers = !showDeactivatedUsers" class="btn btn-secondary">
                                    <i class="bi bi-archive mr-2"></i>
                                    Deactivated
                                </button>
                                <button @click="showAddUserModal = true" class="btn btn-primary">
                                    <i class="bi bi-plus-lg mr-2"></i>
                                    Add User
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                            @click="sortBy = 'name'; sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'">
                                            Name
                                            <i class="bi" :class="sortBy === 'name' ? (sortDirection === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : ''"></i>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personal Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Online Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                            <i class="bi bi-person text-gray-500"></i>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($user['name']); ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?php echo htmlspecialchars($user['email']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($user['personal_number']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="role-badge">
                                                    <?php echo htmlspecialchars($roles[$user['role']]['name']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($user['department']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="status-badge <?php echo $user['status']; ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="online-badge <?php echo $user['is_online'] ? 'online' : 'offline'; ?>">
                                                    <?php echo $user['is_online'] ? 'Online' : 'Offline'; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y H:i', strtotime($user['last_login'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button @click="selectedUser = <?php echo htmlspecialchars(json_encode($user)); ?>; showEditUserModal = true" 
                                                        class="text-emerald-600 hover:text-emerald-900 mr-3">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button @click="selectedUser = <?php echo htmlspecialchars(json_encode($user)); ?>; showDeactivateUserModal = true"
                                                        class="text-amber-600 hover:text-amber-900">
                                                    <i class="bi bi-person-x"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Deactivated Users Table -->
                    <div x-show="showDeactivatedUsers" class="mt-6 bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Deactivated Users</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personal Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deactivated On</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Remaining</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($deactivatedUsers as $user): 
                                        $deactivatedDate = new DateTime($user['deactivated_at']);
                                        $now = new DateTime();
                                        $daysRemaining = 90 - $deactivatedDate->diff($now)->days;
                                    ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                            <i class="bi bi-person text-gray-500"></i>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($user['name']); ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?php echo htmlspecialchars($user['email']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($user['personal_number']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="role-badge">
                                                    <?php echo htmlspecialchars($roles[$user['role']]['name']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($user['department']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($user['deactivated_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="status-badge <?php echo $daysRemaining <= 7 ? 'inactive' : 'active'; ?>">
                                                    <?php echo $daysRemaining; ?> days
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button @click="selectedUser = <?php echo htmlspecialchars(json_encode($user)); ?>; showReactivateUserModal = true"
                                                        class="text-emerald-600 hover:text-emerald-900">
                                                    <i class="bi bi-person-check"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Roles Tab -->
                <div x-show="activeTab === 'roles'">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <?php foreach ($roles as $key => $role): ?>
                            <div class="bg-white rounded-lg shadow-sm p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($role['name']); ?></h3>
                                        <p class="text-sm text-gray-500">Role ID: <?php echo htmlspecialchars($key); ?></p>
                                    </div>
                                    <button class="btn btn-secondary btn-sm">
                                        <i class="bi bi-pencil mr-2"></i>
                                        Edit Role
                                    </button>
                                </div>
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900">Permissions</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <?php foreach ($role['permissions'] as $permission): ?>
                                            <div class="flex items-center">
                                                <i class="bi bi-check-circle-fill text-emerald-500 mr-2"></i>
                                                <span class="text-sm text-gray-600"><?php echo htmlspecialchars($permission); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add User Modal -->
        <div x-show="showAddUserModal" class="modal-overlay" x-cloak>
            <div class="modal-content">
                <h3 class="text-lg font-semibold mb-4">Add New User</h3>
                <form class="space-y-4">
                    <div>
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Personal Number</label>
                        <input type="text" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Department</label>
                        <select class="form-select" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select class="form-select" required>
                            <?php foreach ($roles as $key => $role): ?>
                                <option value="<?php echo $key; ?>"><?php echo $role['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showAddUserModal = false" class="btn btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div x-show="showEditUserModal" class="modal-overlay" x-cloak>
            <div class="modal-content">
                <h3 class="text-lg font-semibold mb-4">Edit User</h3>
                <form class="space-y-4" x-data="{ user: selectedUser }">
                    <div>
                        <label class="form-label">Full Name</label>
                        <input type="text" x-model="user.name" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Personal Number</label>
                        <input type="text" x-model="user.personal_number" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" x-model="user.email" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="tel" x-model="user.phone" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Department</label>
                        <select x-model="user.department" class="form-select" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select x-model="user.role" class="form-select" required>
                            <?php foreach ($roles as $key => $role): ?>
                                <option value="<?php echo $key; ?>"><?php echo $role['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select x-model="user.status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showEditUserModal = false" class="btn btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deactivate User Modal -->
        <div x-show="showDeactivateUserModal" class="modal-overlay" x-cloak>
            <div class="modal-content">
                <h3 class="text-lg font-semibold mb-4">Deactivate User</h3>
                <p class="text-gray-600 mb-4">
                    This user's account will be deactivated for 90 days before being permanently deleted. 
                    During this period, they will not be able to access the system.
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="showDeactivateUserModal = false" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button class="btn btn-danger">
                        Deactivate User
                    </button>
                </div>
            </div>
        </div>

        <!-- Reactivate User Modal -->
        <div x-show="showReactivateUserModal" class="modal-overlay" x-cloak>
            <div class="modal-content">
                <h3 class="text-lg font-semibold mb-4">Reactivate User</h3>
                <p class="text-gray-600 mb-4">
                    Are you sure you want to reactivate this user's account? They will regain access to the system immediately.
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="showReactivateUserModal = false" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button class="btn btn-primary">
                        Reactivate User
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 