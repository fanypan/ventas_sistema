<?php

namespace Modules\{Module}\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use Modules\{Module}\Models\{Model};
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class {Module}Controller extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('{model}');
    }

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
            Alert::success('Listo', 'Datos de ' . ${model}->name . ' creados.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo crear', 'Revisá los datos e intentá de nuevo.')->toToast();
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
            Alert::success('Listo', 'Datos de ' . ${model}->name . ' guardados.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo guardar', 'Revisá los datos e intentá de nuevo.')->toToast();
        }
        return back();
    }

    public function destroy(Request $request)
    {
        try {
            ${model} = {Model}::find($request->id);
            ${model}->delete();
            Alert::success('Listo', 'Datos de ' . ${model}->name . ' eliminados.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo eliminar', 'Intentá de nuevo.')->toToast();
        }
        return back();
    }
}
