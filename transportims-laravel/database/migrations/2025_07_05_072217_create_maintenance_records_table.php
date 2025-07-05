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
        Schema::create("maintenance_records", function (Blueprint $table) {
            $table->id();

            // Maintenance Record Identification
            $table->string("maintenance_number", 50)->unique();
            $table->string("work_order_number", 50)->nullable();
            $table->string("reference_number", 50)->nullable();

            // Vehicle Relationship
            $table
                ->foreignId("vehicle_id")
                ->constrained("vehicles")
                ->onDelete("cascade");

            // Maintenance Type and Category
            $table
                ->enum("maintenance_type", [
                    "Scheduled",
                    "Unscheduled",
                    "Emergency",
                    "Preventive",
                    "Corrective",
                    "Predictive",
                ])
                ->default("Scheduled");

            $table
                ->enum("category", [
                    "Engine",
                    "Transmission",
                    "Brakes",
                    "Suspension",
                    "Electrical",
                    "Body",
                    "Interior",
                    "Tires",
                    "Fuel System",
                    "Cooling System",
                    "Exhaust",
                    "Safety Systems",
                    "General",
                ])
                ->default("General");

            // Service Details
            $table->text("description");
            $table->text("problem_description")->nullable();
            $table->text("diagnosis")->nullable();
            $table->text("work_performed");
            $table->text("recommendations")->nullable();

            // Scheduling Information
            $table->date("scheduled_date")->nullable();
            $table->date("actual_start_date");
            $table->date("actual_completion_date")->nullable();
            $table->time("start_time")->nullable();
            $table->time("completion_time")->nullable();
            $table->integer("estimated_duration")->nullable(); // in hours
            $table->integer("actual_duration")->nullable(); // in hours

            // Mileage/Odometer Information
            $table->bigInteger("odometer_reading");
            $table->bigInteger("next_service_odometer")->nullable();
            $table->integer("service_interval")->nullable(); // in km
            $table->date("next_service_date")->nullable();

            // Service Provider Information
            $table->string("service_provider", 100)->nullable();
            $table->string("service_location", 200)->nullable();
            $table->string("technician_name", 100)->nullable();
            $table->string("technician_id", 50)->nullable();
            $table->string("shop_supervisor", 100)->nullable();

            // Status Tracking
            $table
                ->enum("status", [
                    "Scheduled",
                    "In Progress",
                    "Completed",
                    "Cancelled",
                    "Postponed",
                    "Pending Parts",
                    "Pending Approval",
                    "Under Review",
                ])
                ->default("Scheduled");

            // Priority and Urgency
            $table
                ->enum("priority", ["Low", "Normal", "High", "Critical"])
                ->default("Normal");

            $table
                ->enum("urgency", ["Routine", "Soon", "Immediate", "Emergency"])
                ->default("Routine");

            // Cost Information
            $table->decimal("labor_cost", 10, 2)->default(0);
            $table->decimal("parts_cost", 10, 2)->default(0);
            $table->decimal("other_costs", 10, 2)->default(0);
            $table->decimal("total_cost", 10, 2)->default(0);
            $table->decimal("estimated_cost", 10, 2)->nullable();
            $table->decimal("tax_amount", 8, 2)->default(0);
            $table->decimal("discount_amount", 8, 2)->default(0);

            // Parts Information
            $table->json("parts_used")->nullable(); // Array of parts with quantities and costs
            $table->json("parts_ordered")->nullable();
            $table->boolean("parts_warranty")->default(false);
            $table->integer("parts_warranty_period")->nullable(); // in months
            $table->date("parts_warranty_expiry")->nullable();

            // Labor Information
            $table->decimal("labor_hours", 5, 2)->default(0);
            $table->decimal("labor_rate", 6, 2)->nullable();
            $table->json("labor_breakdown")->nullable(); // Different types of labor
            $table->boolean("labor_warranty")->default(false);
            $table->integer("labor_warranty_period")->nullable(); // in months
            $table->date("labor_warranty_expiry")->nullable();

            // Quality and Inspection
            $table->boolean("quality_check_passed")->default(false);
            $table->text("quality_check_notes")->nullable();
            $table->string("inspector_name", 100)->nullable();
            $table->date("inspection_date")->nullable();
            $table->boolean("road_test_completed")->default(false);
            $table->text("road_test_notes")->nullable();

            // Compliance and Certification
            $table->boolean("compliance_check")->default(false);
            $table->string("compliance_standard", 100)->nullable();
            $table->string("certification_number", 50)->nullable();
            $table->date("certification_expiry")->nullable();
            $table->json("regulatory_requirements")->nullable();

            // Documentation
            $table->json("documents_attached")->nullable();
            $table->string("invoice_number", 50)->nullable();
            $table->string("receipt_number", 50)->nullable();
            $table->json("photos_before")->nullable();
            $table->json("photos_after")->nullable();
            $table->text("maintenance_checklist")->nullable();

            // Downtime Tracking
            $table->datetime("vehicle_out_of_service")->nullable();
            $table->datetime("vehicle_back_in_service")->nullable();
            $table->integer("downtime_hours")->nullable();
            $table->text("downtime_impact")->nullable();
            $table->boolean("replacement_vehicle_provided")->default(false);

            // Recurring Maintenance
            $table->boolean("recurring_maintenance")->default(false);
            $table->string("recurring_pattern", 50)->nullable(); // monthly, quarterly, etc.
            $table->integer("recurring_interval")->nullable(); // in days or km
            $table->date("next_recurring_date")->nullable();

            // Performance Metrics
            $table->decimal("fuel_efficiency_before", 5, 2)->nullable();
            $table->decimal("fuel_efficiency_after", 5, 2)->nullable();
            $table->text("performance_improvements")->nullable();
            $table->integer("issues_resolved")->default(0);
            $table->integer("new_issues_identified")->default(0);

            // Approval and Authorization
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
            $table->datetime("approval_date")->nullable();
            $table->text("approval_notes")->nullable();

            // Environmental and Safety
            $table->text("environmental_compliance")->nullable();
            $table->text("safety_measures")->nullable();
            $table->text("hazardous_materials")->nullable();
            $table->text("disposal_method")->nullable();

            // Follow-up and Monitoring
            $table->boolean("follow_up_required")->default(false);
            $table->date("follow_up_date")->nullable();
            $table->text("follow_up_notes")->nullable();
            $table->boolean("monitoring_required")->default(false);
            $table->integer("monitoring_period")->nullable(); // in days
            $table->date("monitoring_end_date")->nullable();

            // Customer Satisfaction
            $table->integer("satisfaction_rating")->nullable(); // 1-5 scale
            $table->text("customer_feedback")->nullable();
            $table->text("service_rating_notes")->nullable();

            // Integration and External Systems
            $table->string("external_system_id", 50)->nullable();
            $table->string("external_system_name", 50)->nullable();
            $table->datetime("last_sync_date")->nullable();
            $table->json("sync_status")->nullable();

            // Administrative
            $table->text("internal_notes")->nullable();
            $table->text("customer_notes")->nullable();
            $table->boolean("billable")->default(true);
            $table->string("cost_center", 50)->nullable();
            $table->string("budget_code", 50)->nullable();

            // Audit Trail
            $table->json("status_history")->nullable();
            $table->datetime("last_updated_at")->nullable();
            $table
                ->foreignId("last_updated_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");

            $table->timestamps();

            // Indexes for better performance
            $table->index("maintenance_number");
            $table->index("work_order_number");
            $table->index("status");
            $table->index("priority");
            $table->index("maintenance_type");
            $table->index("category");
            $table->index("scheduled_date");
            $table->index("actual_start_date");
            $table->index("actual_completion_date");
            $table->index(["vehicle_id", "status"]);
            $table->index(["vehicle_id", "maintenance_type"]);
            $table->index("service_provider");
            $table->index("odometer_reading");
            $table->index("next_service_date");
            $table->index("total_cost");
            $table->index(["requested_by", "status"]);
            $table->index(["approved_by", "approval_date"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("maintenance_records");
    }
};
