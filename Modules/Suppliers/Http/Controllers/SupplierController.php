<?php

namespace Modules\Suppliers\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Rules\RucParaguay;
use App\Support\RucParaguay as RucParaguaySupport;

class SupplierController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('supplier');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $suppliers = \Modules\Suppliers\Entities\Supplier::latest()->paginate(10);
        return view('suppliers::index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers::create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nit' => ['nullable', new RucParaguay(allowConsumidorFinal: false), 'string', 'max:50'],
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        \Modules\Suppliers\Entities\Supplier::create([
            'name' => $request->name,
            'nit' => RucParaguaySupport::format($request->nit),
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor creado con éxito');
    }

    public function edit($id)
    {
        $supplier = \Modules\Suppliers\Entities\Supplier::findOrFail($id);
        return view('suppliers::edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nit' => ['nullable', new RucParaguay(allowConsumidorFinal: false), 'string', 'max:50'],
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $supplier = \Modules\Suppliers\Entities\Supplier::findOrFail($id);
        $supplier->update([
            'name' => $request->name,
            'nit' => RucParaguaySupport::format($request->nit),
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado con éxito');
    }

    public function destroy($id)
    {
        $supplier = \Modules\Suppliers\Entities\Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado con éxito');
    }
}
