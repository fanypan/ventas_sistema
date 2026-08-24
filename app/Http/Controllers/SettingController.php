<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function index()
    {
        $x['title']     = 'Setting';
        $x['category']  = Setting::select('category')->groupBy('category')->get();
        return view('admin.setting', $x);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:255'],
            'key'      => ['required', 'string', 'max:255', 'unique:settings'],
            'value'    => ['nullable', 'string'],
            'category' => ['required', 'string'],
            'type'     => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try {
            Setting::create($request->all());
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
            'status'    => Response::HTTP_OK,
            'message'   => 'Datos del ajuste',
            'data'      => $setting
        ], Response::HTTP_OK);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key'       => ['required', 'array'],
            'value'     => ['required', 'array']
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            for ($i = 0; $i < count($request->key); $i++) { 
                Setting::where(['key' => $request->key[$i]])->update(['value' => $request->value[$i]]);
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
