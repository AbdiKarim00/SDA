<?php
class DashboardSidebar {
    public static function render($activePage = 'dashboard') {
        $user = $_SESSION['user'] ?? ['name' => 'Admin', 'email' => 'admin@example.com'];
        ?>
        <aside class="dashboard-sidebar flex flex-col h-full" x-data="{ 
            open: false,
            activePage: '<?php echo $activePage; ?>',
            showPasswordModal: false,
            currentPassword: '',
            newPassword: '',
            confirmPassword: '',
            passwordError: '',
            passwordSuccess: '',
            profileOpen: false,
            isLoading: false,
            toggleProfile() {
                this.profileOpen = !this.profileOpen;
            },
            closeProfile() {
                this.profileOpen = false;
            },
            async changePassword() {
                this.passwordError = '';
                this.passwordSuccess = '';
                
                if (!this.currentPassword || !this.newPassword || !this.confirmPassword) {
                    this.passwordError = 'All fields are required';
                    return;
                }
                
                if (this.newPassword !== this.confirmPassword) {
                    this.passwordError = 'New passwords do not match';
                    return;
                }
                
                if (this.newPassword.length < 8) {
                    this.passwordError = 'Password must be at least 8 characters long';
                    return;
                }
                
                this.passwordSuccess = 'Password changed successfully';
                this.showPasswordModal = false;
                this.currentPassword = '';
                this.newPassword = '';
                this.confirmPassword = '';
            }
        }" x-init="document.addEventListener('click', (e) => {
            if (e.target.tagName === 'A' && e.target.href) {
                isLoading = true;
                setTimeout(() => isLoading = false, 200);
            }
        })">
            <!-- Loader -->
            <div x-show="isLoading" 
                 class="fixed inset-0 bg-emerald-50/30 z-50 flex items-center justify-center">
                <i class="bi bi-steering-wheel animate-[spin_1s_linear_infinite] text-4xl text-primary"></i>
            </div>

            <div class="sidebar-header">
                <img src="../assets/images/sda_logo.png" alt="Transport IMS Logo" class="sidebar-logo">
            </div>

            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Vehicle Management Section -->
                <a href="vehicles.php" class="sidebar-item <?php echo $activePage === 'vehicles' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-truck"></i>
                    <span>Vehicles</span>
                </a>
                <a href="vehicle-locations.php" class="sidebar-item <?php echo $activePage === 'vehicle-locations' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-geo-alt"></i>
                    <span>Vehicle Locations</span>
                </a>
                <a href="depreciation-calculator.php" class="sidebar-item <?php echo $activePage === 'depreciation-calculator' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-calculator"></i>
                    <span>Depreciation Calculator</span>
                </a>
                <a href="maintenance.php" class="sidebar-item <?php echo $activePage === 'maintenance' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-tools"></i>
                    <span>Maintenance</span>
                </a>
                <a href="disposal.php" class="sidebar-item <?php echo $activePage === 'disposal' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-trash"></i>
                    <span>Disposal Management</span>
                </a>

                <!-- Driver Management Section -->
                <a href="drivers.php" class="sidebar-item <?php echo $activePage === 'drivers' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-person"></i>
                    <span>Drivers</span>
                </a>
                <a href="assignment-history.php" class="sidebar-item <?php echo $activePage === 'assignment-history' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-clock-history"></i>
                    <span>Assignment History</span>
                </a>

                <!-- Safety & Compliance Section -->
                <a href="incidents.php" class="sidebar-item <?php echo $activePage === 'incidents' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Incident Reports</span>
                </a>

                <!-- Financial Management Section -->
                <a href="cards.php" class="sidebar-item <?php echo $activePage === 'cards' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-credit-card"></i>
                    <span>Fuel Cards</span>
                </a>

                <!-- System Management Section -->
                <a href="users.php" class="sidebar-item <?php echo $activePage === 'users' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-people"></i>
                    <span>User Management</span>
                </a>

                <!-- Reports Section -->
                <a href="reports.php" class="sidebar-item <?php echo $activePage === 'reports' ? 'active' : ''; ?>" @click="closeProfile()">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Reports</span>
                </a>
            </nav>

            <!-- Profile Section -->
            <div class="profile-section">
                <div class="profile-avatar" @click="toggleProfile()">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <p class="text-sm font-medium text-white"><?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <!-- Profile Dropup -->
                <div x-show="profileOpen" 
                     x-cloak
                     @click.away="profileOpen = false"
                     class="profile-dropup">
                    
                    <button @click="showPasswordModal = true; profileOpen = false" 
                            class="profile-dropup-item">
                        <i class="bi bi-key"></i>Change Password
                    </button>
                    <a href="../auth/logout.php" 
                       class="profile-dropup-item logout"
                       onclick="return confirm('Are you sure you want to logout?')">
                        <i class="bi bi-box-arrow-right"></i>Logout
                    </a>
                </div>
            </div>

            <!-- Password Change Modal -->
            <div x-show="showPasswordModal" 
                 x-cloak
                 class="modal-backdrop"
                 @click.away="showPasswordModal = false">
                <div class="modal-container">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">Change Password</h3>
                            <button class="modal-close" @click="showPasswordModal = false">
                                <i class="bi bi-x text-xl"></i>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form @submit.prevent="changePassword" class="space-y-4">
                                <div>
                                    <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                    <input type="password" 
                                           id="currentPassword" 
                                           x-model="currentPassword"
                                           class="form-input w-full"
                                           required>
                                </div>

                                <div>
                                    <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <input type="password" 
                                           id="newPassword" 
                                           x-model="newPassword"
                                           class="form-input w-full"
                                           required>
                                </div>

                                <div>
                                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                    <input type="password" 
                                           id="confirmPassword" 
                                           x-model="confirmPassword"
                                           class="form-input w-full"
                                           required>
                                </div>

                                <div x-show="passwordError" class="text-red-500 text-sm">
                                    <p x-text="passwordError"></p>
                                </div>

                                <div x-show="passwordSuccess" class="text-green-500 text-sm">
                                    <p x-text="passwordSuccess"></p>
                                </div>
                            </form>
                        </div>

                        <div class="modal-footer">
                            <button type="button" 
                                    @click="changePassword" 
                                    class="btn btn-primary">
                                Change Password
                            </button>
                            <button type="button" 
                                    @click="showPasswordModal = false" 
                                    class="btn btn-secondary">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        <?php
    }
} 