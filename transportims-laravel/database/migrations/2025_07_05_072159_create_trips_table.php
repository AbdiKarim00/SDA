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
        Schema::create("trips", function (Blueprint $table) {
            $table->id();

            // Trip Identification
            $table->string("trip_number", 50)->unique();
            $table->string("reference_number", 50)->nullable();

            // Relationships
            $table
                ->foreignId("driver_id")
                ->constrained("drivers")
                ->onDelete("cascade");
            $table
                ->foreignId("vehicle_id")
                ->constrained("vehicles")
                ->onDelete("cascade");
            $table
                ->foreignId("requested_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");
            $table
                ->foreignId("approved_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");

            // Trip Details
            $table->string("start_location", 200);
            $table->string("end_location", 200);
            $table->text("purpose");
            $table->text("additional_stops")->nullable();
            $table->integer("passenger_count")->default(0);
            $table->text("passenger_details")->nullable();

            // Trip Status
            $table
                ->enum("status", [
                    "Requested",
                    "Approved",
                    "Assigned",
                    "In Progress",
                    "Completed",
                    "Cancelled",
                    "Rejected",
                ])
                ->default("Requested");

            // Trip Priority
            $table
                ->enum("priority", ["Low", "Normal", "High", "Urgent"])
                ->default("Normal");

            // Scheduling
            $table->datetime("scheduled_start_time");
            $table->datetime("scheduled_end_time");
            $table->datetime("actual_start_time")->nullable();
            $table->datetime("actual_end_time")->nullable();
            $table->integer("estimated_duration")->nullable(); // in minutes
            $table->integer("actual_duration")->nullable(); // in minutes

            // Distance and Odometer
            $table->decimal("estimated_distance", 8, 2)->nullable(); // in km
            $table->decimal("actual_distance", 8, 2)->nullable(); // in km
            $table->bigInteger("odometer_start")->nullable();
            $table->bigInteger("odometer_end")->nullable();

            // Fuel and Cost Tracking
            $table->decimal("fuel_consumed", 8, 2)->nullable(); // in liters
            $table->decimal("fuel_cost", 10, 2)->nullable();
            $table->decimal("other_expenses", 10, 2)->default(0);
            $table->decimal("total_cost", 10, 2)->nullable();
            $table->text("expense_breakdown")->nullable();

            // Performance Metrics
            $table->decimal("fuel_efficiency", 5, 2)->nullable(); // km per liter
            $table->decimal("average_speed", 5, 2)->nullable(); // km/h
            $table->integer("stops_count")->default(0);
            $table->integer("delay_minutes")->default(0);

            // Route Information
            $table->json("route_coordinates")->nullable();
            $table->text("route_notes")->nullable();
            $table->json("traffic_conditions")->nullable();
            $table->json("weather_conditions")->nullable();

            // Authorization and Approval
            $table->datetime("request_date");
            $table->datetime("approval_date")->nullable();
            $table->text("approval_notes")->nullable();
            $table->text("rejection_reason")->nullable();

            // Documentation
            $table->json("required_documents")->nullable();
            $table->json("completed_documents")->nullable();
            $table->boolean("pre_trip_inspection_completed")->default(false);
            $table->boolean("post_trip_inspection_completed")->default(false);

            // Emergency and Safety
            $table->string("emergency_contact_name", 100)->nullable();
            $table->string("emergency_contact_phone", 20)->nullable();
            $table->text("safety_instructions")->nullable();
            $table->text("special_requirements")->nullable();

            // Maintenance Alerts
            $table->boolean("maintenance_due_alert")->default(false);
            $table->boolean("license_expiry_alert")->default(false);
            $table->boolean("insurance_expiry_alert")->default(false);

            // Trip Completion
            $table->text("completion_notes")->nullable();
            $table->text("issues_encountered")->nullable();
            $table->integer("completion_rating")->nullable(); // 1-5 scale
            $table->text("driver_feedback")->nullable();
            $table->text("passenger_feedback")->nullable();

            // GPS and Tracking
            $table->json("gps_tracking_data")->nullable();
            $table->decimal("start_latitude", 10, 8)->nullable();
            $table->decimal("start_longitude", 11, 8)->nullable();
            $table->decimal("end_latitude", 10, 8)->nullable();
            $table->decimal("end_longitude", 11, 8)->nullable();

            // Administrative
            $table->boolean("recurring_trip")->default(false);
            $table->string("recurring_pattern", 50)->nullable(); // daily, weekly, monthly
            $table
                ->foreignId("parent_trip_id")
                ->nullable()
                ->constrained("trips")
                ->onDelete("set null");
            $table->text("admin_notes")->nullable();

            // Audit Trail
            $table->json("status_history")->nullable();
            $table->datetime("last_updated_at")->nullable();
            $table
                ->foreignId("last_updated_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");

            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index("trip_number");
            $table->index("status");
            $table->index("priority");
            $table->index("scheduled_start_time");
            $table->index("scheduled_end_time");
            $table->index(["driver_id", "status"]);
            $table->index(["vehicle_id", "status"]);
            $table->index(["requested_by", "status"]);
            $table->index(["start_location", "end_location"]);
            $table->index("request_date");
            $table->index("approval_date");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("trips");
    }
};
