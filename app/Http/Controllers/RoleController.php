<?php

namespace App\Http\Controllers;

use App\Actions\CreateRole;
use App\Actions\UpdateRole;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Support\TenantAssignableRole;
use App\Support\TenantPermissionLabel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        $x['title'] = 'Roles';
        $x['data'] = Role::with('permissions')->orderBy('name')->get();
        $x['permission'] = $permissions;
        $x['permissionGroups'] = $permissions->groupBy(
            fn (Permission $permission) => TenantPermissionLabel::groupKey($permission->name)
        );

        return view('admin.role', $x);
    }

    public function store(StoreRoleRequest $request, CreateRole $createRole): RedirectResponse
    {
        if (TenantAssignableRole::isProtected($request->validated('name'))) {
            abort(403, 'No se puede crear el rol superadmin.');
        }

        try {
            $role = $createRole->execute($request->validated());
            Alert::success('Listo', 'Rol '.$role->name.' creado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo crear el rol', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function show(Request $request): RoleResource
    {
        $role = Role::with('permissions')->findOrFail($request->id);

        return (new RoleResource($role))->includePreviouslyLoadedRelationships();
    }

    public function update(UpdateRoleRequest $request, UpdateRole $updateRole): RedirectResponse
    {
        $role = Role::find($request->validated('id'));
        $this->denyProtectedRole($role);
        if (TenantAssignableRole::isProtected($request->validated('name'))) {
            abort(403, 'No se puede renombrar un rol a superadmin.');
        }

        try {
            $role = $updateRole->execute($role, $request->validated());
            Alert::success('Listo', 'Rol '.$role->name.' guardado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo guardar el rol', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        $role = Role::find($request->id);
        $this->denyProtectedRole($role);

        try {
            $name = $role->name;
            $role->delete();
            Alert::success('Listo', 'Rol '.$name.' eliminado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo eliminar el rol', 'Intentá de nuevo.')->toToast();
        }

        return back();
    }

    private function denyProtectedRole(?Role $role): void
    {
        if (! $role) {
            abort(404);
        }

        if (TenantAssignableRole::isProtected($role->name)) {
            abort(403, 'No se puede modificar el rol superadmin.');
        }
    }
}
