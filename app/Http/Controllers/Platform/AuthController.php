<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('platform.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('platform')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->put(
                'password_hash_platform',
                Auth::guard('platform')->user()->getAuthPassword()
            );

            return redirect()->intended(route('platform.dashboard'));
        }

        return back()->withErrors(['email' => 'Ese correo o contraseña no coinciden.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('platform')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }
}
