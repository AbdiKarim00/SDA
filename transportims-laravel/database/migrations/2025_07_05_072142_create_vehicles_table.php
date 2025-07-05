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
        Schema::create("vehicles", function (Blueprint $table) {
            $table->id();

            // Basic Vehicle Information
            $table->string("registration_no", 20)->unique();
            $table->string("make", 50);
            $table->string("model", 50);
            $table->string("chassis_no", 50)->nullable();
            $table->string("engine_no", 50)->nullable();
            $table->string("tag_number", 50)->nullable();
            $table->string("pv_number", 50)->nullable();

            // Vehicle Classification
            $table->string("vehicle_type", 50)->nullable(); // SUV, Truck, Van, Sedan, etc.
            $table->string("capacity", 50)->nullable(); // passengers or cargo capacity
            $table
                ->enum("fuel_type", ["Petrol", "Diesel", "Electric", "Hybrid"])
                ->default("Petrol");
            $table->integer("fuel_tank_capacity")->nullable(); // in liters

            // Purchase Information
            $table->date("date_of_purchase")->nullable();
            $table->integer("year_of_purchase")->nullable();
            $table->string("financed_by", 100)->nullable();
            $table->string("funded_by", 100)->nullable();
            $table->decimal("amount", 15, 2)->nullable();

            // Location Information
            $table->string("original_location", 100)->nullable();
            $table->string("current_location", 100)->nullable();

            // Operational Information
            $table->bigInteger("current_mileage")->default(0);
            $table
                ->enum("status", [
                    "Available",
                    "On Trip",
                    "In Maintenance",
                    "Decommissioned",
                ])
                ->default("Available");
            $table->string("responsible_officer", 100)->nullable();
            $table->string("asset_condition", 50)->nullable();
            $table->boolean("has_logbook")->default(false);

            // Insurance and Licensing
            $table->date("insurance_expiry")->nullable();
            $table->date("road_license_expiry")->nullable();
            $table->date("next_service_due")->nullable();

            // Financial Information
            $table->decimal("depreciation_rate", 5, 2)->nullable();
            $table->decimal("annual_depreciation", 15, 2)->nullable();
            $table->decimal("accumulated_depreciation", 15, 2)->nullable();
            $table->decimal("net_book_value", 15, 2)->nullable();

            // Disposal Information
            $table->date("replacement_date")->nullable();
            $table->date("disposal_date")->nullable();
            $table->decimal("disposal_value", 15, 2)->nullable();

            // Additional Information
            $table->text("notes")->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index("registration_no");
            $table->index("status");
            $table->index("current_location");
            $table->index(["make", "model"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("vehicles");
    }
};
