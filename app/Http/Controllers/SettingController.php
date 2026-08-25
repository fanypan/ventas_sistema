<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function index()
    {
        $x['title'] = 'Setting';
        $x['category'] = Setting::select('category')->groupBy('category')->get();

        return view('admin.setting', $x);
    }

    public function store(StoreSettingRequest $request)
    {
        try {
            Setting::create($request->validated());
            Alert::success('Listo', 'Ajuste creado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo crear el ajuste', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function show(Request $request)
    {
        $setting = Setting::find($request->id);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Datos del ajuste',
            'data' => $setting,
        ], Response::HTTP_OK);
    }

    public function update(UpdateSettingsRequest $request)
    {
        try {
            $keys = $request->validated('key');
            $values = $request->validated('value');
            for ($i = 0; $i < count($keys); $i++) {
                Setting::where(['key' => $keys[$i]])->update(['value' => $values[$i]]);
            }
            Alert::success('Listo', 'Configuración guardada.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudieron guardar los ajustes', 'Intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function destroy(Request $request)
    {
        try {
            $setting = Setting::find($request->id);
            $name = $setting->name;
            $setting->delete();
            Alert::success('Listo', 'El ajuste '.$name.' fue eliminado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo eliminar el ajuste', 'Intentá de nuevo.')->toToast();
        }

        return back();
    }
}
