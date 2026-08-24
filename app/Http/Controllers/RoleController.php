<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    private const PROTECTED_ROLE = 'superadmin';

    public function index()
    {
        $x['title']         = 'Role';
        $x['data']          = Role::with('permissions')->get();
        $x['permission']    = Permission::orderBy('id', 'desc')->get();
        return view('admin.role', $x);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => ['required'],
            'guard_name'    => ['required'],
            'permissions'   => ['required', 'array'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }
        if ($this->isProtectedRoleName($request->name)) {
            abort(403, 'No se puede crear el rol superadmin.');
        }
        DB::beginTransaction();
        try {
            $role = Role::create([
                'name'          => $request->name,
                'guard_name'    => $request->guard_name,
            ]);
            $role->givePermissionTo($request->permissions);
            DB::commit();
            Alert::success('Listo', 'Rol '.$role->name.' creado.')->toToast();
        } catch (\Throwable $th) {
            DB::rollback();
            report($th);
            Alert::error('No se pudo crear el rol', 'Revisá los datos e intentá de nuevo.')->toToast();
        }
        return back();
    }

    public function show(Request $request)
    {
        $role = Role::with('permissions')->find($request->id);
        return response()->json([
            'status'    => Response::HTTP_OK,
            'message'   => 'Data role by id',
            'data'      => $role
        ], Response::HTTP_OK);
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => ['required'],
            'guard_name'    => ['required'],
            'permissions'   => ['required', 'array'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }
        $role = Role::find($request->id);
        $this->denyProtectedRole($role);
        if ($this->isProtectedRoleName($request->name)) {
            abort(403, 'No se puede renombrar un rol a superadmin.');
        }
        DB::beginTransaction();
        try {
            $role->update([
                'name'          => $request->name,
                'guard_name'    => $request->guard_name,
            ]);
            $role->syncPermissions($request->permissions);
            DB::commit();
            Alert::success('Listo', 'Rol '.$role->name.' guardado.')->toToast();
        } catch (\Throwable $th) {
            DB::rollback();
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
            $role->delete();
            Alert::success('Listo', 'Rol '.$role->name.' eliminado.')->toToast();
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
