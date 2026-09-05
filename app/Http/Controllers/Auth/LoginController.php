<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Facades\Alert;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user instanceof User && $user->must_change_password) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => ['Definí tu contraseña con el enlace que te mandamos por mail.'],
            ]);
        }

        $request->session()->put('password_hash_web', $user->getAuthPassword());
        Alert::info('Hola, '.$user->name)->toToast();

        return to_route('dashboard');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $user = User::where('email', $request->input($this->username()))->first();

        if ($user?->must_change_password) {
            throw ValidationException::withMessages([
                $this->username() => ['Definí tu contraseña con el enlace que te mandamos por mail.'],
            ]);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}
