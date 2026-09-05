<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SetAdminPassword;
use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminPasswordRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class SetupPasswordController extends Controller
{
    public function show(Request $request)
    {
        $user = $this->adminUser();

        if (! $user) {
            $tenant = tenant();

            if ($tenant instanceof Tenant) {
                return response()->view('tenant.pending', ['tenant' => $tenant], 503);
            }

            return redirect()->route('login');
        }

        if (! $user->must_change_password) {
            return redirect()->route('login')->with(
                'status',
                'Ya definiste tu contraseña. Ingresá con tu usuario.'
            );
        }

        return view('auth.setup-password', [
            'user' => $user,
            'actionUrl' => $request->fullUrl(),
        ]);
    }

    public function store(StoreAdminPasswordRequest $request, SetAdminPassword $setAdminPassword)
    {
        $user = $this->adminUser();

        if (! $user) {
            return redirect()->route('login')->with(
                'error',
                'Todavía estamos preparando la cuenta. Probá de nuevo en unos minutos.'
            );
        }

        try {
            $setAdminPassword->execute($user, $request->validated('password'));
        } catch (BusinessRuleException $e) {
            return redirect()->route('login')->with('status', $e->getMessage());
        }

        $user->refresh();
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('password_hash_web', $user->getAuthPassword());

        Alert::success('Listo', 'Tu contraseña quedó definida.')->toToast();

        return redirect()->route('dashboard');
    }

    private function adminUser(): ?User
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant || ! $tenant->admin_email) {
            return null;
        }

        return User::where('email', $tenant->admin_email)->first();
    }
}
