<?php

namespace Modules\{Module}\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use Modules\{Module}\Models\{Model};
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class {Module}Controller extends Controller
{
    public function index()
    {
        $x['title']     = "{Model}";
        $x['data']      = {Model}::get();

        return view('{module}::index', $x);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:255']
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }
        try {
            ${model} = {Model}::create([
                'name'      => $request->name
            ]);
            Alert::success('Notificación', 'Datos de <b>' . ${model}->name . '</b> creados correctamente')->toToast()->toHtml();
        } catch (\Throwable $th) {
            Alert::error('Notificación', 'Error al crear <b>' . ${model}->name . '</b>: ' . $th->getMessage())->toToast()->toHtml();
        }
        return back();
    }

    public function show(Request $request)
    {
        ${model} = {Model}::find($request->id);
        return response()->json([
            'status'    => Response::HTTP_OK,
            'message'   => 'Data {model} by id',
            'data'      => ${model}
        ], Response::HTTP_OK);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:255']
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }
        try {
            ${model} = {Model}::find($request->id);
            ${model}->update([
                'name'  => $request->name
            ]);
            Alert::success('Notificación', 'Datos de <b>' . ${model}->name . '</b> guardados correctamente')->toToast()->toHtml();
        } catch (\Throwable $th) {
            Alert::error('Notificación', 'Error al guardar <b>' . ${model}->name . '</b>: ' . $th->getMessage())->toToast()->toHtml();
        }
        return back();
    }

    public function destroy(Request $request)
    {
        try {
            ${model} = {Model}::find($request->id);
            ${model}->delete();
            Alert::success('Notificación', 'Datos de <b>' . ${model}->name . '</b> eliminados')->toToast()->toHtml();
        } catch (\Throwable $th) {
            Alert::error('Notificación', 'Error al eliminar <b>' . ${model}->name . '</b>: ' . $th->getMessage())->toToast()->toHtml();
        }
        return back();
    }
}
