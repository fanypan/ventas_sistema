<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('platform.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

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

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('platform')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }
}
