<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Nwidart\Modules\Facades\Module;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
    {
        $x['title'] = 'Permission';
        $x['data'] = Permission::get();

        return view('admin.permission', $x);
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        try {
            $permission = Permission::create($request->validated());
            Alert::success('Listo', 'Permiso '.$permission->name.' creado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo crear el permiso', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function show(Request $request): PermissionResource
    {
        $permission = Permission::findOrFail($request->id);

        return new PermissionResource($permission);
    }

    public function update(UpdatePermissionRequest $request): RedirectResponse
    {
        try {
            $permission = Permission::find($request->validated('id'));
            $permission->update($request->safe()->only(['name', 'guard_name']));
            Alert::success('Listo', 'Permiso '.$permission->name.' guardado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo guardar el permiso', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        try {
            $permission = Permission::find($request->id);
            $name = $permission->name;
            $permission->delete();
            Alert::success('Listo', 'Permiso '.$name.' eliminado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo eliminar el permiso', 'Intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function reloadPermission(): RedirectResponse
    {
        try {
            $this->initModules();
            Alert::success('Listo', 'Permisos actualizados.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudieron actualizar los permisos', 'Intentá de nuevo.')->toToast();
        }

        return back();
    }

    private function initModules()
    {
        $modules = Module::getOrdered();

        foreach ($modules as $module) {
            $moduleJson = json_decode(file_get_contents($module->getPath().'/module.json', true));
            $permissions = $moduleJson->permissions ?? [];
            for ($i = 0; $i < count($permissions); $i++) {
                $permissionMappings = ['delete', 'update', 'read', 'create'];
                foreach ($permissionMappings as $permissionMapping) {
                    $name = $permissionMapping.' '.$permissions[$i];
                    $permission = Permission::where(['name' => $name])->count();
                    if ($permission == 0) {
                        Permission::create(['name' => $name]);
                    }
                }
            }
        }
    }
}
