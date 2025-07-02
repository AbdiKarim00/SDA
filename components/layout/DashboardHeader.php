<?php
class DashboardHeader {
    public static function render($title = 'Dashboard') {
        ?>
        <header class="dashboard-header">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden">
                    <i class="bi bi-list text-2xl"></i>
                </button>
                <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($title); ?></h1>
            </div>
            <div class="header-actions">
                <!-- Search -->
                <div class="search-container" x-data="{ 
                    query: '',
                    results: [],
                    showResults: false,
                    isLoading: false,
                    async search() {
                        if (this.query.length < 2) {
                            this.results = [];
                            return;
                        }
                        this.isLoading = true;
                        try {
                            const response = await fetch(`../api/search.php?q=${encodeURIComponent(this.query)}`);
                            const data = await response.json();
                            this.results = data;
                            this.showResults = true;
                        } catch (error) {
                            console.error('Search failed:', error);
                            this.results = [];
                        } finally {
                            this.isLoading = false;
                        }
                    }
                }">
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="search" 
                               class="search-input pl-10" 
                               placeholder="Search vehicles, drivers, maintenance..."
                               x-model="query"
                               @input.debounce.300ms="search()"
                               @focus="showResults = true"
                               @click.away="showResults = false">
                        <div x-show="isLoading" 
                             class="fixed inset-0 bg-emerald-50/30 z-50 flex items-center justify-center">
                            <i class="bi bi-steering-wheel animate-[spin_1s_linear_infinite] text-4xl text-primary"></i>
                        </div>
                    </div>
                    
                    <!-- Search Results -->
                    <div class="absolute top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg z-50"
                         x-show="showResults"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95">
                        <template x-if="results.length === 0">
                            <div class="px-4 py-2 text-gray-500">No results found</div>
                        </template>
                        <template x-for="result in results" :key="result.id">
                            <a :href="result.url" class="block px-4 py-2 hover:bg-gray-100">
                                <div class="flex items-center gap-2">
                                    <i :class="result.icon" class="text-gray-500 text-lg"></i>
                                    <div>
                                        <div x-text="result.title" class="font-medium"></div>
                                        <div x-text="result.subtitle" class="text-sm text-gray-500"></div>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>

                <!-- Logout Button -->
                <a href="../auth/logout.php" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
                   onclick="return confirm('Are you sure you want to logout?')">
                    <i class="bi bi-box-arrow-right mr-2"></i>
                    Logout
                </a>
            </div>
        </header>

        <!-- Profile Dropup -->
        <div x-show="showProfileDropup" 
             @click.away="showProfileDropup = false"
             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
            <!-- Dropup content -->
        </div>
        <?php
    }
} 