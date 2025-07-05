<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[
            \Spatie\Permission\PermissionRegistrar::class
        ]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            "users.view",
            "users.create",
            "users.edit",
            "users.delete",
            "users.assign-roles",

            // Vehicle Management
            "vehicles.view",
            "vehicles.create",
            "vehicles.edit",
            "vehicles.delete",
            "vehicles.assign-driver",
            "vehicles.update-status",
            "vehicles.update-mileage",

            // Driver Management
            "drivers.view",
            "drivers.create",
            "drivers.edit",
            "drivers.delete",
            "drivers.assign-vehicle",
            "drivers.update-status",
            "drivers.update-certifications",

            // Trip Management
            "trips.view",
            "trips.create",
            "trips.edit",
            "trips.delete",
            "trips.approve",
            "trips.reject",
            "trips.assign-driver",
            "trips.assign-vehicle",
            "trips.start",
            "trips.complete",
            "trips.cancel",
            "trips.update-location",

            // Fuel Card Management
            "fuel-cards.view",
            "fuel-cards.create",
            "fuel-cards.edit",
            "fuel-cards.delete",
            "fuel-cards.assign",
            "fuel-cards.update-balance",
            "fuel-cards.update-limits",
            "fuel-cards.block",
            "fuel-cards.unblock",

            // Maintenance Management
            "maintenance.view",
            "maintenance.create",
            "maintenance.edit",
            "maintenance.delete",
            "maintenance.approve",
            "maintenance.reject",
            "maintenance.start",
            "maintenance.complete",
            "maintenance.update-cost",

            // Incident Management
            "incidents.view",
            "incidents.create",
            "incidents.edit",
            "incidents.delete",
            "incidents.assign-investigator",
            "incidents.update-status",
            "incidents.resolve",
            "incidents.close",

            // Reporting & Analytics
            "reports.view",
            "reports.vehicle-utilization",
            "reports.driver-performance",
            "reports.trip-analysis",
            "reports.fuel-consumption",
            "reports.maintenance-costs",
            "reports.incident-analysis",
            "reports.compliance-status",
            "reports.financial-summary",
            "reports.export",

            // Dashboard Access
            "dashboard.admin",
            "dashboard.logistics",
            "dashboard.driver",

            // System Settings
            "settings.view",
            "settings.edit",
            "settings.backup",
            "settings.logs",
            "settings.cache-clear",

            // Profile Management
            "profile.view",
            "profile.edit",
            "profile.change-password",

            // File Management
            "files.upload",
            "files.download",
            "files.delete",

            // Notifications
            "notifications.view",
            "notifications.send",
            "notifications.manage",
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::create(["name" => $permission, "guard_name" => "web"]);
        }

        // Create roles and assign permissions

        // Admin Role - Full access
        $adminRole = Role::create(["name" => "admin", "guard_name" => "web"]);
        $adminRole->givePermissionTo(Permission::all());

        // Logistics Role - Vehicle, driver, trip, and maintenance management
        $logisticsRole = Role::create([
            "name" => "logistics",
            "guard_name" => "web",
        ]);
        $logisticsRole->givePermissionTo([
            // Vehicle Management
            "vehicles.view",
            "vehicles.create",
            "vehicles.edit",
            "vehicles.assign-driver",
            "vehicles.update-status",
            "vehicles.update-mileage",

            // Driver Management
            "drivers.view",
            "drivers.create",
            "drivers.edit",
            "drivers.assign-vehicle",
            "drivers.update-status",
            "drivers.update-certifications",

            // Trip Management
            "trips.view",
            "trips.create",
            "trips.edit",
            "trips.approve",
            "trips.reject",
            "trips.assign-driver",
            "trips.assign-vehicle",
            "trips.start",
            "trips.complete",
            "trips.cancel",

            // Fuel Card Management
            "fuel-cards.view",
            "fuel-cards.create",
            "fuel-cards.edit",
            "fuel-cards.assign",
            "fuel-cards.update-balance",
            "fuel-cards.update-limits",
            "fuel-cards.block",
            "fuel-cards.unblock",

            // Maintenance Management
            "maintenance.view",
            "maintenance.create",
            "maintenance.edit",
            "maintenance.approve",
            "maintenance.reject",
            "maintenance.start",
            "maintenance.complete",
            "maintenance.update-cost",

            // Incident Management
            "incidents.view",
            "incidents.create",
            "incidents.edit",
            "incidents.assign-investigator",
            "incidents.update-status",
            "incidents.resolve",
            "incidents.close",

            // Reporting
            "reports.view",
            "reports.vehicle-utilization",
            "reports.driver-performance",
            "reports.trip-analysis",
            "reports.fuel-consumption",
            "reports.maintenance-costs",
            "reports.incident-analysis",
            "reports.compliance-status",
            "reports.financial-summary",
            "reports.export",

            // Dashboard
            "dashboard.logistics",

            // Profile
            "profile.view",
            "profile.edit",
            "profile.change-password",

            // Files
            "files.upload",
            "files.download",
            "files.delete",

            // Notifications
            "notifications.view",
            "notifications.send",
        ]);

        // Driver Role - Limited access to personal data and trip updates
        $driverRole = Role::create(["name" => "driver", "guard_name" => "web"]);
        $driverRole->givePermissionTo([
            // Vehicle Management (view only assigned vehicle)
            "vehicles.view",
            "vehicles.update-mileage",

            // Trip Management (own trips only)
            "trips.view",
            "trips.create",
            "trips.start",
            "trips.complete",
            "trips.update-location",

            // Fuel Cards (view own card)
            "fuel-cards.view",

            // Maintenance (request and view)
            "maintenance.view",
            "maintenance.create",

            // Incidents (report and view own)
            "incidents.view",
            "incidents.create",

            // Dashboard
            "dashboard.driver",

            // Profile
            "profile.view",
            "profile.edit",
            "profile.change-password",

            // Files
            "files.upload",
            "files.download",

            // Notifications
            "notifications.view",
        ]);

        $this->command->info("Roles and permissions created successfully!");
        $this->command->info("Created roles: admin, logistics, driver");
        $this->command->info("Created " . count($permissions) . " permissions");
    }
}
