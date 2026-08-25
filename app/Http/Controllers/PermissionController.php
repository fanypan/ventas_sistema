<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class PermissionController extends Controller
{
    public function index()
    {
        $x['title'] = 'Permission';
        $x['data'] = Permission::get();

        return view('admin.permission', $x);
    }

    public function store(StorePermissionRequest $request)
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

    public function show(Request $request)
    {
        $permission = Permission::find($request->id);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data permission by id',
            'data' => $permission,
        ], Response::HTTP_OK);
    }

    public function update(UpdatePermissionRequest $request)
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

    public function destroy(Request $request)
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

    public function reloadPermission()
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
