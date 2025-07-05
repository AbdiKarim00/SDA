<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info("🚀 Starting Transport IMS database seeding...");

        // Run seeders in order
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            VehicleSeeder::class,
        ]);

        $this->command->info("");
        $this->command->line("✅ Database seeding completed successfully!");
        $this->command->info("");
        $this->command->info("🔐 Default Login Credentials:");
        $this->command->info(
            "┌─────────────────────────────────────────────────────┐"
        );
        $this->command->info(
            "│ Admin:     admin@transportims.com / password123     │"
        );
        $this->command->info(
            "│ Logistics: logistics@transportims.com / password123 │"
        );
        $this->command->info(
            "│ Driver:    james.wilson@transportims.com / password123 │"
        );
        $this->command->info(
            "└─────────────────────────────────────────────────────┘"
        );
        $this->command->info("");
        $this->command->warn(
            "⚠️  Please change default passwords in production!"
        );
        $this->command->info("");

        // Display summary statistics
        $this->displaySummary();
    }

    /**
     * Display a summary of seeded data.
     */
    private function displaySummary(): void
    {
        $this->command->info("📊 Seeded Data Summary:");
        $this->command->info("┌──────────────────────────────────────────┐");

        // Count roles
        $rolesCount = \Spatie\Permission\Models\Role::count();
        $this->command->info(
            "│ Roles: {$rolesCount} (admin, logistics, driver)     │"
        );

        // Count permissions
        $permissionsCount = \Spatie\Permission\Models\Permission::count();
        $this->command->info(
            "│ Permissions: {$permissionsCount}                        │"
        );

        // Count users
        $usersCount = \App\Models\User::count();
        $this->command->info(
            "│ Users: {$usersCount}                                │"
        );

        // Count drivers
        $driversCount = \App\Models\Driver::count();
        $this->command->info(
            "│ Drivers: {$driversCount}                              │"
        );

        // Count vehicles
        $vehiclesCount = \App\Models\Vehicle::count();
        $this->command->info(
            "│ Vehicles: {$vehiclesCount}                            │"
        );

        $this->command->info("└──────────────────────────────────────────┘");
        $this->command->info("");

        // Vehicle status breakdown
        $availableVehicles = \App\Models\Vehicle::where(
            "status",
            "Available"
        )->count();
        $onTripVehicles = \App\Models\Vehicle::where(
            "status",
            "On Trip"
        )->count();
        $maintenanceVehicles = \App\Models\Vehicle::where(
            "status",
            "In Maintenance"
        )->count();

        $this->command->info("🚗 Vehicle Status Breakdown:");
        $this->command->info("   • Available: {$availableVehicles}");
        $this->command->info("   • On Trip: {$onTripVehicles}");
        $this->command->info("   • In Maintenance: {$maintenanceVehicles}");
        $this->command->info("");

        // Driver status breakdown
        $availableDrivers = \App\Models\Driver::where(
            "status",
            "Available"
        )->count();

        $this->command->info("👨‍💼 Driver Status:");
        $this->command->info("   • Available: {$availableDrivers}");
        $this->command->info("");

        $this->command->info("🎯 Next Steps:");
        $this->command->info("   1. Run: php artisan serve");
        $this->command->info("   2. Visit: http://localhost:8000");
        $this->command->info("   3. Login with any of the credentials above");
        $this->command->info("   4. Explore the Transport IMS dashboard!");
        $this->command->info("");
    }
}
