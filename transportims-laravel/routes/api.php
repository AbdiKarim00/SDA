<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\FuelCardController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // System health check
    Route::get('health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now(),
            'version' => '1.0.0'
        ]);
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Authentication & Profile Management
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::get('sessions', [AuthController::class, 'sessions']);
        Route::delete('sessions/{tokenId}', [AuthController::class, 'revokeSession']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('admin', [DashboardController::class, 'adminDashboard'])->middleware('role:admin');
        Route::get('logistics', [DashboardController::class, 'logisticsDashboard'])->middleware('role:logistics');
        Route::get('driver', [DashboardController::class, 'driverDashboard'])->middleware('role:driver');
        Route::get('statistics', [DashboardController::class, 'getStatistics']);
        Route::get('alerts', [DashboardController::class, 'getAlerts']);
        Route::get('recent-activities', [DashboardController::class, 'getRecentActivities']);
    });

    // User Management (Admin only)
    Route::prefix('users')->middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('{user}', [UserController::class, 'show']);
        Route::put('{user}', [UserController::class, 'update']);
        Route::delete('{user}', [UserController::class, 'destroy']);
        Route::post('{user}/assign-role', [UserController::class, 'assignRole']);
        Route::post('{user}/revoke-role', [UserController::class, 'revokeRole']);
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::get('{user}/activity-log', [UserController::class, 'getActivityLog']);
    });

    // Vehicle Management
    Route::prefix('vehicles')->group(function () {
        Route::get('/', [VehicleController::class, 'index']);
        Route::post('/', [VehicleController::class, 'store'])->middleware('role:admin|logistics');
        Route::get('available', [VehicleController::class, 'getAvailableVehicles']);
        Route::get('statistics', [VehicleController::class, 'getStatistics']);
        Route::get('alerts', [VehicleController::class, 'getAlerts']);
        Route::get('export', [VehicleController::class, 'export']);

        Route::prefix('{vehicle}')->group(function () {
            Route::get('/', [VehicleController::class, 'show']);
            Route::put('/', [VehicleController::class, 'update'])->middleware('role:admin|logistics');
            Route::delete('/', [VehicleController::class, 'destroy'])->middleware('role:admin');
            Route::post('assign-driver', [VehicleController::class, 'assignDriver'])->middleware('role:admin|logistics');
            Route::post('unassign-driver', [VehicleController::class, 'unassignDriver'])->middleware('role:admin|logistics');
            Route::post('update-status', [VehicleController::class, 'updateStatus'])->middleware('role:admin|logistics');
            Route::post('update-mileage', [VehicleController::class, 'updateMileage']);
            Route::get('trips', [VehicleController::class, 'getTrips']);
            Route::get('maintenance-history', [VehicleController::class, 'getMaintenanceHistory']);
            Route::get('incidents', [VehicleController::class, 'getIncidents']);
            Route::get('fuel-consumption', [VehicleController::class, 'getFuelConsumption']);
            Route::get('utilization', [VehicleController::class, 'getUtilization']);
        });
    });

    // Driver Management
    Route::prefix('drivers')->group(function () {
        Route::get('/', [DriverController::class, 'index']);
        Route::post('/', [DriverController::class, 'store'])->middleware('role:admin|logistics');
        Route::get('available', [DriverController::class, 'getAvailableDrivers']);
        Route::get('statistics', [DriverController::class, 'getStatistics']);
        Route::get('compliance-report', [DriverController::class, 'getComplianceReport']);
        Route::get('performance-report', [DriverController::class, 'getPerformanceReport']);
        Route::get('export', [DriverController::class, 'export']);

        Route::prefix('{driver}')->group(function () {
            Route::get('/', [DriverController::class, 'show']);
            Route::put('/', [DriverController::class, 'update'])->middleware('role:admin|logistics');
            Route::delete('/', [DriverController::class, 'destroy'])->middleware('role:admin');
            Route::post('assign-vehicle', [DriverController::class, 'assignVehicle'])->middleware('role:admin|logistics');
            Route::post('unassign-vehicle', [DriverController::class, 'unassignVehicle'])->middleware('role:admin|logistics');
            Route::post('update-status', [DriverController::class, 'updateStatus'])->middleware('role:admin|logistics');
            Route::get('trips', [DriverController::class, 'getTrips']);
            Route::get('current-trip', [DriverController::class, 'getCurrentTrip']);
            Route::get('performance', [DriverController::class, 'getPerformance']);
            Route::get('compliance', [DriverController::class, 'getCompliance']);
            Route::get('incidents', [DriverController::class, 'getIncidents']);
            Route::post('update-certifications', [DriverController::class, 'updateCertifications'])->middleware('role:admin|logistics');
        });
    });

    // Trip Management
    Route::prefix('trips')->group(function () {
        Route::get('/', [TripController::class, 'index']);
        Route::post('/', [TripController::class, 'store']);
        Route::get('pending-approval', [TripController::class, 'getPendingApproval'])->middleware('role:admin|logistics');
        Route::get('active', [TripController::class, 'getActiveTrips']);
        Route::get('statistics', [TripController::class, 'getStatistics']);
        Route::get('export', [TripController::class, 'export']);

        Route::prefix('{trip}')->group(function () {
            Route::get('/', [TripController::class, 'show']);
            Route::put('/', [TripController::class, 'update']);
            Route::delete('/', [TripController::class, 'destroy'])->middleware('role:admin|logistics');
            Route::post('approve', [TripController::class, 'approve'])->middleware('role:admin|logistics');
            Route::post('reject', [TripController::class, 'reject'])->middleware('role:admin|logistics');
            Route::post('assign-driver', [TripController::class, 'assignDriver'])->middleware('role:admin|logistics');
            Route::post('assign-vehicle', [TripController::class, 'assignVehicle'])->middleware('role:admin|logistics');
            Route::post('start', [TripController::class, 'startTrip']);
            Route::post('complete', [TripController::class, 'completeTrip']);
            Route::post('cancel', [TripController::class, 'cancelTrip']);
            Route::post('update-location', [TripController::class, 'updateLocation']);
            Route::post('add-stop', [TripController::class, 'addStop']);
            Route::get('route', [TripController::class, 'getRoute']);
            Route::get('tracking', [TripController::class, 'getTracking']);
        });
    });

    // Fuel Card Management
    Route::prefix('fuel-cards')->group(function () {
        Route::get('/', [FuelCardController::class, 'index']);
        Route::post('/', [FuelCardController::class, 'store'])->middleware('role:admin|logistics');
        Route::get('statistics', [FuelCardController::class, 'getStatistics']);
        Route::get('low-balance', [FuelCardController::class, 'getLowBalanceCards']);
        Route::get('expiring', [FuelCardController::class, 'getExpiringCards']);
        Route::get('export', [FuelCardController::class, 'export']);

        Route::prefix('{fuelCard}')->group(function () {
            Route::get('/', [FuelCardController::class, 'show']);
            Route::put('/', [FuelCardController::class, 'update'])->middleware('role:admin|logistics');
            Route::delete('/', [FuelCardController::class, 'destroy'])->middleware('role:admin');
            Route::post('assign-driver', [FuelCardController::class, 'assignDriver'])->middleware('role:admin|logistics');
            Route::post('assign-vehicle', [FuelCardController::class, 'assignVehicle'])->middleware('role:admin|logistics');
            Route::post('update-balance', [FuelCardController::class, 'updateBalance'])->middleware('role:admin|logistics');
            Route::post('update-limits', [FuelCardController::class, 'updateLimits'])->middleware('role:admin|logistics');
            Route::post('block', [FuelCardController::class, 'blockCard'])->middleware('role:admin|logistics');
            Route::post('unblock', [FuelCardController::class, 'unblockCard'])->middleware('role:admin|logistics');
            Route::get('transactions', [FuelCardController::class, 'getTransactions']);
            Route::get('usage-history', [FuelCardController::class, 'getUsageHistory']);
        });
    });

    // Maintenance Management
    Route::prefix('maintenance')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index']);
        Route::post('/', [MaintenanceController::class, 'store']);
        Route::get('pending', [MaintenanceController::class, 'getPendingMaintenance']);
        Route::get('due', [MaintenanceController::class, 'getDueMaintenance']);
        Route::get('statistics', [MaintenanceController::class, 'getStatistics']);
        Route::get('cost-analysis', [MaintenanceController::class, 'getCostAnalysis']);
        Route::get('export', [MaintenanceController::class, 'export']);

        Route::prefix('{maintenance}')->group(function () {
            Route::get('/', [MaintenanceController::class, 'show']);
            Route::put('/', [MaintenanceController::class, 'update']);
            Route::delete('/', [MaintenanceController::class, 'destroy'])->middleware('role:admin');
            Route::post('approve', [MaintenanceController::class, 'approve'])->middleware('role:admin|logistics');
            Route::post('reject', [MaintenanceController::class, 'reject'])->middleware('role:admin|logistics');
            Route::post('start', [MaintenanceController::class, 'startMaintenance']);
            Route::post('complete', [MaintenanceController::class, 'completeMaintenance']);
            Route::post('add-parts', [MaintenanceController::class, 'addParts']);
            Route::post('update-cost', [MaintenanceController::class, 'updateCost']);
            Route::post('quality-check', [MaintenanceController::class, 'qualityCheck']);
            Route::get('photos', [MaintenanceController::class, 'getPhotos']);
            Route::post('photos', [MaintenanceController::class, 'uploadPhotos']);
        });
    });

    // Incident Management
    Route::prefix('incidents')->group(function () {
        Route::get('/', [IncidentController::class, 'index']);
        Route::post('/', [IncidentController::class, 'store']);
        Route::get('open', [IncidentController::class, 'getOpenIncidents']);
        Route::get('statistics', [IncidentController::class, 'getStatistics']);
        Route::get('trends', [IncidentController::class, 'getTrends']);
        Route::get('export', [IncidentController::class, 'export']);

        Route::prefix('{incident}')->group(function () {
            Route::get('/', [IncidentController::class, 'show']);
            Route::put('/', [IncidentController::class, 'update']);
            Route::delete('/', [IncidentController::class, 'destroy'])->middleware('role:admin');
            Route::post('assign-investigator', [IncidentController::class, 'assignInvestigator'])->middleware('role:admin|logistics');
            Route::post('update-status', [IncidentController::class, 'updateStatus'])->middleware('role:admin|logistics');
            Route::post('add-evidence', [IncidentController::class, 'addEvidence']);
            Route::post('add-witness', [IncidentController::class, 'addWitness']);
            Route::post('update-costs', [IncidentController::class, 'updateCosts']);
            Route::post('resolve', [IncidentController::class, 'resolveIncident'])->middleware('role:admin|logistics');
            Route::post('close', [IncidentController::class, 'closeIncident'])->middleware('role:admin|logistics');
            Route::get('timeline', [IncidentController::class, 'getTimeline']);
            Route::get('documents', [IncidentController::class, 'getDocuments']);
            Route::post('documents', [IncidentController::class, 'uploadDocuments']);
        });
    });

    // Reporting & Analytics
    Route::prefix('reports')->group(function () {
        Route::get('dashboard-summary', [ReportController::class, 'getDashboardSummary']);
        Route::get('vehicle-utilization', [ReportController::class, 'getVehicleUtilization']);
        Route::get('driver-performance', [ReportController::class, 'getDriverPerformance']);
        Route::get('trip-analysis', [ReportController::class, 'getTripAnalysis']);
        Route::get('fuel-consumption', [ReportController::class, 'getFuelConsumption']);
        Route::get('maintenance-costs', [ReportController::class, 'getMaintenanceCosts']);
        Route::get('incident-analysis', [ReportController::class, 'getIncidentAnalysis']);
        Route::get('compliance-status', [ReportController::class, 'getComplianceStatus']);
        Route::get('financial-summary', [ReportController::class, 'getFinancialSummary']);
        Route::get('operational-metrics', [ReportController::class, 'getOperationalMetrics']);

        // Export routes
        Route::prefix('export')->group(function () {
            Route::get('vehicles', [ReportController::class, 'exportVehicles']);
            Route::get('drivers', [ReportController::class, 'exportDrivers']);
            Route::get('trips', [ReportController::class, 'exportTrips']);
            Route::get('maintenance', [ReportController::class, 'exportMaintenance']);
            Route::get('incidents', [ReportController::class, 'exportIncidents']);
            Route::get('fuel-usage', [ReportController::class, 'exportFuelUsage']);
            Route::get('custom', [ReportController::class, 'exportCustomReport']);
        });
    });

    // File Upload & Management
    Route::prefix('files')->group(function () {
        Route::post('upload', [FileController::class, 'upload']);
        Route::get('{file}/download', [FileController::class, 'download']);
        Route::delete('{file}', [FileController::class, 'delete']);
        Route::get('{file}/preview', [FileController::class, 'preview']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread', [NotificationController::class, 'getUnread']);
        Route::post('{notification}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{notification}', [NotificationController::class, 'delete']);
        Route::get('settings', [NotificationController::class, 'getSettings']);
        Route::put('settings', [NotificationController::class, 'updateSettings']);
    });

    // System Settings (Admin only)
    Route::prefix('settings')->middleware('role:admin')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::put('/', [SettingsController::class, 'update']);
        Route::get('backup', [SettingsController::class, 'createBackup']);
        Route::post('restore', [SettingsController::class, 'restoreBackup']);
        Route::get('logs', [SettingsController::class, 'getLogs']);
        Route::get('system-info', [SettingsController::class, 'getSystemInfo']);
        Route::post('cache-clear', [SettingsController::class, 'clearCache']);
    });

    // Search & Filters
    Route::prefix('search')->group(function () {
        Route::get('vehicles', [SearchController::class, 'searchVehicles']);
        Route::get('drivers', [SearchController::class, 'searchDrivers']);
        Route::get('trips', [SearchController::class, 'searchTrips']);
        Route::get('maintenance', [SearchController::class, 'searchMaintenance']);
        Route::get('incidents', [SearchController::class, 'searchIncidents']);
        Route::get('global', [SearchController::class, 'globalSearch']);
    });

    // Mobile App Specific Routes
    Route::prefix('mobile')->group(function () {
        Route::get('driver-dashboard', [MobileController::class, 'getDriverDashboard']);
        Route::post('trip-update', [MobileController::class, 'updateTripStatus']);
        Route::post('location-update', [MobileController::class, 'updateLocation']);
        Route::post('incident-report', [MobileController::class, 'reportIncident']);
        Route::post('maintenance-request', [MobileController::class, 'requestMaintenance']);
        Route::get('offline-sync', [MobileController::class, 'getOfflineData']);
        Route::post('sync-data', [MobileController::class, 'syncData']);
    });

    // Test route for authenticated user
    Route::get('user', function (Request $request) {
        return $request->user();
    });
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});
