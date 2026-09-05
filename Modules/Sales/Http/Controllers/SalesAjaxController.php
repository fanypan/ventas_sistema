<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customers\Entities\Customer;
use Modules\Products\Entities\Product;
use Modules\Sales\Actions\ProcessSale;
use Modules\Sales\Http\Requests\AddToCartRequest;
use Modules\Sales\Http\Requests\ProcessSaleRequest;
use Modules\Sales\Http\Requests\RemoveFromCartRequest;
use Modules\Sales\Http\Requests\StorePosCustomerRequest;
use Modules\Sales\Http\Requests\UpdateCartItemRequest;
use Modules\Sales\Services\CartService;

class SalesAjaxController extends Controller
{
    public function __construct(private CartService $cart)
    {
        $this->middleware('permission:create sale')->except(['storeCustomer']);
        $this->middleware('permission:create customer')->only(['storeCustomer']);
    }

    public function searchProduct(Request $request)
    {
        $term = $request->term;
        $product = Product::active()
            ->where(function ($query) use ($term) {
                $query->where('code', $term)
                    ->orWhere('description', 'LIKE', "%$term%");
            })->first();

        if ($product) {
            return response()->json($product);
        }

        return response()->json(['error' => 'Not found'], 404);
    }

    public function getCart()
    {
        return response()->json($this->cart->get($this->cartToken()));
    }

    public function addToCart(AddToCartRequest $request)
    {
        $data = $request->validated();

        return response()->json($this->cart->add(
            $this->cartToken(),
            (int) $data['product_id'],
            (int) $data['quantity'],
        ));
    }

    public function removeFromCart(RemoveFromCartRequest $request)
    {
        return response()->json($this->cart->remove($this->cartToken(), $request->validated('id')));
    }

    public function clearCart()
    {
        $this->cart->clear($this->cartToken());

        return response()->json(['success' => true]);
    }

    public function searchCustomer(Request $request)
    {
        $term = $request->term;
        $customer = Customer::active()
            ->where(function ($query) use ($term) {
                $query->where('nit', $term)
                    ->orWhere('name', 'LIKE', "%$term%");
            })->first();

        if ($customer) {
            return response()->json($customer);
        }

        return response()->json(['error' => 'Not found'], 404);
    }

    public function listCustomers()
    {
        return response()->json(Customer::active()->orderBy('name')->get());
    }

    public function storeCustomer(StorePosCustomerRequest $request)
    {
        $data = $request->validated();

        $customer = Customer::create([
            'name' => $data['name'],
            'nit' => $data['nit'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'user_id' => auth()->id(),
            'status' => 1,
        ]);

        return response()->json($customer);
    }

    public function updateCartItem(UpdateCartItemRequest $request)
    {
        $data = $request->validated();

        return response()->json($this->cart->update(
            $this->cartToken(),
            $data['id'],
            (int) $data['quantity'],
            parse_currency($data['discount'] ?? 0),
            parse_currency($data['interest'] ?? 0),
        ));
    }

    public function processSale(ProcessSaleRequest $request, ProcessSale $processSale)
    {
        return response()->json($processSale->execute(
            $this->cartToken(),
            $request->validated(),
            (int) auth()->id(),
        ));
    }

    private function cartToken(): string
    {
        return md5((string) auth()->id());
    }
}
