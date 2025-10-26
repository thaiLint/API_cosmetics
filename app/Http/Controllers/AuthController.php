<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Mail\PasswordResetCodeMail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Login user and return JWT token
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => auth()->user()
        ]);
    }

    /**
     * Generate password reset token
     */
public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|',
    ]);
    $user = User::where('email', $request->email)->first();
if (!$user) {
   
    return response()->json([
        'message' => 'If your email exists, a verification code has been sent.'
    ]);
}

    // Generate a 6-digit numeric code
    $code = rand(100000, 999999);

    // Store hashed code in password_resets table
    DB::table('password_resets')->updateOrInsert(
        ['email' => $request->email],
        [
            'token' => Hash::make((string)$code),
            'created_at' => now(),
        ]
    );

    // Send the plain code via email
    Mail::to($request->email)->send(new PasswordResetCodeMail($code));

    return response()->json([
        'message' => 'Verification code sent successfully to your email.'
    ]);
}


    /**
     * Reset password using token
     */
   public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'token' => 'required|string', // the 6-digit code user received
        'password' => 'required|string|min:6|confirmed',
    ]);

    // Fetch the reset record
    $passwordReset = DB::table('password_resets')
        ->where('email', $request->email)
        ->first();

    if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
        return response()->json(['error' => 'Invalid token or email'], 400);
    }

    // Update the user's password
    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    // Delete the used token
    DB::table('password_resets')->where('email', $request->email)->delete();

    return response()->json(['message' => 'Password reset successfully']);
}

    public function logout(Request $request)
{
    try {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => 'User logged out successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to logout, token invalid or missing'
        ], 500);
    }
}
}
