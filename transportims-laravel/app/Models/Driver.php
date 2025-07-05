<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "user_id",
        "license_number",
        "license_expiry",
        "license_class",
        "license_issue_date",
        "license_issuing_authority",
        "employee_id",
        "national_id",
        "phone",
        "emergency_contact_name",
        "emergency_contact_phone",
        "address",
        "employment_start_date",
        "employment_end_date",
        "department",
        "supervisor",
        "status",
        "assigned_vehicle_id",
        "vehicle_assigned_date",
        "total_trips",
        "total_distance",
        "incidents_count",
        "fuel_efficiency_rating",
        "safety_rating",
        "medical_certificate_expiry",
        "medical_conditions",
        "medications",
        "defensive_driving_certification",
        "first_aid_certification",
        "additional_certifications",
        "background_check_completed",
        "background_check_date",
        "drug_test_passed",
        "drug_test_date",
        "notes",
        "performance_notes",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "license_expiry" => "date",
        "license_issue_date" => "date",
        "employment_start_date" => "date",
        "employment_end_date" => "date",
        "vehicle_assigned_date" => "date",
        "total_trips" => "integer",
        "total_distance" => "decimal:2",
        "incidents_count" => "integer",
        "fuel_efficiency_rating" => "decimal:2",
        "safety_rating" => "decimal:2",
        "medical_certificate_expiry" => "date",
        "defensive_driving_certification" => "date",
        "first_aid_certification" => "date",
        "additional_certifications" => "json",
        "background_check_completed" => "boolean",
        "background_check_date" => "date",
        "drug_test_passed" => "boolean",
        "drug_test_date" => "date",
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        "license_expiry",
        "license_issue_date",
        "employment_start_date",
        "employment_end_date",
        "vehicle_assigned_date",
        "medical_certificate_expiry",
        "defensive_driving_certification",
        "first_aid_certification",
        "background_check_date",
        "drug_test_date",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * Get the user that owns the driver profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vehicle assigned to the driver.
     */
    public function assignedVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, "assigned_vehicle_id");
    }

    /**
     * Get all trips for this driver.
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Get active trips for this driver.
     */
    public function activeTrips(): HasMany
    {
        return $this->hasMany(Trip::class)->whereIn("status", [
            "Assigned",
            "In Progress",
        ]);
    }

    /**
     * Get completed trips for this driver.
     */
    public function completedTrips(): HasMany
    {
        return $this->hasMany(Trip::class)->where("status", "Completed");
    }

    /**
     * Get the fuel card assigned to this driver.
     */
    public function fuelCard(): HasOne
    {
        return $this->hasOne(FuelCard::class);
    }

    /**
     * Get all incidents involving this driver.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Get open incidents for this driver.
     */
    public function openIncidents(): HasMany
    {
        return $this->hasMany(Incident::class)->whereNotIn("status", [
            "Resolved",
            "Closed",
        ]);
    }

    /**
     * Get the driver's full name from user relationship.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user ? $this->user->name : "Unknown";
    }

    /**
     * Get the driver's email from user relationship.
     */
    public function getEmailAttribute(): string
    {
        return $this->user ? $this->user->email : "";
    }

    /**
     * Get the driver's employment duration in years.
     */
    public function getEmploymentDurationAttribute(): float
    {
        if (!$this->employment_start_date) {
            return 0;
        }

        $endDate = $this->employment_end_date ?: now();
        return Carbon::parse($this->employment_start_date)->diffInYears(
            $endDate
        );
    }

    /**
     * Check if the driver is available for assignment.
     */
    public function isAvailable(): bool
    {
        return $this->status === "Available";
    }

    /**
     * Check if the driver is currently on a trip.
     */
    public function isOnTrip(): bool
    {
        return $this->status === "On Trip";
    }

    /**
     * Check if the driver is on leave.
     */
    public function isOnLeave(): bool
    {
        return $this->status === "On Leave";
    }

    /**
     * Check if the driver is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === "Suspended";
    }

    /**
     * Check if the driver is terminated.
     */
    public function isTerminated(): bool
    {
        return $this->status === "Terminated";
    }

    /**
     * Check if the driver's license is expired or expiring soon.
     */
    public function isLicenseExpiring(int $days = 30): bool
    {
        if (!$this->license_expiry) {
            return false;
        }

        return Carbon::parse($this->license_expiry)->diffInDays(now()) <= $days;
    }

    /**
     * Check if the driver's license is expired.
     */
    public function isLicenseExpired(): bool
    {
        if (!$this->license_expiry) {
            return false;
        }

        return Carbon::parse($this->license_expiry)->isPast();
    }

    /**
     * Check if the driver's medical certificate is expired or expiring soon.
     */
    public function isMedicalCertificateExpiring(int $days = 30): bool
    {
        if (!$this->medical_certificate_expiry) {
            return false;
        }

        return Carbon::parse($this->medical_certificate_expiry)->diffInDays(
            now()
        ) <= $days;
    }

    /**
     * Check if the driver's medical certificate is expired.
     */
    public function isMedicalCertificateExpired(): bool
    {
        if (!$this->medical_certificate_expiry) {
            return false;
        }

        return Carbon::parse($this->medical_certificate_expiry)->isPast();
    }

    /**
     * Check if the driver has a vehicle assigned.
     */
    public function hasAssignedVehicle(): bool
    {
        return !is_null($this->assigned_vehicle_id);
    }

    /**
     * Check if the driver's background check is completed.
     */
    public function hasCompletedBackgroundCheck(): bool
    {
        return $this->background_check_completed;
    }

    /**
     * Check if the driver has passed drug test.
     */
    public function hasPassedDrugTest(): bool
    {
        return $this->drug_test_passed;
    }

    /**
     * Get the driver's average fuel efficiency rating.
     */
    public function getAverageFuelEfficiencyAttribute(): ?float
    {
        $recentTrips = $this->trips()
            ->where("status", "Completed")
            ->where("created_at", ">=", now()->subMonths(3))
            ->whereNotNull("fuel_efficiency")
            ->get();

        if ($recentTrips->isEmpty()) {
            return $this->fuel_efficiency_rating;
        }

        return $recentTrips->avg("fuel_efficiency");
    }

    /**
     * Get the driver's incident rate (incidents per 1000 km).
     */
    public function getIncidentRateAttribute(): float
    {
        if ($this->total_distance == 0) {
            return 0;
        }

        return ($this->incidents_count / $this->total_distance) * 1000;
    }

    /**
     * Get the driver's performance score.
     */
    public function getPerformanceScoreAttribute(): float
    {
        $score = 0;
        $factors = 0;

        if ($this->safety_rating) {
            $score += $this->safety_rating * 0.4;
            $factors++;
        }

        if ($this->fuel_efficiency_rating) {
            $score += $this->fuel_efficiency_rating * 0.3;
            $factors++;
        }

        // Factor in incident rate (lower is better)
        $incidentScore = max(0, 5 - $this->getIncidentRateAttribute());
        $score += $incidentScore * 0.3;
        $factors++;

        return $factors > 0 ? $score / $factors : 0;
    }

    /**
     * Scope a query to only include available drivers.
     */
    public function scopeAvailable($query)
    {
        return $query->where("status", "Available");
    }

    /**
     * Scope a query to only include drivers on trips.
     */
    public function scopeOnTrip($query)
    {
        return $query->where("status", "On Trip");
    }

    /**
     * Scope a query to only include active drivers.
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn("status", ["Suspended", "Terminated"]);
    }

    /**
     * Scope a query to only include drivers with expiring licenses.
     */
    public function scopeLicenseExpiring($query, int $days = 30)
    {
        return $query
            ->whereNotNull("license_expiry")
            ->where("license_expiry", "<=", now()->addDays($days));
    }

    /**
     * Scope a query to only include drivers with expired licenses.
     */
    public function scopeLicenseExpired($query)
    {
        return $query
            ->whereNotNull("license_expiry")
            ->where("license_expiry", "<", now());
    }

    /**
     * Scope a query to only include drivers with expiring medical certificates.
     */
    public function scopeMedicalCertificateExpiring($query, int $days = 30)
    {
        return $query
            ->whereNotNull("medical_certificate_expiry")
            ->where("medical_certificate_expiry", "<=", now()->addDays($days));
    }

    /**
     * Scope a query to only include drivers with assigned vehicles.
     */
    public function scopeWithVehicle($query)
    {
        return $query->whereNotNull("assigned_vehicle_id");
    }

    /**
     * Scope a query to only include drivers without assigned vehicles.
     */
    public function scopeWithoutVehicle($query)
    {
        return $query->whereNull("assigned_vehicle_id");
    }

    /**
     * Scope a query to only include drivers by department.
     */
    public function scopeByDepartment($query, string $department)
    {
        return $query->where("department", $department);
    }

    /**
     * Update the driver's status.
     */
    public function updateStatus(string $status): bool
    {
        $validStatuses = [
            "Available",
            "On Trip",
            "On Leave",
            "Suspended",
            "Terminated",
        ];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->update(["status" => $status]);
    }

    /**
     * Assign a vehicle to the driver.
     */
    public function assignVehicle(Vehicle $vehicle): bool
    {
        if (!$this->isAvailable() || !$vehicle->isAvailable()) {
            return false;
        }

        return $this->update([
            "assigned_vehicle_id" => $vehicle->id,
            "vehicle_assigned_date" => now(),
        ]);
    }

    /**
     * Unassign the vehicle from the driver.
     */
    public function unassignVehicle(): bool
    {
        return $this->update([
            "assigned_vehicle_id" => null,
            "vehicle_assigned_date" => null,
        ]);
    }

    /**
     * Update trip statistics.
     */
    public function updateTripStatistics(): void
    {
        $completedTrips = $this->completedTrips();

        $this->update([
            "total_trips" => $completedTrips->count(),
            "total_distance" => $completedTrips->sum("actual_distance") ?? 0,
        ]);
    }

    /**
     * Update incidents count.
     */
    public function updateIncidentsCount(): void
    {
        $this->update([
            "incidents_count" => $this->incidents()->count(),
        ]);
    }

    /**
     * Get alerts for the driver.
     */
    public function getAlertsAttribute(): array
    {
        $alerts = [];

        if ($this->isLicenseExpiring()) {
            $alerts[] = [
                "type" => "warning",
                "message" =>
                    "License expiring on " .
                    $this->license_expiry->format("Y-m-d"),
            ];
        }

        if ($this->isLicenseExpired()) {
            $alerts[] = [
                "type" => "danger",
                "message" =>
                    "License expired on " .
                    $this->license_expiry->format("Y-m-d"),
            ];
        }

        if ($this->isMedicalCertificateExpiring()) {
            $alerts[] = [
                "type" => "warning",
                "message" =>
                    "Medical certificate expiring on " .
                    $this->medical_certificate_expiry->format("Y-m-d"),
            ];
        }

        if ($this->isMedicalCertificateExpired()) {
            $alerts[] = [
                "type" => "danger",
                "message" =>
                    "Medical certificate expired on " .
                    $this->medical_certificate_expiry->format("Y-m-d"),
            ];
        }

        if (!$this->hasCompletedBackgroundCheck()) {
            $alerts[] = [
                "type" => "warning",
                "message" => "Background check not completed",
            ];
        }

        if (!$this->hasPassedDrugTest()) {
            $alerts[] = [
                "type" => "warning",
                "message" => "Drug test not passed",
            ];
        }

        if ($this->openIncidents()->count() > 0) {
            $alerts[] = [
                "type" => "danger",
                "message" =>
                    "Has " .
                    $this->openIncidents()->count() .
                    " open incident(s)",
            ];
        }

        return $alerts;
    }

    /**
     * Get the driver's compliance status.
     */
    public function getComplianceStatusAttribute(): string
    {
        if ($this->isLicenseExpired() || $this->isMedicalCertificateExpired()) {
            return "Non-Compliant";
        }

        if (
            $this->isLicenseExpiring() ||
            $this->isMedicalCertificateExpiring()
        ) {
            return "Expiring Soon";
        }

        if (
            !$this->hasCompletedBackgroundCheck() ||
            !$this->hasPassedDrugTest()
        ) {
            return "Incomplete";
        }

        return "Compliant";
    }

    /**
     * Check if the driver is compliant with all requirements.
     */
    public function isCompliant(): bool
    {
        return $this->getComplianceStatusAttribute() === "Compliant";
    }

    /**
     * Get the driver's current trip.
     */
    public function getCurrentTripAttribute(): ?Trip
    {
        return $this->activeTrips()->first();
    }

    /**
     * Check if the driver is currently on an active trip.
     */
    public function hasActiveTrip(): bool
    {
        return $this->activeTrips()->exists();
    }

    /**
     * Get the driver's monthly trip count.
     */
    public function getMonthlyTripCountAttribute(): int
    {
        return $this->trips()
            ->where("created_at", ">=", now()->startOfMonth())
            ->count();
    }

    /**
     * Get the driver's yearly trip count.
     */
    public function getYearlyTripCountAttribute(): int
    {
        return $this->trips()
            ->where("created_at", ">=", now()->startOfYear())
            ->count();
    }
}
