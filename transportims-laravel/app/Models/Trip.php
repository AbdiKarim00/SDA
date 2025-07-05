<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "trip_number",
        "reference_number",
        "driver_id",
        "vehicle_id",
        "requested_by",
        "approved_by",
        "start_location",
        "end_location",
        "purpose",
        "additional_stops",
        "passenger_count",
        "passenger_details",
        "status",
        "priority",
        "scheduled_start_time",
        "scheduled_end_time",
        "actual_start_time",
        "actual_end_time",
        "estimated_duration",
        "actual_duration",
        "estimated_distance",
        "actual_distance",
        "odometer_start",
        "odometer_end",
        "fuel_consumed",
        "fuel_cost",
        "other_expenses",
        "total_cost",
        "expense_breakdown",
        "fuel_efficiency",
        "average_speed",
        "stops_count",
        "delay_minutes",
        "route_coordinates",
        "route_notes",
        "traffic_conditions",
        "weather_conditions",
        "request_date",
        "approval_date",
        "approval_notes",
        "rejection_reason",
        "required_documents",
        "completed_documents",
        "pre_trip_inspection_completed",
        "post_trip_inspection_completed",
        "emergency_contact_name",
        "emergency_contact_phone",
        "safety_instructions",
        "special_requirements",
        "maintenance_due_alert",
        "license_expiry_alert",
        "insurance_expiry_alert",
        "completion_notes",
        "issues_encountered",
        "completion_rating",
        "driver_feedback",
        "passenger_feedback",
        "gps_tracking_data",
        "start_latitude",
        "start_longitude",
        "end_latitude",
        "end_longitude",
        "recurring_trip",
        "recurring_pattern",
        "parent_trip_id",
        "admin_notes",
        "status_history",
        "last_updated_at",
        "last_updated_by",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "passenger_count" => "integer",
        "scheduled_start_time" => "datetime",
        "scheduled_end_time" => "datetime",
        "actual_start_time" => "datetime",
        "actual_end_time" => "datetime",
        "estimated_duration" => "integer",
        "actual_duration" => "integer",
        "estimated_distance" => "decimal:2",
        "actual_distance" => "decimal:2",
        "odometer_start" => "integer",
        "odometer_end" => "integer",
        "fuel_consumed" => "decimal:2",
        "fuel_cost" => "decimal:2",
        "other_expenses" => "decimal:2",
        "total_cost" => "decimal:2",
        "fuel_efficiency" => "decimal:2",
        "average_speed" => "decimal:2",
        "stops_count" => "integer",
        "delay_minutes" => "integer",
        "route_coordinates" => "json",
        "traffic_conditions" => "json",
        "weather_conditions" => "json",
        "request_date" => "datetime",
        "approval_date" => "datetime",
        "required_documents" => "json",
        "completed_documents" => "json",
        "pre_trip_inspection_completed" => "boolean",
        "post_trip_inspection_completed" => "boolean",
        "maintenance_due_alert" => "boolean",
        "license_expiry_alert" => "boolean",
        "insurance_expiry_alert" => "boolean",
        "completion_rating" => "integer",
        "gps_tracking_data" => "json",
        "start_latitude" => "decimal:8",
        "start_longitude" => "decimal:8",
        "end_latitude" => "decimal:8",
        "end_longitude" => "decimal:8",
        "recurring_trip" => "boolean",
        "status_history" => "json",
        "last_updated_at" => "datetime",
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        "scheduled_start_time",
        "scheduled_end_time",
        "actual_start_time",
        "actual_end_time",
        "request_date",
        "approval_date",
        "last_updated_at",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * Get the driver assigned to this trip.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the vehicle assigned to this trip.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who requested this trip.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "requested_by");
    }

    /**
     * Get the user who approved this trip.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "approved_by");
    }

    /**
     * Get the user who last updated this trip.
     */
    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "last_updated_by");
    }

    /**
     * Get the parent trip if this is a recurring trip.
     */
    public function parentTrip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, "parent_trip_id");
    }

    /**
     * Get child trips if this is a recurring trip.
     */
    public function childTrips(): HasMany
    {
        return $this->hasMany(Trip::class, "parent_trip_id");
    }

    /**
     * Get all incidents related to this trip.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Get the trip's duration in minutes.
     */
    public function getDurationAttribute(): ?int
    {
        if ($this->actual_start_time && $this->actual_end_time) {
            return $this->actual_start_time->diffInMinutes(
                $this->actual_end_time
            );
        }

        return null;
    }

    /**
     * Get the trip's route description.
     */
    public function getRouteDescriptionAttribute(): string
    {
        return $this->start_location . " → " . $this->end_location;
    }

    /**
     * Get the trip's progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->status === "Completed") {
            return 100;
        }

        if ($this->status === "In Progress") {
            if ($this->actual_start_time && $this->scheduled_end_time) {
                $totalDuration = $this->actual_start_time->diffInMinutes(
                    $this->scheduled_end_time
                );
                $elapsedDuration = $this->actual_start_time->diffInMinutes(
                    now()
                );
                return min(100, ($elapsedDuration / $totalDuration) * 100);
            }
            return 50;
        }

        return 0;
    }

    /**
     * Check if the trip is pending approval.
     */
    public function isPendingApproval(): bool
    {
        return $this->status === "Requested";
    }

    /**
     * Check if the trip is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === "Approved";
    }

    /**
     * Check if the trip is assigned.
     */
    public function isAssigned(): bool
    {
        return $this->status === "Assigned";
    }

    /**
     * Check if the trip is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === "In Progress";
    }

    /**
     * Check if the trip is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === "Completed";
    }

    /**
     * Check if the trip is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === "Cancelled";
    }

    /**
     * Check if the trip is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === "Rejected";
    }

    /**
     * Check if the trip is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->scheduled_end_time &&
            $this->scheduled_end_time->isPast() &&
            !$this->isCompleted();
    }

    /**
     * Check if the trip is delayed.
     */
    public function isDelayed(): bool
    {
        return $this->delay_minutes > 0;
    }

    /**
     * Check if the trip has high priority.
     */
    public function isHighPriority(): bool
    {
        return in_array($this->priority, ["High", "Urgent"]);
    }

    /**
     * Scope a query to only include trips with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where("status", $status);
    }

    /**
     * Scope a query to only include pending trips.
     */
    public function scopePending($query)
    {
        return $query->where("status", "Requested");
    }

    /**
     * Scope a query to only include active trips.
     */
    public function scopeActive($query)
    {
        return $query->whereIn("status", ["Assigned", "In Progress"]);
    }

    /**
     * Scope a query to only include completed trips.
     */
    public function scopeCompleted($query)
    {
        return $query->where("status", "Completed");
    }

    /**
     * Scope a query to only include overdue trips.
     */
    public function scopeOverdue($query)
    {
        return $query
            ->where("scheduled_end_time", "<", now())
            ->whereNotIn("status", ["Completed", "Cancelled"]);
    }

    /**
     * Scope a query to only include high priority trips.
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn("priority", ["High", "Urgent"]);
    }

    /**
     * Scope a query to only include trips for a specific driver.
     */
    public function scopeForDriver($query, $driverId)
    {
        return $query->where("driver_id", $driverId);
    }

    /**
     * Scope a query to only include trips for a specific vehicle.
     */
    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where("vehicle_id", $vehicleId);
    }

    /**
     * Scope a query to only include trips within a date range.
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween("scheduled_start_time", [
            $startDate,
            $endDate,
        ]);
    }

    /**
     * Scope a query to only include recurring trips.
     */
    public function scopeRecurring($query)
    {
        return $query->where("recurring_trip", true);
    }

    /**
     * Update the trip status.
     */
    public function updateStatus(string $status, ?User $user = null): bool
    {
        $validStatuses = [
            "Requested",
            "Approved",
            "Assigned",
            "In Progress",
            "Completed",
            "Cancelled",
            "Rejected",
        ];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $updateData = [
            "status" => $status,
            "last_updated_at" => now(),
        ];

        if ($user) {
            $updateData["last_updated_by"] = $user->id;
        }

        // Update status history
        $statusHistory = $this->status_history ?? [];
        $statusHistory[] = [
            "status" => $status,
            "timestamp" => now(),
            "user_id" => $user ? $user->id : null,
            "user_name" => $user ? $user->name : null,
        ];
        $updateData["status_history"] = $statusHistory;

        return $this->update($updateData);
    }

    /**
     * Approve the trip.
     */
    public function approve(User $user, ?string $notes = null): bool
    {
        if (!$this->isPendingApproval()) {
            return false;
        }

        return $this->update([
            "status" => "Approved",
            "approved_by" => $user->id,
            "approval_date" => now(),
            "approval_notes" => $notes,
            "last_updated_at" => now(),
            "last_updated_by" => $user->id,
        ]);
    }

    /**
     * Reject the trip.
     */
    public function reject(User $user, string $reason): bool
    {
        if (!$this->isPendingApproval()) {
            return false;
        }

        return $this->update([
            "status" => "Rejected",
            "approved_by" => $user->id,
            "approval_date" => now(),
            "rejection_reason" => $reason,
            "last_updated_at" => now(),
            "last_updated_by" => $user->id,
        ]);
    }

    /**
     * Start the trip.
     */
    public function startTrip(array $data = []): bool
    {
        if (!in_array($this->status, ["Approved", "Assigned"])) {
            return false;
        }

        $updateData = array_merge(
            [
                "status" => "In Progress",
                "actual_start_time" => now(),
                "last_updated_at" => now(),
            ],
            $data
        );

        // Update vehicle status
        if ($this->vehicle) {
            $this->vehicle->updateStatus("On Trip");
        }

        // Update driver status
        if ($this->driver) {
            $this->driver->updateStatus("On Trip");
        }

        return $this->update($updateData);
    }

    /**
     * Complete the trip.
     */
    public function completeTrip(array $data = []): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $updateData = array_merge(
            [
                "status" => "Completed",
                "actual_end_time" => now(),
                "last_updated_at" => now(),
            ],
            $data
        );

        // Calculate actual duration
        if ($this->actual_start_time) {
            $updateData[
                "actual_duration"
            ] = $this->actual_start_time->diffInMinutes(now());
        }

        // Calculate delay
        if ($this->scheduled_end_time) {
            $updateData["delay_minutes"] = max(
                0,
                now()->diffInMinutes($this->scheduled_end_time)
            );
        }

        // Calculate fuel efficiency if data provided
        if (isset($data["fuel_consumed"]) && isset($data["actual_distance"])) {
            $updateData["fuel_efficiency"] =
                $data["actual_distance"] / $data["fuel_consumed"];
        }

        // Update vehicle status
        if ($this->vehicle) {
            $this->vehicle->updateStatus("Available");
        }

        // Update driver status
        if ($this->driver) {
            $this->driver->updateStatus("Available");
            $this->driver->updateTripStatistics();
        }

        return $this->update($updateData);
    }

    /**
     * Cancel the trip.
     */
    public function cancelTrip(string $reason, ?User $user = null): bool
    {
        if (in_array($this->status, ["Completed", "Cancelled"])) {
            return false;
        }

        $updateData = [
            "status" => "Cancelled",
            "admin_notes" => $reason,
            "last_updated_at" => now(),
        ];

        if ($user) {
            $updateData["last_updated_by"] = $user->id;
        }

        // Update vehicle status
        if ($this->vehicle && $this->vehicle->status === "On Trip") {
            $this->vehicle->updateStatus("Available");
        }

        // Update driver status
        if ($this->driver && $this->driver->status === "On Trip") {
            $this->driver->updateStatus("Available");
        }

        return $this->update($updateData);
    }

    /**
     * Calculate the total cost of the trip.
     */
    public function calculateTotalCost(): void
    {
        $totalCost = ($this->fuel_cost ?? 0) + ($this->other_expenses ?? 0);
        $this->update(["total_cost" => $totalCost]);
    }

    /**
     * Get the trip's estimated arrival time.
     */
    public function getEstimatedArrivalAttribute(): ?Carbon
    {
        if ($this->actual_start_time && $this->estimated_duration) {
            return $this->actual_start_time->addMinutes(
                $this->estimated_duration
            );
        }

        return $this->scheduled_end_time;
    }

    /**
     * Get the trip's remaining time.
     */
    public function getRemainingTimeAttribute(): ?int
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return 0;
        }

        if ($this->isInProgress() && $this->scheduled_end_time) {
            return now()->diffInMinutes($this->scheduled_end_time, false);
        }

        if (
            $this->scheduled_start_time &&
            $this->scheduled_start_time->isFuture()
        ) {
            return now()->diffInMinutes($this->scheduled_start_time, false);
        }

        return null;
    }

    /**
     * Get the trip's status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            "Requested" => "yellow",
            "Approved" => "blue",
            "Assigned" => "purple",
            "In Progress" => "green",
            "Completed" => "gray",
            "Cancelled" => "red",
            "Rejected" => "red",
            default => "gray",
        };
    }

    /**
     * Get the trip's priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            "Low" => "gray",
            "Normal" => "blue",
            "High" => "orange",
            "Urgent" => "red",
            default => "gray",
        };
    }

    /**
     * Generate a unique trip number.
     */
    public static function generateTripNumber(): string
    {
        $prefix = "TRP";
        $date = now()->format("Ymd");
        $sequence = str_pad(
            Trip::whereDate("created_at", today())->count() + 1,
            3,
            "0",
            STR_PAD_LEFT
        );

        return $prefix . $date . $sequence;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($trip) {
            if (!$trip->trip_number) {
                $trip->trip_number = self::generateTripNumber();
            }
            if (!$trip->request_date) {
                $trip->request_date = now();
            }
        });
    }
}
