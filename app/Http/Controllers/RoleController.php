<?php

namespace App\Http\Controllers;

use App\Actions\CreateRole;
use App\Actions\UpdateRole;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    private const PROTECTED_ROLE = 'superadmin';

    public function index()
    {
        $x['title'] = 'Role';
        $x['data'] = Role::with('permissions')->get();
        $x['permission'] = Permission::orderBy('id', 'desc')->get();

        return view('admin.role', $x);
    }

    public function store(StoreRoleRequest $request, CreateRole $createRole)
    {
        if ($this->isProtectedRoleName($request->validated('name'))) {
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

    public function show(Request $request)
    {
        $role = Role::with('permissions')->find($request->id);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data role by id',
            'data' => $role,
        ], Response::HTTP_OK);
    }

    public function update(UpdateRoleRequest $request, UpdateRole $updateRole)
    {
        $role = Role::find($request->validated('id'));
        $this->denyProtectedRole($role);
        if ($this->isProtectedRoleName($request->validated('name'))) {
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

    public function destroy(Request $request)
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

    private function isProtectedRoleName(?string $name): bool
    {
        return strtolower(trim((string) $name)) === self::PROTECTED_ROLE;
    }

    private function denyProtectedRole(?Role $role): void
    {
        if (! $role) {
            abort(404);
        }

        if ($this->isProtectedRoleName($role->name)) {
            abort(403, 'No se puede modificar el rol superadmin.');
        }
    }
}
