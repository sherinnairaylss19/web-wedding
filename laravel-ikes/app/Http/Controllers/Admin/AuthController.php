<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() { return view('admin.login'); }

    public function login(Request $request)
    {
        $c = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (!Auth::attempt($c, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Email atau password salah. Silakan isi ulang.']);
        }
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
