<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "registration_no",
        "make",
        "model",
        "chassis_no",
        "engine_no",
        "tag_number",
        "pv_number",
        "vehicle_type",
        "capacity",
        "fuel_type",
        "fuel_tank_capacity",
        "date_of_purchase",
        "year_of_purchase",
        "financed_by",
        "funded_by",
        "amount",
        "original_location",
        "current_location",
        "current_mileage",
        "status",
        "responsible_officer",
        "asset_condition",
        "has_logbook",
        "insurance_expiry",
        "road_license_expiry",
        "next_service_due",
        "depreciation_rate",
        "annual_depreciation",
        "accumulated_depreciation",
        "net_book_value",
        "replacement_date",
        "disposal_date",
        "disposal_value",
        "notes",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "date_of_purchase" => "date",
        "year_of_purchase" => "integer",
        "amount" => "decimal:2",
        "current_mileage" => "integer",
        "has_logbook" => "boolean",
        "insurance_expiry" => "date",
        "road_license_expiry" => "date",
        "next_service_due" => "date",
        "depreciation_rate" => "decimal:2",
        "annual_depreciation" => "decimal:2",
        "accumulated_depreciation" => "decimal:2",
        "net_book_value" => "decimal:2",
        "replacement_date" => "date",
        "disposal_date" => "date",
        "disposal_value" => "decimal:2",
        "fuel_tank_capacity" => "integer",
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        "date_of_purchase",
        "insurance_expiry",
        "road_license_expiry",
        "next_service_due",
        "replacement_date",
        "disposal_date",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * Get the driver currently assigned to this vehicle.
     */
    public function currentDriver(): HasOne
    {
        return $this->hasOne(Driver::class, "assigned_vehicle_id");
    }

    /**
     * Get all drivers that have been assigned to this vehicle.
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class, "assigned_vehicle_id");
    }

    /**
     * Get all trips for this vehicle.
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Get active trips for this vehicle.
     */
    public function activeTrips(): HasMany
    {
        return $this->hasMany(Trip::class)->whereIn("status", [
            "Assigned",
            "In Progress",
        ]);
    }

    /**
     * Get completed trips for this vehicle.
     */
    public function completedTrips(): HasMany
    {
        return $this->hasMany(Trip::class)->where("status", "Completed");
    }

    /**
     * Get the fuel card assigned to this vehicle.
     */
    public function fuelCard(): HasOne
    {
        return $this->hasOne(FuelCard::class);
    }

    /**
     * Get all maintenance records for this vehicle.
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    /**
     * Get pending maintenance records for this vehicle.
     */
    public function pendingMaintenance(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class)->whereIn("status", [
            "Scheduled",
            "Pending Parts",
            "Pending Approval",
        ]);
    }

    /**
     * Get completed maintenance records for this vehicle.
     */
    public function completedMaintenance(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class)->where(
            "status",
            "Completed"
        );
    }

    /**
     * Get all incidents involving this vehicle.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Get open incidents for this vehicle.
     */
    public function openIncidents(): HasMany
    {
        return $this->hasMany(Incident::class)->whereNotIn("status", [
            "Resolved",
            "Closed",
        ]);
    }

    /**
     * Get the vehicle's age in years.
     */
    public function getAgeAttribute(): int
    {
        if (!$this->date_of_purchase) {
            return 0;
        }

        return Carbon::parse($this->date_of_purchase)->diffInYears(now());
    }

    /**
     * Get the vehicle's full name (make + model).
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->make . " " . $this->model);
    }

    /**
     * Get the vehicle's display name (registration + make + model).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->registration_no .
            " (" .
            $this->getFullNameAttribute() .
            ")";
    }

    /**
     * Check if the vehicle is available for assignment.
     */
    public function isAvailable(): bool
    {
        return $this->status === "Available";
    }

    /**
     * Check if the vehicle is currently on a trip.
     */
    public function isOnTrip(): bool
    {
        return $this->status === "On Trip";
    }

    /**
     * Check if the vehicle is in maintenance.
     */
    public function isInMaintenance(): bool
    {
        return $this->status === "In Maintenance";
    }

    /**
     * Check if the vehicle is decommissioned.
     */
    public function isDecommissioned(): bool
    {
        return $this->status === "Decommissioned";
    }

    /**
     * Check if the vehicle's insurance is expired or expiring soon.
     */
    public function isInsuranceExpiring(int $days = 30): bool
    {
        if (!$this->insurance_expiry) {
            return false;
        }

        return Carbon::parse($this->insurance_expiry)->diffInDays(now()) <=
            $days;
    }

    /**
     * Check if the vehicle's license is expired or expiring soon.
     */
    public function isLicenseExpiring(int $days = 30): bool
    {
        if (!$this->road_license_expiry) {
            return false;
        }

        return Carbon::parse($this->road_license_expiry)->diffInDays(now()) <=
            $days;
    }

    /**
     * Check if the vehicle is due for service.
     */
    public function isServiceDue(): bool
    {
        if (!$this->next_service_due) {
            return false;
        }

        return Carbon::parse($this->next_service_due)->isPast();
    }

    /**
     * Get the vehicle's fuel efficiency based on recent trips.
     */
    public function getFuelEfficiencyAttribute(): ?float
    {
        $recentTrips = $this->trips()
            ->where("status", "Completed")
            ->where("created_at", ">=", now()->subMonths(3))
            ->whereNotNull("fuel_efficiency")
            ->get();

        if ($recentTrips->isEmpty()) {
            return null;
        }

        return $recentTrips->avg("fuel_efficiency");
    }

    /**
     * Get the vehicle's total trip count.
     */
    public function getTotalTripsAttribute(): int
    {
        return $this->trips()->count();
    }

    /**
     * Get the vehicle's total distance traveled.
     */
    public function getTotalDistanceAttribute(): float
    {
        return $this->trips()
            ->where("status", "Completed")
            ->sum("actual_distance") ?? 0;
    }

    /**
     * Get the vehicle's total maintenance cost.
     */
    public function getTotalMaintenanceCostAttribute(): float
    {
        return $this->maintenanceRecords()
            ->where("status", "Completed")
            ->sum("total_cost") ?? 0;
    }

    /**
     * Get the vehicle's total incident count.
     */
    public function getTotalIncidentsAttribute(): int
    {
        return $this->incidents()->count();
    }

    /**
     * Scope a query to only include available vehicles.
     */
    public function scopeAvailable($query)
    {
        return $query->where("status", "Available");
    }

    /**
     * Scope a query to only include vehicles on trips.
     */
    public function scopeOnTrip($query)
    {
        return $query->where("status", "On Trip");
    }

    /**
     * Scope a query to only include vehicles in maintenance.
     */
    public function scopeInMaintenance($query)
    {
        return $query->where("status", "In Maintenance");
    }

    /**
     * Scope a query to only include active vehicles (not decommissioned).
     */
    public function scopeActive($query)
    {
        return $query->where("status", "!=", "Decommissioned");
    }

    /**
     * Scope a query to only include vehicles by make.
     */
    public function scopeByMake($query, string $make)
    {
        return $query->where("make", $make);
    }

    /**
     * Scope a query to only include vehicles by fuel type.
     */
    public function scopeByFuelType($query, string $fuelType)
    {
        return $query->where("fuel_type", $fuelType);
    }

    /**
     * Scope a query to only include vehicles by location.
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->where("current_location", $location);
    }

    /**
     * Scope a query to only include vehicles with expiring insurance.
     */
    public function scopeInsuranceExpiring($query, int $days = 30)
    {
        return $query
            ->whereNotNull("insurance_expiry")
            ->where("insurance_expiry", "<=", now()->addDays($days));
    }

    /**
     * Scope a query to only include vehicles with expiring licenses.
     */
    public function scopeLicenseExpiring($query, int $days = 30)
    {
        return $query
            ->whereNotNull("road_license_expiry")
            ->where("road_license_expiry", "<=", now()->addDays($days));
    }

    /**
     * Scope a query to only include vehicles due for service.
     */
    public function scopeServiceDue($query)
    {
        return $query
            ->whereNotNull("next_service_due")
            ->where("next_service_due", "<=", now());
    }

    /**
     * Update the vehicle's status.
     */
    public function updateStatus(string $status): bool
    {
        $validStatuses = [
            "Available",
            "On Trip",
            "In Maintenance",
            "Decommissioned",
        ];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->update(["status" => $status]);
    }

    /**
     * Update the vehicle's mileage.
     */
    public function updateMileage(int $mileage): bool
    {
        if ($mileage < $this->current_mileage) {
            return false;
        }

        return $this->update(["current_mileage" => $mileage]);
    }

    /**
     * Schedule maintenance for the vehicle.
     */
    public function scheduleMaintenance(
        array $maintenanceData
    ): MaintenanceRecord {
        return $this->maintenanceRecords()->create($maintenanceData);
    }

    /**
     * Assign a driver to the vehicle.
     */
    public function assignDriver(Driver $driver): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $driver->update(["assigned_vehicle_id" => $this->id]);
        return true;
    }

    /**
     * Unassign the current driver from the vehicle.
     */
    public function unassignDriver(): bool
    {
        if ($this->currentDriver) {
            $this->currentDriver->update(["assigned_vehicle_id" => null]);
            return true;
        }

        return false;
    }

    /**
     * Get alerts for the vehicle.
     */
    public function getAlertsAttribute(): array
    {
        $alerts = [];

        if ($this->isInsuranceExpiring()) {
            $alerts[] = [
                "type" => "warning",
                "message" =>
                    "Insurance expiring on " .
                    $this->insurance_expiry->format("Y-m-d"),
            ];
        }

        if ($this->isLicenseExpiring()) {
            $alerts[] = [
                "type" => "warning",
                "message" =>
                    "License expiring on " .
                    $this->road_license_expiry->format("Y-m-d"),
            ];
        }

        if ($this->isServiceDue()) {
            $alerts[] = [
                "type" => "danger",
                "message" =>
                    "Service overdue since " .
                    $this->next_service_due->format("Y-m-d"),
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
     * Get the vehicle's utilization rate.
     */
    public function getUtilizationRateAttribute(): float
    {
        $totalDays = 30; // Last 30 days
        $tripsInPeriod = $this->trips()
            ->where("created_at", ">=", now()->subDays($totalDays))
            ->count();

        return ($tripsInPeriod / $totalDays) * 100;
    }

    /**
     * Calculate the vehicle's depreciation.
     */
    public function calculateDepreciation(): void
    {
        if (!$this->amount || !$this->depreciation_rate) {
            return;
        }

        $yearsOld = $this->getAgeAttribute();
        $annualDepreciation = $this->amount * ($this->depreciation_rate / 100);
        $accumulatedDepreciation = $annualDepreciation * $yearsOld;
        $netBookValue = max(0, $this->amount - $accumulatedDepreciation);

        $this->update([
            "annual_depreciation" => $annualDepreciation,
            "accumulated_depreciation" => $accumulatedDepreciation,
            "net_book_value" => $netBookValue,
        ]);
    }
}
