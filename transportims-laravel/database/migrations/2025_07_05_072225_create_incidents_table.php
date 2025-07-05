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
        Schema::create("incidents", function (Blueprint $table) {
            $table->id();

            // Incident Identification
            $table->string("incident_number", 50)->unique();
            $table->string("case_number", 50)->nullable();
            $table->string("reference_number", 50)->nullable();
            $table->string("police_report_number", 50)->nullable();
            $table->string("insurance_claim_number", 50)->nullable();

            // Relationships
            $table
                ->foreignId("vehicle_id")
                ->nullable()
                ->constrained("vehicles")
                ->onDelete("set null");
            $table
                ->foreignId("driver_id")
                ->nullable()
                ->constrained("drivers")
                ->onDelete("set null");
            $table
                ->foreignId("trip_id")
                ->nullable()
                ->constrained("trips")
                ->onDelete("set null");
            $table
                ->foreignId("reported_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");

            // Incident Basic Information
            $table->datetime("incident_date_time");
            $table->datetime("reported_date_time");
            $table->string("incident_title", 200);
            $table->text("incident_description");
            $table->text("initial_report")->nullable();

            // Incident Classification
            $table
                ->enum("incident_type", [
                    "Accident",
                    "Breakdown",
                    "Theft",
                    "Vandalism",
                    "Traffic Violation",
                    "Fuel Theft",
                    "Maintenance Issue",
                    "Safety Violation",
                    "Environmental",
                    "Security",
                    "Other",
                ])
                ->default("Other");

            $table
                ->enum("severity", [
                    "Low",
                    "Medium",
                    "High",
                    "Critical",
                    "Fatal",
                ])
                ->default("Medium");

            $table
                ->enum("category", [
                    "Property Damage",
                    "Personal Injury",
                    "Fatality",
                    "Near Miss",
                    "Environmental",
                    "Security",
                    "Operational",
                    "Regulatory",
                ])
                ->default("Property Damage");

            // Location Information
            $table->string("location_address", 300);
            $table->string("location_description", 200)->nullable();
            $table->decimal("latitude", 10, 8)->nullable();
            $table->decimal("longitude", 11, 8)->nullable();
            $table->string("nearest_landmark", 100)->nullable();
            $table->string("road_type", 50)->nullable(); // Highway, City Road, etc.
            $table->string("weather_conditions", 100)->nullable();
            $table->string("road_conditions", 100)->nullable();
            $table->string("lighting_conditions", 50)->nullable();
            $table->string("traffic_conditions", 50)->nullable();

            // Personnel Involved
            $table->json("personnel_involved")->nullable(); // Array of people involved
            $table->json("witnesses")->nullable(); // Array of witnesses
            $table->json("emergency_responders")->nullable(); // Police, Medical, etc.
            $table->integer("injured_count")->default(0);
            $table->integer("fatality_count")->default(0);
            $table->text("injury_details")->nullable();

            // Third Party Information
            $table->json("third_party_vehicles")->nullable();
            $table->json("third_party_persons")->nullable();
            $table->json("third_party_property")->nullable();
            $table->text("third_party_insurance")->nullable();

            // Damage Assessment
            $table->text("vehicle_damage_description")->nullable();
            $table->decimal("estimated_vehicle_damage_cost", 12, 2)->default(0);
            $table->decimal("actual_vehicle_damage_cost", 12, 2)->nullable();
            $table->text("property_damage_description")->nullable();
            $table
                ->decimal("estimated_property_damage_cost", 12, 2)
                ->default(0);
            $table->decimal("actual_property_damage_cost", 12, 2)->nullable();
            $table->boolean("vehicle_drivable")->default(true);
            $table->boolean("vehicle_towed")->default(false);
            $table->string("towing_company", 100)->nullable();
            $table->decimal("towing_cost", 8, 2)->nullable();

            // Emergency Response
            $table->boolean("emergency_services_called")->default(false);
            $table->boolean("police_called")->default(false);
            $table->boolean("ambulance_called")->default(false);
            $table->boolean("fire_department_called")->default(false);
            $table->time("emergency_response_time")->nullable();
            $table->text("emergency_actions_taken")->nullable();

            // Investigation
            $table->text("preliminary_investigation")->nullable();
            $table->text("detailed_investigation")->nullable();
            $table->text("investigation_findings")->nullable();
            $table->text("root_cause_analysis")->nullable();
            $table->text("contributing_factors")->nullable();
            $table
                ->foreignId("investigated_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");
            $table->datetime("investigation_start_date")->nullable();
            $table->datetime("investigation_completion_date")->nullable();

            // Legal and Regulatory
            $table->boolean("legal_action_required")->default(false);
            $table->text("legal_proceedings")->nullable();
            $table->boolean("regulatory_reporting_required")->default(false);
            $table->json("regulatory_agencies_notified")->nullable();
            $table->text("regulatory_compliance_issues")->nullable();
            $table->text("citations_issued")->nullable();
            $table->decimal("fines_imposed", 10, 2)->default(0);

            // Insurance
            $table->boolean("insurance_notified")->default(false);
            $table->datetime("insurance_notification_date")->nullable();
            $table->string("insurance_company", 100)->nullable();
            $table->string("insurance_adjuster", 100)->nullable();
            $table->string("insurance_adjuster_phone", 20)->nullable();
            $table->decimal("insurance_deductible", 8, 2)->nullable();
            $table->decimal("insurance_coverage_amount", 12, 2)->nullable();
            $table->decimal("insurance_settlement_amount", 12, 2)->nullable();
            $table->date("insurance_settlement_date")->nullable();

            // Cost Tracking
            $table->decimal("medical_costs", 10, 2)->default(0);
            $table->decimal("legal_costs", 10, 2)->default(0);
            $table->decimal("administrative_costs", 8, 2)->default(0);
            $table->decimal("loss_of_use_costs", 10, 2)->default(0);
            $table->decimal("other_costs", 10, 2)->default(0);
            $table->decimal("total_incident_cost", 12, 2)->default(0);
            $table->decimal("recoverable_amount", 10, 2)->default(0);
            $table->decimal("net_loss_amount", 10, 2)->default(0);

            // Documentation and Evidence
            $table->json("photos_attached")->nullable();
            $table->json("documents_attached")->nullable();
            $table->json("video_evidence")->nullable();
            $table->json("audio_recordings")->nullable();
            $table->text("sketch_diagram")->nullable();
            $table->boolean("dashcam_footage_available")->default(false);
            $table->json("dashcam_files")->nullable();

            // Status and Resolution
            $table
                ->enum("status", [
                    "Reported",
                    "Under Investigation",
                    "Investigation Complete",
                    "Pending Legal",
                    "Pending Insurance",
                    "In Litigation",
                    "Resolved",
                    "Closed",
                ])
                ->default("Reported");

            $table
                ->enum("resolution_status", [
                    "Pending",
                    "Resolved - Fault",
                    "Resolved - No Fault",
                    "Resolved - Partial Fault",
                    "Disputed",
                    "Settled",
                    "Closed - No Action",
                ])
                ->nullable();

            // Corrective Actions
            $table->text("immediate_actions_taken")->nullable();
            $table->text("corrective_actions_required")->nullable();
            $table->text("preventive_measures")->nullable();
            $table->text("policy_changes_recommended")->nullable();
            $table->text("training_recommendations")->nullable();
            $table->json("action_items")->nullable();
            $table->date("corrective_actions_due_date")->nullable();

            // Follow-up
            $table->boolean("follow_up_required")->default(false);
            $table->date("follow_up_date")->nullable();
            $table->text("follow_up_notes")->nullable();
            $table->boolean("lessons_learned_documented")->default(false);
            $table->text("lessons_learned")->nullable();

            // Approval and Review
            $table
                ->foreignId("reviewed_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");
            $table->datetime("review_date")->nullable();
            $table->text("review_comments")->nullable();
            $table
                ->foreignId("approved_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");
            $table->datetime("approval_date")->nullable();
            $table->text("approval_comments")->nullable();

            // Communication and Notifications
            $table->json("notifications_sent")->nullable();
            $table->json("stakeholders_notified")->nullable();
            $table->text("media_involvement")->nullable();
            $table->text("public_relations_impact")->nullable();

            // Performance Impact
            $table->integer("downtime_hours")->default(0);
            $table->decimal("revenue_loss", 10, 2)->default(0);
            $table->integer("trips_affected")->default(0);
            $table->text("operational_impact")->nullable();
            $table->text("customer_impact")->nullable();

            // Driver Performance Impact
            $table->boolean("driver_at_fault")->default(false);
            $table->decimal("fault_percentage", 5, 2)->default(0);
            $table->boolean("driver_disciplinary_action")->default(false);
            $table->text("disciplinary_action_taken")->nullable();
            $table->boolean("additional_training_required")->default(false);
            $table->text("training_requirements")->nullable();

            // Environmental Impact
            $table->boolean("environmental_impact")->default(false);
            $table->text("environmental_damage")->nullable();
            $table->text("cleanup_actions")->nullable();
            $table->decimal("environmental_cleanup_cost", 10, 2)->default(0);

            // Quality Assurance
            $table->integer("incident_rating")->nullable(); // 1-5 scale
            $table->text("quality_review_notes")->nullable();
            $table->boolean("quality_review_completed")->default(false);
            $table->date("quality_review_date")->nullable();

            // Audit Trail
            $table->json("status_history")->nullable();
            $table->datetime("last_updated_at")->nullable();
            $table
                ->foreignId("last_updated_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");

            // Administrative
            $table->text("internal_notes")->nullable();
            $table->text("external_notes")->nullable();
            $table->boolean("confidential")->default(false);
            $table->string("cost_center", 50)->nullable();
            $table->string("department", 100)->nullable();

            $table->timestamps();

            // Indexes for better performance
            $table->index("incident_number");
            $table->index("case_number");
            $table->index("police_report_number");
            $table->index("insurance_claim_number");
            $table->index("incident_date_time");
            $table->index("reported_date_time");
            $table->index("incident_type");
            $table->index("severity");
            $table->index("category");
            $table->index("status");
            $table->index("resolution_status");
            $table->index(["vehicle_id", "status"]);
            $table->index(["driver_id", "status"]);
            $table->index(["trip_id", "status"]);
            $table->index(["reported_by", "status"]);
            $table->index("location_address");
            $table->index("total_incident_cost");
            $table->index(["incident_type", "severity"]);
            $table->index(["driver_at_fault", "driver_id"]);
            $table->index("insurance_notification_date");
            $table->index("investigation_completion_date");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("incidents");
    }
};
