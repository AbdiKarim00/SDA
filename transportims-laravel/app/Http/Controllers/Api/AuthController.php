<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware("auth:sanctum")->except([
            "login",
            "register",
            "forgotPassword",
        ]);
    }

    /**
     * Handle user login and generate API token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        // Rate limiting
        $key = "login-attempts:" . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Too many login attempts. Please try again in " .
                        $seconds .
                        " seconds.",
                ],
                429
            );
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|string|min:6",
            "device_name" => "sometimes|string|max:255",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation failed",
                    "errors" => $validator->errors(),
                ],
                422
            );
        }

        // Attempt to authenticate user
        $user = User::where("email", $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key);
            return response()->json(
                [
                    "success" => false,
                    "message" => "Invalid credentials",
                ],
                401
            );
        }

        // Check if user is active
        if (!$user->isActive()) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Account is not active. Please contact administrator.",
                ],
                403
            );
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        // Update last login timestamp
        $user->updateLastLogin();

        // Generate token
        $deviceName = $request->device_name ?? "Unknown Device";
        $token = $user->createToken($deviceName);

        // Load user relationships
        $user->load(["roles", "permissions"]);

        // Load driver profile if user is a driver
        if ($user->isDriver()) {
            $user->load("driver.assignedVehicle");
        }

        return response()->json(
            [
                "success" => true,
                "message" => "Login successful",
                "data" => [
                    "user" => $this->formatUserResponse($user),
                    "token" => $token->plainTextToken,
                    "token_type" => "Bearer",
                    "expires_at" => null, // Sanctum tokens don't expire by default
                ],
            ],
            200
        );
    }

    /**
     * Handle user logout and revoke API token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke the current token
            $request->user()->currentAccessToken()->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Logout successful",
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Logout failed",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Revoke all tokens for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            // Revoke all tokens
            $request->user()->tokens()->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "All sessions logged out successfully",
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Logout all failed",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load(["roles", "permissions"]);

            // Load driver profile if user is a driver
            if ($user->isDriver()) {
                $user->load("driver.assignedVehicle");
            }

            return response()->json(
                [
                    "success" => true,
                    "data" => $this->formatUserResponse($user),
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to get profile",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                "name" => "sometimes|string|max:255",
                "phone" => "sometimes|string|max:20",
                "profile_photo" => "sometimes|image|max:2048", // 2MB max
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Validation failed",
                        "errors" => $validator->errors(),
                    ],
                    422
                );
            }

            $updateData = $request->only(["name", "phone"]);

            // Handle profile photo upload
            if ($request->hasFile("profile_photo")) {
                $path = $request
                    ->file("profile_photo")
                    ->store("profile-photos", "public");
                $updateData["profile_photo_path"] = $path;
            }

            $user->update($updateData);

            return response()->json(
                [
                    "success" => true,
                    "message" => "Profile updated successfully",
                    "data" => $this->formatUserResponse($user->fresh()),
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to update profile",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Change the authenticated user's password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                "current_password" => "required|string",
                "new_password" => [
                    "required",
                    "string",
                    Password::min(8)->mixedCase()->numbers()->symbols(),
                ],
                "new_password_confirmation" =>
                    "required|string|same:new_password",
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Validation failed",
                        "errors" => $validator->errors(),
                    ],
                    422
                );
            }

            $user = $request->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Current password is incorrect",
                    ],
                    400
                );
            }

            // Update password
            $user->update([
                "password" => Hash::make($request->new_password),
            ]);

            // Revoke all tokens except current one
            $currentToken = $request->user()->currentAccessToken();
            $user->tokens()->where("id", "!=", $currentToken->id)->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Password changed successfully",
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to change password",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Register a new user (Admin only).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Check if user has permission to create users
            if (!$request->user() || !$request->user()->canManageDrivers()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unauthorized to create users",
                    ],
                    403
                );
            }

            $validator = Validator::make($request->all(), [
                "name" => "required|string|max:255",
                "email" => "required|string|email|max:255|unique:users",
                "password" => [
                    "required",
                    "string",
                    Password::min(8)->mixedCase()->numbers()->symbols(),
                ],
                "phone" => "sometimes|string|max:20",
                "employee_id" => "sometimes|string|max:50",
                "department" => "sometimes|string|max:100",
                "role" => "required|string|in:admin,logistics,driver",
                "driver_data" => "sometimes|array", // Additional driver data if role is driver
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Validation failed",
                        "errors" => $validator->errors(),
                    ],
                    422
                );
            }

            // Create user
            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "phone" => $request->phone,
                "employee_id" => $request->employee_id,
                "department" => $request->department,
                "status" => "active",
                "email_verified_at" => now(),
            ]);

            // Assign role
            $user->assignRole($request->role);

            // Create driver profile if role is driver
            if ($request->role === "driver" && $request->has("driver_data")) {
                $driverData = $request->driver_data;
                $driverData["user_id"] = $user->id;
                Driver::create($driverData);
            }

            return response()->json(
                [
                    "success" => true,
                    "message" => "User created successfully",
                    "data" => $this->formatUserResponse(
                        $user->fresh(["roles", "permissions"])
                    ),
                ],
                201
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to create user",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Get all active sessions for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sessions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tokens = $user->tokens()->get();

            $sessions = $tokens->map(function ($token) {
                return [
                    "id" => $token->id,
                    "name" => $token->name,
                    "last_used_at" => $token->last_used_at,
                    "created_at" => $token->created_at,
                    "is_current" =>
                        $token->id ===
                        request()->user()->currentAccessToken()->id,
                ];
            });

            return response()->json(
                [
                    "success" => true,
                    "data" => $sessions,
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to get sessions",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Revoke a specific session.
     *
     * @param Request $request
     * @param string $tokenId
     * @return JsonResponse
     */
    public function revokeSession(
        Request $request,
        string $tokenId
    ): JsonResponse {
        try {
            $user = $request->user();
            $token = $user->tokens()->where("id", $tokenId)->first();

            if (!$token) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Session not found",
                    ],
                    404
                );
            }

            // Don't allow revoking current session
            if ($token->id === $request->user()->currentAccessToken()->id) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Cannot revoke current session",
                    ],
                    400
                );
            }

            $token->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Session revoked successfully",
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to revoke session",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Refresh the current token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentToken = $user->currentAccessToken();

            // Delete current token
            $currentToken->delete();

            // Create new token
            $deviceName = $currentToken->name ?? "Unknown Device";
            $newToken = $user->createToken($deviceName);

            return response()->json(
                [
                    "success" => true,
                    "message" => "Token refreshed successfully",
                    "data" => [
                        "token" => $newToken->plainTextToken,
                        "token_type" => "Bearer",
                        "expires_at" => null,
                    ],
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to refresh token",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Format user response data.
     *
     * @param User $user
     * @return array
     */
    private function formatUserResponse(User $user): array
    {
        $userData = [
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "phone" => $user->phone,
            "employee_id" => $user->employee_id,
            "department" => $user->department,
            "status" => $user->status,
            "profile_photo_url" => $user->profile_photo_url,
            "last_login_at" => $user->last_login_at,
            "roles" => $user->roles->pluck("name"),
            "permissions" => $user->getAllPermissions()->pluck("name"),
            "dashboard_route" => $user->getDashboardRoute(),
            "created_at" => $user->created_at,
            "updated_at" => $user->updated_at,
        ];

        // Add driver data if user is a driver
        if ($user->isDriver() && $user->driver) {
            $userData["driver"] = [
                "id" => $user->driver->id,
                "license_number" => $user->driver->license_number,
                "license_expiry" => $user->driver->license_expiry,
                "license_class" => $user->driver->license_class,
                "status" => $user->driver->status,
                "assigned_vehicle" => $user->driver->assignedVehicle
                    ? [
                        "id" => $user->driver->assignedVehicle->id,
                        "registration_no" =>
                            $user->driver->assignedVehicle->registration_no,
                        "make" => $user->driver->assignedVehicle->make,
                        "model" => $user->driver->assignedVehicle->model,
                        "status" => $user->driver->assignedVehicle->status,
                    ]
                    : null,
                "compliance_status" => $user->driver->compliance_status,
                "performance_score" => $user->driver->performance_score,
                "total_trips" => $user->driver->total_trips,
                "total_distance" => $user->driver->total_distance,
                "alerts" => $user->driver->alerts,
            ];
        }

        return $userData;
    }
}
