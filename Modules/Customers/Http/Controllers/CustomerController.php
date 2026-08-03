<?php

namespace Modules\Customers\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $customers = \Modules\Customers\Entities\Customer::latest()->paginate(10);
        return view('customers::index', compact('customers'));
    }

    public function create()
    {
        return view('customers::create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nit' => 'nullable|string|unique:customers,nit',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        \Modules\Customers\Entities\Customer::create([
            'nit' => $request->nit,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'user_id' => auth()->id(),
            'status' => 1,
        ]);

        return redirect()->route('customers.index')->with('success', 'Cliente registrado con éxito.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('customers::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('customers::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
