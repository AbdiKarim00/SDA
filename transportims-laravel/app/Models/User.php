<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "email",
        "password",
        "phone",
        "employee_id",
        "department",
        "status",
        "last_login_at",
        "profile_photo_path",
        "email_verified_at",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
            "last_login_at" => "datetime",
        ];
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        "email_verified_at",
        "last_login_at",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * Get the driver profile associated with the user.
     */
    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    /**
     * Get the trips requested by the user.
     */
    public function requestedTrips(): HasMany
    {
        return $this->hasMany(Trip::class, "requested_by");
    }

    /**
     * Get the trips approved by the user.
     */
    public function approvedTrips(): HasMany
    {
        return $this->hasMany(Trip::class, "approved_by");
    }

    /**
     * Get the trips last updated by the user.
     */
    public function lastUpdatedTrips(): HasMany
    {
        return $this->hasMany(Trip::class, "last_updated_by");
    }

    /**
     * Get the maintenance records requested by the user.
     */
    public function requestedMaintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, "requested_by");
    }

    /**
     * Get the maintenance records approved by the user.
     */
    public function approvedMaintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, "approved_by");
    }

    /**
     * Get the maintenance records last updated by the user.
     */
    public function lastUpdatedMaintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, "last_updated_by");
    }

    /**
     * Get the incidents reported by the user.
     */
    public function reportedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, "reported_by");
    }

    /**
     * Get the incidents investigated by the user.
     */
    public function investigatedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, "investigated_by");
    }

    /**
     * Get the incidents reviewed by the user.
     */
    public function reviewedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, "reviewed_by");
    }

    /**
     * Get the incidents approved by the user.
     */
    public function approvedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, "approved_by");
    }

    /**
     * Get the incidents last updated by the user.
     */
    public function lastUpdatedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, "last_updated_by");
    }

    /**
     * Get the fuel cards assigned by the user.
     */
    public function assignedFuelCards(): HasMany
    {
        return $this->hasMany(FuelCard::class, "assigned_by");
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole("admin");
    }

    /**
     * Check if the user is logistics personnel.
     */
    public function isLogistics(): bool
    {
        return $this->hasRole("logistics");
    }

    /**
     * Check if the user is a driver.
     */
    public function isDriver(): bool
    {
        return $this->hasRole("driver");
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->status === "active";
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(" ", $this->name);
        $initials = "";
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        return $initials;
    }

    /**
     * Get the user's profile photo URL.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->profile_photo_path
            ? asset("storage/" . $this->profile_photo_path)
            : "https://ui-avatars.com/api/?name=" .
                    urlencode($this->name) .
                    "&color=7F9CF5&background=EBF4FF";
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where("status", "active");
    }

    /**
     * Scope a query to only include users with a specific role.
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->role($role);
    }

    /**
     * Update the user's last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(["last_login_at" => now()]);
    }

    /**
     * Get the user's dashboard route based on their role.
     */
    public function getDashboardRoute(): string
    {
        if ($this->isAdmin()) {
            return "admin.dashboard";
        } elseif ($this->isLogistics()) {
            return "logistics.dashboard";
        } elseif ($this->isDriver()) {
            return "driver.dashboard";
        }

        return "home";
    }

    /**
     * Check if the user can manage vehicles.
     */
    public function canManageVehicles(): bool
    {
        return $this->hasAnyRole(["admin", "logistics"]);
    }

    /**
     * Check if the user can manage trips.
     */
    public function canManageTrips(): bool
    {
        return $this->hasAnyRole(["admin", "logistics"]);
    }

    /**
     * Check if the user can view reports.
     */
    public function canViewReports(): bool
    {
        return $this->hasAnyRole(["admin", "logistics"]);
    }

    /**
     * Check if the user can manage drivers.
     */
    public function canManageDrivers(): bool
    {
        return $this->hasAnyRole(["admin", "logistics"]);
    }

    /**
     * Check if the user can manage fuel cards.
     */
    public function canManageFuelCards(): bool
    {
        return $this->hasAnyRole(["admin", "logistics"]);
    }

    /**
     * Check if the user can manage maintenance.
     */
    public function canManageMaintenance(): bool
    {
        return $this->hasAnyRole(["admin", "logistics"]);
    }

    /**
     * Check if the user can manage incidents.
     */
    public function canManageIncidents(): bool
    {
        return $this->hasAnyRole(["admin", "logistics"]);
    }
}
