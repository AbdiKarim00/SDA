<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("drivers", function (Blueprint $table) {
            $table->id();

            // User Relationship
            $table
                ->foreignId("user_id")
                ->constrained("users")
                ->onDelete("cascade");

            // Driver License Information
            $table->string("license_number", 50)->unique();
            $table->date("license_expiry");
            $table->string("license_class", 20)->nullable(); // A, B, C, etc.
            $table->date("license_issue_date")->nullable();
            $table->string("license_issuing_authority", 100)->nullable();

            // Personal Information
            $table->string("employee_id", 50)->nullable();
            $table->string("national_id", 50)->nullable();
            $table->string("phone", 20)->nullable();
            $table->string("emergency_contact_name", 100)->nullable();
            $table->string("emergency_contact_phone", 20)->nullable();
            $table->text("address")->nullable();

            // Employment Information
            $table->date("employment_start_date")->nullable();
            $table->date("employment_end_date")->nullable();
            $table->string("department", 100)->nullable();
            $table->string("supervisor", 100)->nullable();

            // Driver Status
            $table
                ->enum("status", [
                    "Available",
                    "On Trip",
                    "On Leave",
                    "Suspended",
                    "Terminated",
                ])
                ->default("Available");

            // Vehicle Assignment
            $table
                ->foreignId("assigned_vehicle_id")
                ->nullable()
                ->constrained("vehicles")
                ->onDelete("set null");
            $table->date("vehicle_assigned_date")->nullable();

            // Performance Tracking
            $table->integer("total_trips")->default(0);
            $table->decimal("total_distance", 10, 2)->default(0);
            $table->integer("incidents_count")->default(0);
            $table->decimal("fuel_efficiency_rating", 3, 2)->nullable(); // 1-5 rating
            $table->decimal("safety_rating", 3, 2)->nullable(); // 1-5 rating

            // Medical Information
            $table->date("medical_certificate_expiry")->nullable();
            $table->text("medical_conditions")->nullable();
            $table->text("medications")->nullable();

            // Training and Certifications
            $table->date("defensive_driving_certification")->nullable();
            $table->date("first_aid_certification")->nullable();
            $table->json("additional_certifications")->nullable();

            // Administrative
            $table->boolean("background_check_completed")->default(false);
            $table->date("background_check_date")->nullable();
            $table->boolean("drug_test_passed")->default(false);
            $table->date("drug_test_date")->nullable();

            // Notes and Comments
            $table->text("notes")->nullable();
            $table->text("performance_notes")->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index("license_number");
            $table->index("employee_id");
            $table->index("status");
            $table->index("license_expiry");
            $table->index(["user_id", "status"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("drivers");
    }
};
