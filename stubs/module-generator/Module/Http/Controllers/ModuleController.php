<?php

namespace Modules\{Module}\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
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

    public function index(): View
    {
        $x['title'] = '{Model}';
        $x['data'] = {Model}::query()->get();

        return view('{module}::index', $x);
    }

    public function store(Store{Model}Request $request): RedirectResponse
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

    public function update(Update{Model}Request $request, {Model} ${model}): RedirectResponse
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

    public function destroy({Model} ${model}): RedirectResponse
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
