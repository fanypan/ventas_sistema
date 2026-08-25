<?php

namespace Modules\{Module}\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Routing\Controller;
use Modules\{Module}\Entities\{Model};
use Modules\{Module}\Http\Requests\Store{Model}Request;
use Modules\{Module}\Http\Requests\Update{Model}Request;
use RealRashid\SweetAlert\Facades\Alert;

class {Module}Controller extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('{model}');
    }

    public function index()
    {
        $x['title'] = '{Model}';
        $x['data'] = {Model}::query()->get();

        return view('{module}::index', $x);
    }

    public function store(Store{Model}Request $request)
    {
        try {
            ${model} = {Model}::create($request->validated());
            Alert::success('Listo', 'Datos de '.${model}->name.' creados.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo crear', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function update(Update{Model}Request $request, {Model} ${model})
    {
        try {
            ${model}->update($request->validated());
            Alert::success('Listo', 'Datos de '.${model}->name.' guardados.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo guardar', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function destroy({Model} ${model})
    {
        try {
            $name = ${model}->name;
            ${model}->delete();
            Alert::success('Listo', 'Datos de '.$name.' eliminados.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo eliminar', 'Intentá de nuevo.')->toToast();
        }

        return back();
    }
}
