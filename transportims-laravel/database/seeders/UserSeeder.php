<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Driver;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            "name" => "System Administrator",
            "email" => "admin@transportims.com",
            "password" => Hash::make("password123"),
            "phone" => "+254712345678",
            "employee_id" => "EMP001",
            "department" => "IT Administration",
            "status" => "active",
            "email_verified_at" => now(),
        ]);
        $admin->assignRole("admin");

        // Create Logistics Manager
        $logistics = User::create([
            "name" => "Jane Smith",
            "email" => "logistics@transportims.com",
            "password" => Hash::make("password123"),
            "phone" => "+254712345679",
            "employee_id" => "EMP002",
            "department" => "Logistics",
            "status" => "active",
            "email_verified_at" => now(),
        ]);
        $logistics->assignRole("logistics");

        // Create Driver Users with Driver Profiles
        $drivers = [
            [
                "user" => [
                    "name" => "James Wilson",
                    "email" => "james.wilson@transportims.com",
                    "password" => Hash::make("password123"),
                    "phone" => "+254712345680",
                    "employee_id" => "DRV001",
                    "department" => "Operations",
                    "status" => "active",
                    "email_verified_at" => now(),
                ],
                "driver" => [
                    "license_number" => "DL123456789",
                    "license_expiry" => Carbon::now()->addYears(2),
                    "license_class" => "B",
                    "license_issue_date" => Carbon::now()->subYears(3),
                    "license_issuing_authority" => "NTSA Kenya",
                    "national_id" => "12345678",
                    "emergency_contact_name" => "Mary Wilson",
                    "emergency_contact_phone" => "+254712345681",
                    "address" => "123 Main Street, Nairobi",
                    "employment_start_date" => Carbon::now()->subYears(2),
                    "supervisor" => "Jane Smith",
                    "status" => "Available",
                    "medical_certificate_expiry" => Carbon::now()->addMonths(6),
                    "defensive_driving_certification" => Carbon::now()->subMonths(
                        6
                    ),
                    "first_aid_certification" => Carbon::now()->subMonths(3),
                    "background_check_completed" => true,
                    "background_check_date" => Carbon::now()->subYears(2),
                    "drug_test_passed" => true,
                    "drug_test_date" => Carbon::now()->subMonths(6),
                    "safety_rating" => 4.5,
                    "fuel_efficiency_rating" => 4.2,
                ],
            ],
            [
                "user" => [
                    "name" => "Robert Brown",
                    "email" => "robert.brown@transportims.com",
                    "password" => Hash::make("password123"),
                    "phone" => "+254712345682",
                    "employee_id" => "DRV002",
                    "department" => "Operations",
                    "status" => "active",
                    "email_verified_at" => now(),
                ],
                "driver" => [
                    "license_number" => "DL987654321",
                    "license_expiry" => Carbon::now()->addYears(3),
                    "license_class" => "C",
                    "license_issue_date" => Carbon::now()->subYears(5),
                    "license_issuing_authority" => "NTSA Kenya",
                    "national_id" => "87654321",
                    "emergency_contact_name" => "Alice Brown",
                    "emergency_contact_phone" => "+254712345683",
                    "address" => "456 Oak Avenue, Nairobi",
                    "employment_start_date" => Carbon::now()->subYears(3),
                    "supervisor" => "Jane Smith",
                    "status" => "Available",
                    "medical_certificate_expiry" => Carbon::now()->addMonths(8),
                    "defensive_driving_certification" => Carbon::now()->subMonths(
                        8
                    ),
                    "first_aid_certification" => Carbon::now()->subMonths(4),
                    "background_check_completed" => true,
                    "background_check_date" => Carbon::now()->subYears(3),
                    "drug_test_passed" => true,
                    "drug_test_date" => Carbon::now()->subMonths(3),
                    "safety_rating" => 4.8,
                    "fuel_efficiency_rating" => 4.6,
                ],
            ],
            [
                "user" => [
                    "name" => "Michael Johnson",
                    "email" => "michael.johnson@transportims.com",
                    "password" => Hash::make("password123"),
                    "phone" => "+254712345684",
                    "employee_id" => "DRV003",
                    "department" => "Operations",
                    "status" => "active",
                    "email_verified_at" => now(),
                ],
                "driver" => [
                    "license_number" => "DL456789123",
                    "license_expiry" => Carbon::now()->addMonths(8),
                    "license_class" => "B",
                    "license_issue_date" => Carbon::now()->subYears(4),
                    "license_issuing_authority" => "NTSA Kenya",
                    "national_id" => "45678912",
                    "emergency_contact_name" => "Sarah Johnson",
                    "emergency_contact_phone" => "+254712345685",
                    "address" => "789 Pine Road, Nairobi",
                    "employment_start_date" => Carbon::now()->subYears(1),
                    "supervisor" => "Jane Smith",
                    "status" => "Available",
                    "medical_certificate_expiry" => Carbon::now()->addMonths(4),
                    "defensive_driving_certification" => Carbon::now()->subMonths(
                        4
                    ),
                    "first_aid_certification" => Carbon::now()->subMonths(2),
                    "background_check_completed" => true,
                    "background_check_date" => Carbon::now()->subYears(1),
                    "drug_test_passed" => true,
                    "drug_test_date" => Carbon::now()->subMonths(2),
                    "safety_rating" => 4.0,
                    "fuel_efficiency_rating" => 3.8,
                ],
            ],
            [
                "user" => [
                    "name" => "David Miller",
                    "email" => "david.miller@transportims.com",
                    "password" => Hash::make("password123"),
                    "phone" => "+254712345686",
                    "employee_id" => "DRV004",
                    "department" => "Operations",
                    "status" => "active",
                    "email_verified_at" => now(),
                ],
                "driver" => [
                    "license_number" => "DL789123456",
                    "license_expiry" => Carbon::now()->addYears(1),
                    "license_class" => "C",
                    "license_issue_date" => Carbon::now()->subYears(6),
                    "license_issuing_authority" => "NTSA Kenya",
                    "national_id" => "78912345",
                    "emergency_contact_name" => "Emma Miller",
                    "emergency_contact_phone" => "+254712345687",
                    "address" => "321 Cedar Street, Nairobi",
                    "employment_start_date" => Carbon::now()->subYears(4),
                    "supervisor" => "Jane Smith",
                    "status" => "Available",
                    "medical_certificate_expiry" => Carbon::now()->addMonths(
                        10
                    ),
                    "defensive_driving_certification" => Carbon::now()->subMonths(
                        10
                    ),
                    "first_aid_certification" => Carbon::now()->subMonths(5),
                    "background_check_completed" => true,
                    "background_check_date" => Carbon::now()->subYears(4),
                    "drug_test_passed" => true,
                    "drug_test_date" => Carbon::now()->subMonths(4),
                    "safety_rating" => 4.3,
                    "fuel_efficiency_rating" => 4.1,
                ],
            ],
            [
                "user" => [
                    "name" => "Thomas Anderson",
                    "email" => "thomas.anderson@transportims.com",
                    "password" => Hash::make("password123"),
                    "phone" => "+254712345688",
                    "employee_id" => "DRV005",
                    "department" => "Operations",
                    "status" => "active",
                    "email_verified_at" => now(),
                ],
                "driver" => [
                    "license_number" => "DL321654987",
                    "license_expiry" => Carbon::now()->addYears(2),
                    "license_class" => "B",
                    "license_issue_date" => Carbon::now()->subYears(2),
                    "license_issuing_authority" => "NTSA Kenya",
                    "national_id" => "32165498",
                    "emergency_contact_name" => "Lisa Anderson",
                    "emergency_contact_phone" => "+254712345689",
                    "address" => "654 Elm Drive, Nairobi",
                    "employment_start_date" => Carbon::now()->subMonths(18),
                    "supervisor" => "Jane Smith",
                    "status" => "Available",
                    "medical_certificate_expiry" => Carbon::now()->addMonths(7),
                    "defensive_driving_certification" => Carbon::now()->subMonths(
                        7
                    ),
                    "first_aid_certification" => Carbon::now()->subMonths(3),
                    "background_check_completed" => true,
                    "background_check_date" => Carbon::now()->subMonths(18),
                    "drug_test_passed" => true,
                    "drug_test_date" => Carbon::now()->subMonths(1),
                    "safety_rating" => 4.7,
                    "fuel_efficiency_rating" => 4.4,
                ],
            ],
        ];

        // Create driver users and their profiles
        foreach ($drivers as $driverData) {
            $user = User::create($driverData["user"]);
            $user->assignRole("driver");

            // Create driver profile
            $driverProfile = $driverData["driver"];
            $driverProfile["user_id"] = $user->id;
            Driver::create($driverProfile);
        }

        // Create Additional Logistics Users
        $logistics2 = User::create([
            "name" => "Mark Thompson",
            "email" => "mark.thompson@transportims.com",
            "password" => Hash::make("password123"),
            "phone" => "+254712345690",
            "employee_id" => "EMP003",
            "department" => "Logistics",
            "status" => "active",
            "email_verified_at" => now(),
        ]);
        $logistics2->assignRole("logistics");

        $logistics3 = User::create([
            "name" => "Sarah Davis",
            "email" => "sarah.davis@transportims.com",
            "password" => Hash::make("password123"),
            "phone" => "+254712345691",
            "employee_id" => "EMP004",
            "department" => "Fleet Management",
            "status" => "active",
            "email_verified_at" => now(),
        ]);
        $logistics3->assignRole("logistics");

        $this->command->info("Users created successfully!");
        $this->command->info("Admin: admin@transportims.com / password123");
        $this->command->info(
            "Logistics: logistics@transportims.com / password123"
        );
        $this->command->info(
            "Sample Driver: james.wilson@transportims.com / password123"
        );
        $this->command->info("Total users created: " . User::count());
        $this->command->info("Total drivers created: " . Driver::count());
    }
}
