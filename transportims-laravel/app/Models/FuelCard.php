<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class FuelCard extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "card_number",
        "card_type",
        "provider",
        "pin_code",
        "driver_id",
        "vehicle_id",
        "assigned_by",
        "current_balance",
        "fuel_balance",
        "daily_limit",
        "weekly_limit",
        "monthly_limit",
        "transaction_limit",
        "daily_usage",
        "weekly_usage",
        "monthly_usage",
        "total_usage",
        "transaction_count",
        "daily_reset_date",
        "weekly_reset_date",
        "monthly_reset_date",
        "status",
        "issue_date",
        "expiry_date",
        "activation_date",
        "deactivation_date",
        "allowed_fuel_types",
        "premium_fuel_allowed",
        "diesel_allowed",
        "petrol_allowed",
        "allowed_locations",
        "restricted_locations",
        "usage_start_time",
        "usage_end_time",
        "allowed_days",
        "weekend_usage_allowed",
        "emergency_override",
        "emergency_limit",
        "emergency_reason",
        "emergency_expires_at",
        "pin_required",
        "odometer_required",
        "driver_id_required",
        "failed_attempts",
        "locked_until",
        "cost_center",
        "budget_code",
        "department",
        "allocated_budget",
        "consumed_budget",
        "average_fuel_price",
        "total_fuel_cost",
        "fuel_efficiency",
        "stations_used",
        "savings_achieved",
        "last_transaction_date",
        "last_station",
        "last_transaction_amount",
        "last_fuel_price",
        "replacement_requested",
        "replacement_reason",
        "replacement_date",
        "replaced_by_card",
        "low_balance_alert",
        "low_balance_threshold",
        "expiry_alert",
        "expiry_alert_days",
        "limit_exceeded_alert",
        "notes",
        "restrictions_notes",
        "audit_trail",
        "temporary_card",
        "temporary_expires_at",
        "external_card_id",
        "external_system",
        "last_sync_date",
        "sync_status",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "current_balance" => "decimal:2",
        "fuel_balance" => "decimal:2",
        "daily_limit" => "decimal:2",
        "weekly_limit" => "decimal:2",
        "monthly_limit" => "decimal:2",
        "transaction_limit" => "decimal:2",
        "daily_usage" => "decimal:2",
        "weekly_usage" => "decimal:2",
        "monthly_usage" => "decimal:2",
        "total_usage" => "decimal:2",
        "transaction_count" => "integer",
        "daily_reset_date" => "date",
        "weekly_reset_date" => "date",
        "monthly_reset_date" => "date",
        "issue_date" => "date",
        "expiry_date" => "date",
        "activation_date" => "date",
        "deactivation_date" => "date",
        "allowed_fuel_types" => "json",
        "premium_fuel_allowed" => "boolean",
        "diesel_allowed" => "boolean",
        "petrol_allowed" => "boolean",
        "allowed_locations" => "json",
        "restricted_locations" => "json",
        "usage_start_time" => "datetime:H:i",
        "usage_end_time" => "datetime:H:i",
        "allowed_days" => "json",
        "weekend_usage_allowed" => "boolean",
        "emergency_override" => "boolean",
        "emergency_limit" => "decimal:2",
        "emergency_expires_at" => "datetime",
        "pin_required" => "boolean",
        "odometer_required" => "boolean",
        "driver_id_required" => "boolean",
        "failed_attempts" => "integer",
        "locked_until" => "datetime",
        "allocated_budget" => "decimal:2",
        "consumed_budget" => "decimal:2",
        "average_fuel_price" => "decimal:3",
        "total_fuel_cost" => "decimal:2",
        "fuel_efficiency" => "decimal:2",
        "stations_used" => "integer",
        "savings_achieved" => "decimal:2",
        "last_transaction_date" => "datetime",
        "last_transaction_amount" => "decimal:2",
        "last_fuel_price" => "decimal:3",
        "replacement_requested" => "boolean",
        "replacement_date" => "datetime",
        "low_balance_alert" => "boolean",
        "low_balance_threshold" => "decimal:2",
        "expiry_alert" => "boolean",
        "expiry_alert_days" => "integer",
        "limit_exceeded_alert" => "boolean",
        "audit_trail" => "json",
        "temporary_card" => "boolean",
        "temporary_expires_at" => "datetime",
        "last_sync_date" => "datetime",
        "sync_status" => "json",
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        "daily_reset_date",
        "weekly_reset_date",
        "monthly_reset_date",
        "issue_date",
        "expiry_date",
        "activation_date",
        "deactivation_date",
        "emergency_expires_at",
        "locked_until",
        "last_transaction_date",
        "replacement_date",
        "temporary_expires_at",
        "last_sync_date",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * Get the driver assigned to this fuel card.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the vehicle assigned to this fuel card.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who assigned this fuel card.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "assigned_by");
    }

    /**
     * Get all fuel transactions for this card.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    /**
     * Get recent fuel transactions for this card.
     */
    public function recentTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class)->latest()->limit(10);
    }

    /**
     * Check if the fuel card is active.
     */
    public function isActive(): bool
    {
        return $this->status === "Active";
    }

    /**
     * Check if the fuel card is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === "Inactive";
    }

    /**
     * Check if the fuel card is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === "Suspended";
    }

    /**
     * Check if the fuel card is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === "Expired" ||
            ($this->expiry_date && $this->expiry_date->isPast());
    }

    /**
     * Check if the fuel card is lost.
     */
    public function isLost(): bool
    {
        return $this->status === "Lost";
    }

    /**
     * Check if the fuel card is stolen.
     */
    public function isStolen(): bool
    {
        return $this->status === "Stolen";
    }

    /**
     * Check if the fuel card is locked due to failed attempts.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if the fuel card is expiring soon.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Check if the balance is low.
     */
    public function isLowBalance(): bool
    {
        return $this->current_balance <= $this->low_balance_threshold;
    }

    /**
     * Check if daily limit is exceeded.
     */
    public function isDailyLimitExceeded(): bool
    {
        return $this->daily_usage >= $this->daily_limit;
    }

    /**
     * Check if weekly limit is exceeded.
     */
    public function isWeeklyLimitExceeded(): bool
    {
        return $this->weekly_usage >= $this->weekly_limit;
    }

    /**
     * Check if monthly limit is exceeded.
     */
    public function isMonthlyLimitExceeded(): bool
    {
        return $this->monthly_usage >= $this->monthly_limit;
    }

    /**
     * Check if usage is allowed at current time.
     */
    public function isUsageAllowedNow(): bool
    {
        $currentTime = now()->format("H:i");
        $currentDay = now()->format("l");

        // Check time restrictions
        if ($this->usage_start_time && $this->usage_end_time) {
            $startTime = $this->usage_start_time->format("H:i");
            $endTime = $this->usage_end_time->format("H:i");

            if ($currentTime < $startTime || $currentTime > $endTime) {
                return false;
            }
        }

        // Check day restrictions
        if (
            $this->allowed_days &&
            !in_array($currentDay, $this->allowed_days)
        ) {
            return false;
        }

        // Check weekend restrictions
        if (
            !$this->weekend_usage_allowed &&
            in_array($currentDay, ["Saturday", "Sunday"])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if fuel type is allowed.
     */
    public function isFuelTypeAllowed(string $fuelType): bool
    {
        if ($this->allowed_fuel_types) {
            return in_array($fuelType, $this->allowed_fuel_types);
        }

        return match (strtolower($fuelType)) {
            "petrol", "gasoline" => $this->petrol_allowed,
            "diesel" => $this->diesel_allowed,
            "premium" => $this->premium_fuel_allowed,
            default => false,
        };
    }

    /**
     * Check if location is allowed.
     */
    public function isLocationAllowed(string $location): bool
    {
        // Check restricted locations
        if (
            $this->restricted_locations &&
            in_array($location, $this->restricted_locations)
        ) {
            return false;
        }

        // Check allowed locations (if specified)
        if ($this->allowed_locations) {
            return in_array($location, $this->allowed_locations);
        }

        return true;
    }

    /**
     * Get remaining daily limit.
     */
    public function getRemainingDailyLimitAttribute(): float
    {
        return max(0, $this->daily_limit - $this->daily_usage);
    }

    /**
     * Get remaining weekly limit.
     */
    public function getRemainingWeeklyLimitAttribute(): float
    {
        return max(0, $this->weekly_limit - $this->weekly_usage);
    }

    /**
     * Get remaining monthly limit.
     */
    public function getRemainingMonthlyLimitAttribute(): float
    {
        return max(0, $this->monthly_limit - $this->monthly_usage);
    }

    /**
     * Get usage percentage for daily limit.
     */
    public function getDailyUsagePercentageAttribute(): float
    {
        if ($this->daily_limit == 0) {
            return 0;
        }

        return min(100, ($this->daily_usage / $this->daily_limit) * 100);
    }

    /**
     * Get usage percentage for monthly limit.
     */
    public function getMonthlyUsagePercentageAttribute(): float
    {
        if ($this->monthly_limit == 0) {
            return 0;
        }

        return min(100, ($this->monthly_usage / $this->monthly_limit) * 100);
    }

    /**
     * Get fuel card status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            "Active" => "green",
            "Inactive" => "gray",
            "Suspended" => "yellow",
            "Expired" => "red",
            "Lost" => "red",
            "Stolen" => "red",
            "Damaged" => "orange",
            default => "gray",
        };
    }

    /**
     * Scope a query to only include active fuel cards.
     */
    public function scopeActive($query)
    {
        return $query->where("status", "Active");
    }

    /**
     * Scope a query to only include inactive fuel cards.
     */
    public function scopeInactive($query)
    {
        return $query->where("status", "Inactive");
    }

    /**
     * Scope a query to only include expired fuel cards.
     */
    public function scopeExpired($query)
    {
        return $query
            ->where("status", "Expired")
            ->orWhere("expiry_date", "<", now());
    }

    /**
     * Scope a query to only include expiring fuel cards.
     */
    public function scopeExpiring($query, int $days = 30)
    {
        return $query
            ->whereNotNull("expiry_date")
            ->where("expiry_date", "<=", now()->addDays($days))
            ->where("expiry_date", ">", now());
    }

    /**
     * Scope a query to only include low balance fuel cards.
     */
    public function scopeLowBalance($query)
    {
        return $query->whereRaw("current_balance <= low_balance_threshold");
    }

    /**
     * Scope a query to only include cards assigned to a specific driver.
     */
    public function scopeForDriver($query, $driverId)
    {
        return $query->where("driver_id", $driverId);
    }

    /**
     * Scope a query to only include cards assigned to a specific vehicle.
     */
    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where("vehicle_id", $vehicleId);
    }

    /**
     * Scope a query to only include temporary cards.
     */
    public function scopeTemporary($query)
    {
        return $query->where("temporary_card", true);
    }

    /**
     * Update the fuel card status.
     */
    public function updateStatus(string $status): bool
    {
        $validStatuses = [
            "Active",
            "Inactive",
            "Suspended",
            "Expired",
            "Lost",
            "Stolen",
            "Damaged",
        ];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $updateData = ["status" => $status];

        if ($status === "Inactive") {
            $updateData["deactivation_date"] = now();
        } elseif ($status === "Active" && $this->status !== "Active") {
            $updateData["activation_date"] = now();
        }

        return $this->update($updateData);
    }

    /**
     * Add fuel balance to the card.
     */
    public function addBalance(float $amount): bool
    {
        return $this->update([
            "current_balance" => $this->current_balance + $amount,
            "fuel_balance" => $this->fuel_balance + $amount,
        ]);
    }

    /**
     * Deduct fuel balance from the card.
     */
    public function deductBalance(float $amount): bool
    {
        if ($this->current_balance < $amount) {
            return false;
        }

        return $this->update([
            "current_balance" => $this->current_balance - $amount,
        ]);
    }

    /**
     * Record a fuel transaction.
     */
    public function recordTransaction(array $transactionData): bool
    {
        $amount = $transactionData["amount"] ?? 0;
        $fuelAmount = $transactionData["fuel_amount"] ?? 0;

        // Update usage counters
        $this->increment("transaction_count");
        $this->increment("daily_usage", $fuelAmount);
        $this->increment("weekly_usage", $fuelAmount);
        $this->increment("monthly_usage", $fuelAmount);
        $this->increment("total_usage", $fuelAmount);

        // Update cost tracking
        $this->increment("total_fuel_cost", $amount);
        $this->increment("consumed_budget", $amount);

        // Update last transaction info
        $this->update([
            "last_transaction_date" => now(),
            "last_station" => $transactionData["station"] ?? null,
            "last_transaction_amount" => $amount,
            "last_fuel_price" => $transactionData["price_per_liter"] ?? null,
        ]);

        // Update average fuel price
        if ($this->total_usage > 0) {
            $this->update([
                "average_fuel_price" =>
                    $this->total_fuel_cost / $this->total_usage,
            ]);
        }

        return true;
    }

    /**
     * Reset daily usage counters.
     */
    public function resetDailyUsage(): bool
    {
        return $this->update([
            "daily_usage" => 0,
            "daily_reset_date" => now(),
        ]);
    }

    /**
     * Reset weekly usage counters.
     */
    public function resetWeeklyUsage(): bool
    {
        return $this->update([
            "weekly_usage" => 0,
            "weekly_reset_date" => now(),
        ]);
    }

    /**
     * Reset monthly usage counters.
     */
    public function resetMonthlyUsage(): bool
    {
        return $this->update([
            "monthly_usage" => 0,
            "monthly_reset_date" => now(),
        ]);
    }

    /**
     * Block the fuel card.
     */
    public function block(string $reason = "Administrative action"): bool
    {
        return $this->update([
            "status" => "Suspended",
            "notes" => $this->notes . "\nBlocked: " . $reason . " at " . now(),
        ]);
    }

    /**
     * Unblock the fuel card.
     */
    public function unblock(): bool
    {
        return $this->update([
            "status" => "Active",
            "failed_attempts" => 0,
            "locked_until" => null,
        ]);
    }

    /**
     * Lock the card due to failed attempts.
     */
    public function lockCard(int $lockDurationMinutes = 15): bool
    {
        return $this->update([
            "locked_until" => now()->addMinutes($lockDurationMinutes),
        ]);
    }

    /**
     * Unlock the card.
     */
    public function unlockCard(): bool
    {
        return $this->update([
            "locked_until" => null,
            "failed_attempts" => 0,
        ]);
    }

    /**
     * Request replacement of the card.
     */
    public function requestReplacement(string $reason): bool
    {
        return $this->update([
            "replacement_requested" => true,
            "replacement_reason" => $reason,
            "replacement_date" => now(),
        ]);
    }

    /**
     * Get alerts for the fuel card.
     */
    public function getAlertsAttribute(): array
    {
        $alerts = [];

        if ($this->isLowBalance()) {
            $alerts[] = [
                "type" => "warning",
                "message" =>
                    "Low balance: " . $this->current_balance . " remaining",
            ];
        }

        if ($this->isExpiringSoon()) {
            $alerts[] = [
                "type" => "warning",
                "message" =>
                    "Card expiring on " . $this->expiry_date->format("Y-m-d"),
            ];
        }

        if ($this->isExpired()) {
            $alerts[] = [
                "type" => "danger",
                "message" =>
                    "Card expired on " . $this->expiry_date->format("Y-m-d"),
            ];
        }

        if ($this->isDailyLimitExceeded()) {
            $alerts[] = [
                "type" => "warning",
                "message" => "Daily limit exceeded",
            ];
        }

        if ($this->isMonthlyLimitExceeded()) {
            $alerts[] = [
                "type" => "warning",
                "message" => "Monthly limit exceeded",
            ];
        }

        if ($this->isLocked()) {
            $alerts[] = [
                "type" => "danger",
                "message" =>
                    "Card locked until " .
                    $this->locked_until->format("Y-m-d H:i"),
            ];
        }

        return $alerts;
    }

    /**
     * Generate a unique card number.
     */
    public static function generateCardNumber(): string
    {
        do {
            $cardNumber = "FC" . now()->format("Y") . rand(100000, 999999);
        } while (self::where("card_number", $cardNumber)->exists());

        return $cardNumber;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fuelCard) {
            if (!$fuelCard->card_number) {
                $fuelCard->card_number = self::generateCardNumber();
            }
            if (!$fuelCard->issue_date) {
                $fuelCard->issue_date = now();
            }
        });
    }
}
