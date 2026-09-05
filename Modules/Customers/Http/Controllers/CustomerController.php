<?php

namespace Modules\Customers\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Customers\Entities\Customer;
use Modules\Customers\Http\Requests\StoreCustomerRequest;

class CustomerController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('customer');
    }

    public function index(): View
    {
        $customers = Customer::latest()->paginate(10);

        return view('customers::index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers::create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create($request->customerPayload(auth()->id()));

        return redirect()->route('customers.index')->with('success', 'Cliente registrado con éxito.');
    }

    public function show($id): View
    {
        return view('customers::show');
    }

    public function edit($id): View
    {
        return view('customers::edit');
    }

    public function update($id): void
    {
        //
    }

    public function destroy($id): void
    {
        //
    }
}
