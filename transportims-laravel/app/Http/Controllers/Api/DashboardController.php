<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\FuelCard;
use App\Models\MaintenanceRecord;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new DashboardController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get dashboard data based on user role.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->isAdmin()) {
                return $this->adminDashboard($request);
            } elseif ($user->isLogistics()) {
                return $this->logisticsDashboard($request);
            } elseif ($user->isDriver()) {
                return $this->driverDashboard($request);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get admin dashboard data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function adminDashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $data = [
                'overview' => $this->getAdminOverview(),
                'vehicle_stats' => $this->getVehicleStats(),
                'driver_stats' => $this->getDriverStats(),
                'trip_stats' => $this->getTripStats(),
                'financial_stats' => $this->getFinancialStats(),
                'alerts' => $this->getSystemAlerts(),
                'recent_activities' => $this->getRecentActivities(),
                'performance_metrics' => $this->getPerformanceMetrics(),
                'charts_data' => $this->getChartsData(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load admin dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get logistics dashboard data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logisticsDashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isLogistics()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $data = [
                'overview' => $this->getLogisticsOverview(),
                'pending_approvals' => $this->getPendingApprovals(),
                'vehicle_status' => $this->getVehicleStatusSummary(),
                'trip_management' => $this->getTripManagementData(),
                'maintenance_alerts' => $this->getMaintenanceAlerts(),
                'fuel_management' => $this->getFuelManagementData(),
                'driver_compliance' => $this->getDriverComplianceData(),
                'recent_trips' => $this->getRecentTrips(),
                'incidents_summary' => $this->getIncidentsSummary(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load logistics dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get driver dashboard data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function driverDashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isDriver()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $driver = $user->driver;
            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver profile not found'
                ], 404);
            }

            $data = [
                'driver_profile' => $this->getDriverProfile($driver),
                'current_trip' => $this->getCurrentTrip($driver),
                'assigned_vehicle' => $this->getAssignedVehicle($driver),
                'fuel_card' => $this->getFuelCardData($driver),
                'upcoming_trips' => $this->getUpcomingTrips($driver),
                'recent_trips' => $this->getDriverRecentTrips($driver),
                'compliance_status' => $this->getDriverComplianceStatus($driver),
                'performance_summary' => $this->getDriverPerformanceSummary($driver),
                'alerts' => $this->getDriverAlerts($driver),
                'maintenance_schedule' => $this->getVehicleMaintenanceSchedule($driver),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load driver dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get admin overview statistics.
     *
     * @return array
     */
    private function getAdminOverview(): array
    {
        return [
            'total_vehicles' => Vehicle::count(),
            'active_vehicles' => Vehicle::active()->count(),
            'total_drivers' => Driver::count(),
            'active_drivers' => Driver::active()->count(),
            'total_trips' => Trip::count(),
            'active_trips' => Trip::whereIn('status', ['Assigned', 'In Progress'])->count(),
            'total_incidents' => Incident::count(),
            'open_incidents' => Incident::whereNotIn('status', ['Resolved', 'Closed'])->count(),
            'maintenance_pending' => MaintenanceRecord::whereIn('status', ['Scheduled', 'Pending Parts'])->count(),
            'fuel_cards_active' => FuelCard::where('status', 'Active')->count(),
        ];
    }

    /**
     * Get vehicle statistics.
     *
     * @return array
     */
    private function getVehicleStats(): array
    {
        $vehicles = Vehicle::selectRaw('
            status,
            COUNT(*) as count,
            fuel_type,
            make
        ')->groupBy('status', 'fuel_type', 'make')->get();

        return [
            'by_status' => $vehicles->groupBy('status')->map(function ($items) {
                return $items->sum('count');
            }),
            'by_fuel_type' => $vehicles->groupBy('fuel_type')->map(function ($items) {
                return $items->sum('count');
            }),
            'by_make' => $vehicles->groupBy('make')->map(function ($items) {
                return $items->sum('count');
            }),
            'expiring_insurance' => Vehicle::insuranceExpiring()->count(),
            'expiring_licenses' => Vehicle::licenseExpiring()->count(),
            'service_due' => Vehicle::serviceDue()->count(),
        ];
    }

    /**
     * Get driver statistics.
     *
     * @return array
     */
    private function getDriverStats(): array
    {
        return [
            'by_status' => Driver::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'license_expiring' => Driver::licenseExpiring()->count(),
            'license_expired' => Driver::licenseExpired()->count(),
            'medical_cert_expiring' => Driver::medicalCertificateExpiring()->count(),
            'with_vehicles' => Driver::withVehicle()->count(),
            'without_vehicles' => Driver::withoutVehicle()->count(),
            'compliance_issues' => Driver::where(function ($query) {
                $query->where('background_check_completed', false)
                      ->orWhere('drug_test_passed', false)
                      ->orWhere('license_expiry', '<', now());
            })->count(),
        ];
    }

    /**
     * Get trip statistics.
     *
     * @return array
     */
    private function getTripStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        return [
            'today' => Trip::whereDate('created_at', $today)->count(),
            'this_month' => Trip::where('created_at', '>=', $thisMonth)->count(),
            'this_year' => Trip::where('created_at', '>=', $thisYear)->count(),
            'by_status' => Trip::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'by_priority' => Trip::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority'),
            'average_duration' => Trip::where('status', 'Completed')
                ->whereNotNull('actual_duration')
                ->avg('actual_duration'),
            'total_distance' => Trip::where('status', 'Completed')
                ->sum('actual_distance'),
        ];
    }

    /**
     * Get financial statistics.
     *
     * @return array
     */
    private function getFinancialStats(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        return [
            'maintenance_cost_month' => MaintenanceRecord::where('created_at', '>=', $thisMonth)
                ->where('status', 'Completed')
                ->sum('total_cost'),
            'maintenance_cost_year' => MaintenanceRecord::where('created_at', '>=', $thisYear)
                ->where('status', 'Completed')
                ->sum('total_cost'),
            'fuel_cost_month' => Trip::where('created_at', '>=', $thisMonth)
                ->where('status', 'Completed')
                ->sum('fuel_cost'),
            'fuel_cost_year' => Trip::where('created_at', '>=', $thisYear)
                ->where('status', 'Completed')
                ->sum('fuel_cost'),
            'incident_cost_month' => Incident::where('created_at', '>=', $thisMonth)
                ->sum('total_incident_cost'),
            'incident_cost_year' => Incident::where('created_at', '>=', $thisYear)
                ->sum('total_incident_cost'),
        ];
    }

    /**
     * Get system alerts.
     *
     * @return array
     */
    private function getSystemAlerts(): array
    {
        $alerts = [];

        // Vehicle alerts
        $vehicleAlerts = Vehicle::with('currentDriver')
            ->get()
            ->flatMap(function ($vehicle) {
                return collect($vehicle->alerts)->map(function ($alert) use ($vehicle) {
                    return array_merge($alert, [
                        'category' => 'vehicle',
                        'vehicle_id' => $vehicle->id,
                        'vehicle_registration' => $vehicle->registration_no,
                    ]);
                });
            });

        // Driver alerts
        $driverAlerts = Driver::with('user')
            ->get()
            ->flatMap(function ($driver) {
                return collect($driver->alerts)->map(function ($alert) use ($driver) {
                    return array_merge($alert, [
                        'category' => 'driver',
                        'driver_id' => $driver->id,
                        'driver_name' => $driver->full_name,
                    ]);
                });
            });

        return $vehicleAlerts->concat($driverAlerts)
            ->sortByDesc('type')
            ->take(20)
            ->values()
            ->all();
    }

    /**
     * Get recent activities.
     *
     * @return array
     */
    private function getRecentActivities(): array
    {
        $activities = [];

        // Recent trips
        $recentTrips = Trip::with(['driver.user', 'vehicle'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($trip) {
                return [
                    'type' => 'trip',
                    'action' => 'Trip ' . strtolower($trip->status),
                    'description' => "Trip #{$trip->trip_number} - {$trip->start_location} to {$trip->end_location}",
                    'user' => $trip->driver->user->name ?? 'Unknown',
                    'timestamp' => $trip->updated_at,
                ];
            });

        // Recent incidents
        $recentIncidents = Incident::with(['driver.user', 'vehicle'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($incident) {
                return [
                    'type' => 'incident',
                    'action' => 'Incident reported',
                    'description' => $incident->incident_title,
                    'user' => $incident->driver->user->name ?? 'Unknown',
                    'timestamp' => $incident->created_at,
                ];
            });

        // Recent maintenance
        $recentMaintenance = MaintenanceRecord::with(['vehicle'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($maintenance) {
                return [
                    'type' => 'maintenance',
                    'action' => 'Maintenance ' . strtolower($maintenance->status),
                    'description' => "Vehicle {$maintenance->vehicle->registration_no} - {$maintenance->description}",
                    'user' => 'System',
                    'timestamp' => $maintenance->updated_at,
                ];
            });

        return collect()
            ->concat($recentTrips)
            ->concat($recentIncidents)
            ->concat($recentMaintenance)
            ->sortByDesc('timestamp')
            ->take(20)
            ->values()
            ->all();
    }

    /**
     * Get performance metrics.
     *
     * @return array
     */
    private function getPerformanceMetrics(): array
    {
        $completedTrips = Trip::where('status', 'Completed')
            ->whereNotNull('actual_duration')
            ->whereNotNull('fuel_efficiency');

        return [
            'average_fuel_efficiency' => $completedTrips->avg('fuel_efficiency'),
            'average_trip_duration' => $completedTrips->avg('actual_duration'),
            'on_time_percentage' => $this->calculateOnTimePercentage(),
            'vehicle_utilization' => $this->calculateVehicleUtilization(),
            'driver_performance' => $this->getDriverPerformanceOverview(),
        ];
    }

    /**
     * Get charts data for admin dashboard.
     *
     * @return array
     */
    private function getChartsData(): array
    {
        return [
            'trips_by_month' => $this->getTripsChartData(),
            'fuel_consumption' => $this->getFuelConsumptionChartData(),
            'maintenance_costs' => $this->getMaintenanceCostChartData(),
            'incident_trends' => $this->getIncidentTrendsChartData(),
        ];
    }

    /**
     * Get logistics overview.
     *
     * @return array
     */
    private function getLogisticsOverview(): array
    {
        return [
            'pending_trip_approvals' => Trip::where('status', 'Requested')->count(),
            'active_trips' => Trip::whereIn('status', ['Assigned', 'In Progress'])->count(),
            'vehicles_available' => Vehicle::available()->count(),
            'drivers_available' => Driver::available()->count(),
            'maintenance_due' => Vehicle::serviceDue()->count(),
            'fuel_alerts' => FuelCard::where('current_balance', '<', 10)->count(),
        ];
    }

    /**
     * Get pending approvals for logistics.
     *
     * @return array
     */
    private function getPendingApprovals(): array
    {
        return [
            'trip_requests' => Trip::with(['driver.user', 'vehicle'])
                ->where('status', 'Requested')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($trip) {
                    return [
                        'id' => $trip->id,
                        'trip_number' => $trip->trip_number,
                        'driver' => $trip->driver->full_name ?? 'Unassigned',
                        'route' => $trip->start_location . ' → ' . $trip->end_location,
                        'scheduled_date' => $trip->scheduled_start_time,
                        'priority' => $trip->priority,
                        'requested_at' => $trip->created_at,
                    ];
                }),
            'maintenance_requests' => MaintenanceRecord::with(['vehicle'])
                ->where('status', 'Pending Approval')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($maintenance) {
                    return [
                        'id' => $maintenance->id,
                        'vehicle' => $maintenance->vehicle->registration_no,
                        'description' => $maintenance->description,
                        'estimated_cost' => $maintenance->estimated_cost,
                        'priority' => $maintenance->priority,
                        'requested_at' => $maintenance->created_at,
                    ];
                }),
        ];
    }

    /**
     * Get driver profile data.
     *
     * @param Driver $driver
     * @return array
     */
    private function getDriverProfile(Driver $driver): array
    {
        return [
            'id' => $driver->id,
            'name' => $driver->full_name,
            'license_number' => $driver->license_number,
            'license_expiry' => $driver->license_expiry,
            'status' => $driver->status,
            'compliance_status' => $driver->compliance_status,
            'performance_score' => $driver->performance_score,
            'total_trips' => $driver->total_trips,
            'total_distance' => $driver->total_distance,
            'incidents_count' => $driver->incidents_count,
            'employment_duration' => $driver->employment_duration,
        ];
    }

    /**
     * Get current trip for driver.
     *
     * @param Driver $driver
     * @return array|null
     */
    private function getCurrentTrip(Driver $driver): ?array
    {
        $currentTrip = $driver->activeTrips()->with(['vehicle'])->first();

        if (!$currentTrip) {
            return null;
        }

        return [
            'id' => $currentTrip->id,
            'trip_number' => $currentTrip->trip_number,
            'start_location' => $currentTrip->start_location,
            'end_location' => $currentTrip->end_location,
            'purpose' => $currentTrip->purpose,
            'status' => $currentTrip->status,
            'scheduled_start_time' => $currentTrip->scheduled_start_time,
            'actual_start_time' => $currentTrip->actual_start_time,
            'vehicle' => $currentTrip->vehicle ? [
                'id' => $currentTrip->vehicle->id,
                'registration_no' => $currentTrip->vehicle->registration_no,
                'make' => $currentTrip->vehicle->make,
                'model' => $currentTrip->vehicle->model,
            ] : null,
        ];
    }

    /**
     * Get assigned vehicle for driver.
     *
     * @param Driver $driver
     * @return array|null
     */
    private function getAssignedVehicle(Driver $driver): ?array
    {
        $vehicle = $driver->assignedVehicle;

        if (!$vehicle) {
            return null;
        }

        return [
            'id' => $vehicle->id,
            'registration_no' => $vehicle->registration_no,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'fuel_type' => $vehicle->fuel_type,
            'current_mileage' => $vehicle->current_mileage,
            'next_service_due' => $vehicle->next_service_due,
            'status' => $vehicle->status,
            'alerts' => $vehicle->alerts,
        ];
    }

    /**
     * Get fuel card data for driver.
     *
     * @param Driver $driver
     * @return array|null
     */
    private function getFuelCardData(Driver $driver): ?array
    {
        $fuelCard = $driver->fuelCard;

        if (!$fuelCard) {
            return null;
        }

        return [
            'id' => $fuelCard->id,
            'card_number' => $fuelCard->card_number,
            'current_balance' => $fuelCard->current_balance,
            'fuel_balance' => $fuelCard->fuel_balance,
            'daily_limit' => $fuelCard->daily_limit,
            'daily_usage' => $fuelCard->daily_usage,
            'status' => $fuelCard->status,
            'expiry_date' => $fuelCard->expiry_date,
        ];
    }

    /**
     * Calculate on-time percentage.
     *
     * @return float
     */
    private function calculateOnTimePercentage(): float
    {
        $totalCompletedTrips = Trip::where('status', 'Completed')->count();

        if ($totalCompletedTrips === 0) {
            return 0;
        }

        $onTimeTrips = Trip::where('status', 'Completed')
            ->where('delay_minutes', '<=', 15)
            ->count();

        return round(($onTimeTrips / $totalCompletedTrips) * 100, 2);
    }

    /**
     * Calculate vehicle utilization.
     *
     * @return float
     */
    private function calculateVehicleUtilization(): float
    {
        $totalVehicles = Vehicle::active()->count();

        if ($totalVehicles === 0) {
            return 0;
        }

        $utilizationData = Vehicle::active()
            ->get()
            ->map(function ($vehicle) {
                return $vehicle->utilization_rate;
            });

        return round($utilizationData->avg(), 2);
    }

    /**
     * Get driver performance overview.
     *
     * @return array
     */
    private function getDriverPerformanceOverview(): array
    {
        $drivers = Driver::active()->get();

        return [
            'average_performance_score' => $drivers->avg('performance_score'),
            'top_performers' => $drivers->sortByDesc('performance_score')->take(5)->map(function ($driver) {
                return [
                    'name' => $driver->full_name,
                    'score' => $driver->performance_score,
                ];
            })->values()->all(),
            'drivers_needing_improvement' => $drivers->where('performance_score', '<', 3)->count(),
        ];
    }

    /**
     * Get trips chart data.
     *
     * @return array
     */
    private function getTripsChartData(): array
    {
        $data = Trip::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->all();

        // Fill missing months with 0
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $data[$i] ?? 0;
        }

        return $chartData;
    }

    /**
     * Get fuel consumption chart data.
     *
     * @return array
     */
    private function getFuelConsumptionChartData(): array
    {
        return Trip::selectRaw('MONTH(created_at) as month, SUM(fuel_consumed) as total')
            ->whereYear('created_at', now()->year)
            ->where('status', 'Completed')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->all();
    }

    /**
     * Get maintenance cost chart data.
     *
     * @return array
     */
    private function getMaintenanceCostChartData(): array
    {
        return MaintenanceRecord::selectRaw('MONTH(created_at) as month, SUM(total_cost) as total')
            ->whereYear('created_at', now()->year)
            ->where('status', 'Completed')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->all();
    }

    /**
     * Get incident trends chart data.
     *
     * @return array
     */
    private function getIncidentTrendsChartData(): array
    {
        return Incident::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->all();
    }

    /**
     * Get upcoming trips for driver.
     *
     * @param Driver $driver
     * @return array
     */
    private function getUpcomingTrips(Driver $driver): array
    {
        return $driver->trips()
            ->with(['vehicle'])
            ->whereIn('status', ['Approved', 'Assigned'])
            ->where('scheduled_start_time', '>', now())
            ->orderBy('scheduled_start_time')
            ->take(5)
            ->get()
            ->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'trip_number' => $trip->trip_number,
                    'start_location' => $trip->start_location,
                    'end_location' => $trip->end_location,
                    'purpose' => $trip->purpose,
                    'scheduled_start_time' => $trip->scheduled_start_time,
                    'vehicle' => $trip->vehicle ? $trip->vehicle->registration_no : 'Not assigned',
                ];
            })
            ->all();
    }

    /**
     * Get recent trips for driver.
     *
     * @param Driver $driver
     * @return array
     */
    private function getDriverRecentTrips(Driver $driver): array
    {
        return $driver->completedTrips()
            ->with(['vehicle'])
            ->orderBy('actual_completion_date', 'desc')
            ->take(10)
            ->get()
            ->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'trip_number' => $trip->trip_number,
                    'start_location' => $trip->start_location,
                    'end_location' => $trip->end_location,
                    'actual_distance' => $trip->actual_distance,
                    'fuel_consumed' => $trip->fuel_consumed,
                    'actual_completion_date' => $trip->actual_completion_date,
                    'vehicle' => $trip->vehicle ? $trip->vehicle->registration_no : 'Unknown',
                ];
            })
            ->all();
    }

    /**
     * Get driver compliance status.
     *
     * @param Driver $driver
     * @return array
     */
    private function getDriverComplianceStatus(Driver $driver): array
    {
        return [
            'overall_status' => $driver->compliance_status,
            'license_valid' => !$driver->isLicenseExpired(),
            'license_expiry' => $driver->license_expiry,
            'medical_cert_valid' => !$driver->isMedicalCertificateExpired(),
            'medical_cert_expiry' => $driver->medical_certificate_expiry,
            'background_check' => $driver->background_check_completed,
            'drug_test' => $driver->drug_test_passed,
            'compliance_score' => $driver->isCompliant() ? 100 : 0,
        ];
    }

    /**
     * Get driver performance summary.
     *
     * @param Driver $driver
     * @return array
     */
    private function getDriverPerformanceSummary(Driver $driver): array
    {
        return [
            'overall_score' => $driver->performance_score,
            'safety_rating' => $driver->safety_rating,
            'fuel_efficiency_rating' => $driver->fuel_efficiency_rating,
            'incident_rate' => $driver->incident_rate,
            'monthly_trips' => $driver->monthly_trip_count,
            'yearly_trips' => $driver->yearly_trip_count,
            'average_fuel_efficiency' => $driver->average_fuel_efficiency,
        ];
    }

    /**
     * Get driver alerts.
     *
     * @param Driver $driver
     * @return array
     */
    private function getDriverAlerts(Driver $driver): array
    {
        return $driver->alerts;
    }

    /**
     * Get vehicle maintenance schedule for driver.
     *
     * @param Driver $driver
     * @return array
     */
    private function getVehicleMaintenanceSchedule(Driver $driver): array
    {
        $vehicle = $driver->assignedVehicle;

        if (!$vehicle) {
            return [];
        }

        return $vehicle->maintenanceRecords()
            ->whereIn('status', ['Scheduled', 'Pending Parts'])
            ->orderBy('scheduled_date')
            ->take(5)
            ->get()
            ->map(function ($maintenance) {
                return [
                    'id' => $maintenance->id,
                    'description' => $maintenance->description,
                    'scheduled_date' => $maintenance->scheduled_date,
                    'maintenance_type'
