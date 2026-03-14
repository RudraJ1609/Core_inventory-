<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors('Invalid login credentials.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $otp = (string) random_int(100000, 999999);
        $hash = Hash::make($otp);

        DB::table('otp_resets')->where('email', $validated['email'])->delete();
        DB::table('otp_resets')->insert([
            'email' => $validated['email'],
            'code_hash' => $hash,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('OTP for password reset', ['email' => $validated['email'], 'otp' => $otp]);

        return redirect()->route('password.otp', ['email' => $validated['email']])
            ->with('status', 'OTP sent. Check logs for demo.');
    }

    public function showOtp(Request $request)
    {
        return view('auth.otp', ['email' => $request->query('email')]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $record = DB::table('otp_resets')
            ->where('email', $validated['email'])
            ->whereNull('used_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$record) {
            return back()->withErrors('OTP not found.');
        }

        if (now()->greaterThan($record->expires_at)) {
            return back()->withErrors('OTP expired.');
        }

        if (!Hash::check($validated['otp'], $record->code_hash)) {
            return back()->withErrors('Invalid OTP.');
        }

        User::where('email', $validated['email'])->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('otp_resets')->where('id', $record->id)->update([
            'used_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('login')->with('status', 'Password updated. Please login.');
    }
}
