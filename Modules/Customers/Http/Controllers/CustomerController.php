<?php

namespace Modules\Customers\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\Customers\Entities\Customer;
use Modules\Customers\Http\Requests\StoreCustomerRequest;

class CustomerController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('customer');
    }

    public function index()
    {
        $customers = Customer::latest()->paginate(10);

        return view('customers::index', compact('customers'));
    }

    public function create()
    {
        return view('customers::create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->customerPayload(auth()->id()));

        return redirect()->route('customers.index')->with('success', 'Cliente registrado con éxito.');
    }

    /**
     * @return Renderable
     */
    public function show($id)
    {
        return view('customers::show');
    }

    public function edit($id)
    {
        return view('customers::edit');
    }

    public function update($id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
