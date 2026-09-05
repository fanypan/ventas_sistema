<?php

namespace Modules\Suppliers\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Suppliers\Entities\Supplier;
use Modules\Suppliers\Http\Requests\StoreSupplierRequest;
use Modules\Suppliers\Http\Requests\UpdateSupplierRequest;

class SupplierController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('supplier');
    }

    public function index(): View
    {
        $suppliers = Supplier::latest()->paginate(10);

        return view('suppliers::index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers::create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->supplierPayload());

        return redirect()->route('suppliers.index')->with('success', 'Proveedor creado con éxito');
    }

    public function edit($id): View
    {
        $supplier = Supplier::findOrFail($id);

        return view('suppliers::edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, $id): RedirectResponse
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->supplierPayload());

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado con éxito');
    }

    public function destroy($id): RedirectResponse
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado con éxito');
    }
}
