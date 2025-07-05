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
        Schema::create("fuel_cards", function (Blueprint $table) {
            $table->id();

            // Card Identification
            $table->string("card_number", 50)->unique();
            $table->string("card_type", 30)->default("Standard"); // Standard, Premium, Fleet
            $table->string("provider", 50)->nullable(); // Shell, Total, Petron, etc.
            $table->string("pin_code", 10)->nullable();

            // Relationships
            $table
                ->foreignId("driver_id")
                ->nullable()
                ->constrained("drivers")
                ->onDelete("set null");
            $table
                ->foreignId("vehicle_id")
                ->nullable()
                ->constrained("vehicles")
                ->onDelete("set null");
            $table
                ->foreignId("assigned_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");

            // Balance and Limits
            $table->decimal("current_balance", 10, 2)->default(0); // in currency
            $table->decimal("fuel_balance", 8, 2)->default(0); // in liters
            $table->decimal("daily_limit", 8, 2)->default(0); // in liters
            $table->decimal("weekly_limit", 8, 2)->default(0); // in liters
            $table->decimal("monthly_limit", 8, 2)->default(0); // in liters
            $table->decimal("transaction_limit", 8, 2)->default(0); // per transaction

            // Usage Tracking
            $table->decimal("daily_usage", 8, 2)->default(0);
            $table->decimal("weekly_usage", 8, 2)->default(0);
            $table->decimal("monthly_usage", 8, 2)->default(0);
            $table->decimal("total_usage", 10, 2)->default(0);
            $table->integer("transaction_count")->default(0);

            // Reset Dates for Usage Tracking
            $table->date("daily_reset_date")->nullable();
            $table->date("weekly_reset_date")->nullable();
            $table->date("monthly_reset_date")->nullable();

            // Card Status
            $table
                ->enum("status", [
                    "Active",
                    "Inactive",
                    "Suspended",
                    "Expired",
                    "Lost",
                    "Stolen",
                    "Damaged",
                ])
                ->default("Active");

            // Validity and Expiry
            $table->date("issue_date");
            $table->date("expiry_date");
            $table->date("activation_date")->nullable();
            $table->date("deactivation_date")->nullable();

            // Fuel Type Restrictions
            $table->json("allowed_fuel_types")->nullable(); // ["Petrol", "Diesel", "Premium"]
            $table->boolean("premium_fuel_allowed")->default(false);
            $table->boolean("diesel_allowed")->default(true);
            $table->boolean("petrol_allowed")->default(true);

            // Location and Time Restrictions
            $table->json("allowed_locations")->nullable(); // Specific stations
            $table->json("restricted_locations")->nullable(); // Blocked stations
            $table->time("usage_start_time")->nullable(); // e.g., 06:00
            $table->time("usage_end_time")->nullable(); // e.g., 22:00
            $table->json("allowed_days")->nullable(); // ["Monday", "Tuesday", ...]
            $table->boolean("weekend_usage_allowed")->default(true);

            // Emergency and Override
            $table->boolean("emergency_override")->default(false);
            $table->decimal("emergency_limit", 8, 2)->nullable();
            $table->text("emergency_reason")->nullable();
            $table->datetime("emergency_expires_at")->nullable();

            // Security Features
            $table->boolean("pin_required")->default(false);
            $table->boolean("odometer_required")->default(false);
            $table->boolean("driver_id_required")->default(false);
            $table->integer("failed_attempts")->default(0);
            $table->datetime("locked_until")->nullable();

            // Cost Center and Budgeting
            $table->string("cost_center", 50)->nullable();
            $table->string("budget_code", 50)->nullable();
            $table->string("department", 100)->nullable();
            $table->decimal("allocated_budget", 10, 2)->nullable();
            $table->decimal("consumed_budget", 10, 2)->default(0);

            // Performance Metrics
            $table->decimal("average_fuel_price", 6, 3)->nullable(); // per liter
            $table->decimal("total_fuel_cost", 10, 2)->default(0);
            $table->decimal("fuel_efficiency", 5, 2)->nullable(); // km per liter
            $table->integer("stations_used")->default(0);
            $table->decimal("savings_achieved", 8, 2)->default(0);

            // Last Transaction Info
            $table->datetime("last_transaction_date")->nullable();
            $table->string("last_station", 100)->nullable();
            $table->decimal("last_transaction_amount", 8, 2)->nullable();
            $table->decimal("last_fuel_price", 6, 3)->nullable();

            // Card Replacement
            $table->boolean("replacement_requested")->default(false);
            $table->string("replacement_reason", 100)->nullable();
            $table->datetime("replacement_date")->nullable();
            $table->string("replaced_by_card", 50)->nullable();

            // Alerts and Notifications
            $table->boolean("low_balance_alert")->default(true);
            $table->decimal("low_balance_threshold", 8, 2)->default(10);
            $table->boolean("expiry_alert")->default(true);
            $table->integer("expiry_alert_days")->default(30);
            $table->boolean("limit_exceeded_alert")->default(true);

            // Administrative
            $table->text("notes")->nullable();
            $table->text("restrictions_notes")->nullable();
            $table->json("audit_trail")->nullable();
            $table->boolean("temporary_card")->default(false);
            $table->datetime("temporary_expires_at")->nullable();

            // Integration with External Systems
            $table->string("external_card_id", 50)->nullable();
            $table->string("external_system", 50)->nullable();
            $table->datetime("last_sync_date")->nullable();
            $table->json("sync_status")->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index("card_number");
            $table->index("status");
            $table->index("expiry_date");
            $table->index(["driver_id", "status"]);
            $table->index(["vehicle_id", "status"]);
            $table->index("provider");
            $table->index("cost_center");
            $table->index("last_transaction_date");
            $table->index(["current_balance", "status"]);
            $table->index(["daily_usage", "daily_limit"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("fuel_cards");
    }
};
