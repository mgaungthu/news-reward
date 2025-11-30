<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordCode;
use App\Helpers\OtpHelper;

class resetPasswordController extends Controller
{
    public function forgotPassword(Request $request)
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

        $otp = OtpHelper::generate(
            $user,
            'password_reset_code',
            'password_reset_expires_at'
        );

        Mail::to($user->email)->queue(new ResetPasswordCode($otp));

        return response()->json([
            'status' => true,
            'message' => 'Password reset code sent to your email.',
        ], 200);
    }

    public function verifyResetOtp(Request $request)
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
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->password_reset_expires_at < now()) {
            return response()->json([
                'status' => false,
                'message' => 'Reset code expired.',
            ], 410);
        }

        if ($user->password_reset_code != $request->code) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid verification code.',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Reset code verified.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'        => 'required|email',
            'code'         => 'required|digits:6',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->password_reset_code != $request->code) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or reset code.',
            ], 422);
        }

        if ($user->password_reset_expires_at < now()) {
            return response()->json([
                'status' => false,
                'message' => 'Reset code expired.',
            ], 410);
        }

        $user->password = Hash::make($request->new_password);
        $user->password_reset_code = null;
        $user->password_reset_expires_at = null;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password has been successfully reset.',
        ], 200);
    }
}
