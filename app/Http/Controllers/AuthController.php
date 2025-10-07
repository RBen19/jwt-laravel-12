<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\PasswordResetConfirmRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponse;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse($result, 'User registered successfully.', 201);
    }

    /**
     * Login user
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        return $this->successResponse($result, 'Login successful.');
    }

    /**
     * Logout user
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->successResponse(null, 'Successfully logged out.', 200);
    }

    /**
     * Get authenticated user profile
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $profile = $this->authService->getProfile();

        return $this->successResponse($profile, 'User profile retrieved successfully.');
    }

    /**
     * Send password reset OTP
     *
     * @param PasswordResetRequest $request
     * @return JsonResponse
     */
    public function sendPasswordResetOTP(PasswordResetRequest $request): JsonResponse
    {
        $this->authService->sendPasswordResetOTP($request->email);

        return $this->successResponse(null, 'Password reset OTP has been sent to your email.', 200);
    }

    /**
     * Reset password with OTP
     *
     * @param PasswordResetConfirmRequest $request
     * @return JsonResponse
     */
    public function resetPassword(PasswordResetConfirmRequest $request): JsonResponse
    {
        $result = $this->authService->resetPassword($request->validated());

        if (!$result) {
            return $this->errorResponse('Invalid or expired OTP.', 400);
        }

        return $this->successResponse(null, 'Password has been reset successfully.', 202);
    }
}
