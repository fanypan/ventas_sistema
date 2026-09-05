<?php

namespace App\Http\Controllers\Platform;

use App\Actions\Platform\CreatePlatformRole;
use App\Actions\Platform\DeletePlatformRole;
use App\Actions\Platform\UpdatePlatformRole;
use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlatformRoleRequest;
use App\Http\Requests\Platform\UpdatePlatformRoleRequest;
use App\Support\PlatformAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::where('guard_name', PlatformAccess::GUARD)
            ->withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return view('platform.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('platform.roles.form', [
            'role' => null,
            'permissions' => PlatformAccess::permissions(),
            'selected' => old('permissions', []),
        ]);
    }

    public function store(StorePlatformRoleRequest $request, CreatePlatformRole $createRole): RedirectResponse
    {
        try {
            $createRole->execute($request->validated());
        } catch (BusinessRuleException $e) {
            Alert::error('No se pudo crear', $e->getMessage())->toToast();

            return back()->withInput();
        }

        Alert::success('Rol creado', 'Asignalo a un usuario del equipo.')->toToast();

        return redirect()->route('platform.roles.index');
    }

    public function edit(Role $role): View
    {
        abort_unless($role->guard_name === PlatformAccess::GUARD, 404);

        return view('platform.roles.form', [
            'role' => $role,
            'permissions' => PlatformAccess::permissions(),
            'selected' => old('permissions', $role->permissions->pluck('name')->all()),
        ]);
    }

    public function update(UpdatePlatformRoleRequest $request, Role $role, UpdatePlatformRole $updateRole): RedirectResponse
    {
        abort_unless($role->guard_name === PlatformAccess::GUARD, 404);

        try {
            $updateRole->execute($role, $request->validated());
        } catch (BusinessRuleException $e) {
            Alert::error('No se pudo guardar', $e->getMessage())->toToast();

            return back()->withInput();
        }

        Alert::success('Rol guardado', $role->name)->toToast();

        return redirect()->route('platform.roles.index');
    }

    public function destroy(Role $role, DeletePlatformRole $deleteRole): RedirectResponse
    {
        abort_unless($role->guard_name === PlatformAccess::GUARD, 404);

        try {
            $deleteRole->execute($role);
        } catch (BusinessRuleException $e) {
            Alert::error('No se pudo eliminar', $e->getMessage())->toToast();

            return back();
        }

        Alert::success('Eliminado', 'Se borró el rol.')->toToast();

        return redirect()->route('platform.roles.index');
    }
}
