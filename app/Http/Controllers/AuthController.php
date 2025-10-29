<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    // Register user
    public function register(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);

        return response()->json([
            'message'=>'User registered successfully',
            'user'=>$user
        ]);
    }

    // Login user
    public function login(Request $request)
    {
        $credentials = $request->only('email','password');

        if(!$token = JWTAuth::attempt($credentials)){
            return response()->json(['error'=>'Invalid credentials'], 401);
        }

        return response()->json([
            'message'=>'Login successful',
            'token'=>$token,
            'user'=>auth()->user()
        ]);
    }

    // Forgot Password - generate token
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(60);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // For testing, return token directly instead of sending email
        // Later you can uncomment Mail::raw for actual email sending
        /*
        Mail::raw("Use this token to reset your password: $token", function($message) use ($request) {
            $message->to($request->email)
                ->subject('Password Reset Request');
        });
        */

        return response()->json([
            'message' => 'Password reset token generated successfully',
            'token' => $token // Return token for testing in Postman
        ]);
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return response()->json(['error' => 'Invalid token or email'], 400);
        }

        // Verify token
        if (!Hash::check($request->token, $passwordReset->token)) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully']);
    }
    // Change Password (for logged-in user)
   public function changePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:6|confirmed',
    ]);

    try {
        $user = User::find(auth()->user()->id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully.'], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong while changing password.',
            'details' => $e->getMessage(),
        ], 500);
    }
}

}