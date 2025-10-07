<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $customClaims = [
            'email' => $user->email,
            'user_id' => $user->id,
            'login_time' => now()->timestamp,
        ];

        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return [
         //   'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    /**
     * Login user
     *
     * @param array $credentials
     * @return array|null
     */
    public function login(array $credentials): ?array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        $customClaims = [
            'email' => $user->email,
            'user_id' => $user->id,
            'login_time' => now()->timestamp,
        ];

        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    /**
     * Logout user (invalidate token)
     *
     * @return void
     */
    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    /**
     * Get authenticated user profile
     *
     * @return array
     */
    public function getProfile(): array
    {
        $user = JWTAuth::parseToken()->authenticate();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * Generate and send OTP for password reset
     *
     * @param string $email
     * @return void
     */
    public function sendPasswordResetOTP(string $email): void
    {
        // Check if user exists - but don't reveal this to the caller
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Silently fail - return success to prevent email enumeration
            return;
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in database with 15 minutes expiry
        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp' => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes(15),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Send OTP via email
        Mail::raw("Your password reset OTP is: {$otp}\n\nThis OTP will expire in 15 minutes.", function ($message) use ($email) {
            $message->to($email)
                    ->subject('Password Reset OTP');
        });
    }

    /**
     * Reset password using OTP
     *
     * @param array $data
     * @return bool
     */
    public function resetPassword(array $data): bool
    {
        // Get the most recent OTP for this email
        $otpRecord = DB::table('password_reset_otps')
            ->where('email', $data['email'])
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            return false;
        }

        // Verify OTP
        if (!Hash::check($data['otp'], $otpRecord->otp)) {
            return false;
        }

        // Update user password
        $user = User::where('email', $data['email'])->first();
        $user->password = Hash::make($data['password']);
        $user->save();

        // Delete used OTP
        DB::table('password_reset_otps')
            ->where('email', $data['email'])
            ->delete();

        return true;
    }
}
