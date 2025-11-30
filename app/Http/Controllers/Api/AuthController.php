<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Mail\VerifyEmailCode;
use App\Helpers\OtpHelper;


class AuthController extends Controller
{
    // 📝 Register new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'email'          => 'required|string|email|unique:users,email',
            'password'       => 'required|string|min:6',
            'referral_code'  => 'nullable|string|exists:users,referral_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Check for referrer
        $referrer = null;
        if (!empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', $validated['referral_code'])->first();
        }

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->referral_code = strtoupper(Str::random(8));
        if ($referrer) {
            $user->referred_by = $referrer->referral_code;
        }
        $user->points = 0;
        
        $user->save();
   
        $user->makeHidden(['device_id']);

        return response()->json([
            'message' => 'Registration successful',
            'user'    => $user,
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code'  => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($user->email_verification_expires_at < now()) {
            return response()->json([
                'status' => false,
                'message' => 'Verification code expired',
            ], 410);
        }

        if ($user->email_verification_code != $request->code) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid verification code',
            ], 422);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->email_verification_expires_at = null;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Email verified successfully',
        ], 200);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Email already verified',
            ], 400);
        }

        $otp = OtpHelper::generate(
            $user,
            'email_verification_code',
            'email_verification_expires_at'
        );

        \Mail::to($user->email)->queue(new \App\Mail\VerifyEmailCode($otp));

        return response()->json([
            'status'  => true,
            'message' => 'Verification code resent',
        ], 200);
    }

    // 🔐 Login existing user (Passport)
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $validator->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        $user = Auth::user();


        $user->device_id = hash('sha256', $request->device_id); // ✔ HASHED
        $user->save();

        // Generate Passport token
        $token = $user->createToken('auth_token')->accessToken;

        $user->makeHidden(['device_id']);

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token,
        ], 200);
    }

    // 🚪 Logout user
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    // 🧾 Update user profile (name, email, password)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ], 200);
    }



    // 👤 Get authenticated user info
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->makeHidden(['device_id']),
        ], 200);
    }
    // 🗑️ Delete user account
   public function deleteAccount(Request $request)
    {
        $user = $request->user();

        \Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/delete/api_delete.log'),
        ])->info('API user account deleted', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'points'    => $user->points,
            'deleted_at' => now()->toDateTimeString(),
            'ip_address' => $request->ip(),
        ]);

        // Revoke all tokens before deletion
        $user->tokens()->delete();

        // Delete the user
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ], 200);
    }
}