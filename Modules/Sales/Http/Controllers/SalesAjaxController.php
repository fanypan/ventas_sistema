<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Responses\JsonEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Modules\Customers\Entities\Customer;
use Modules\Customers\Http\Resources\CustomerResource;
use Modules\Products\Entities\Product;
use Modules\Products\Http\Resources\ProductResource;
use Modules\Sales\Actions\ProcessSale;
use Modules\Sales\Http\Requests\AddToCartRequest;
use Modules\Sales\Http\Requests\ProcessSaleRequest;
use Modules\Sales\Http\Requests\RemoveFromCartRequest;
use Modules\Sales\Http\Requests\StorePosCustomerRequest;
use Modules\Sales\Http\Requests\UpdateCartItemRequest;
use Modules\Sales\Http\Resources\SaleResource;
use Modules\Sales\Http\Resources\TemporaryDetailResource;
use Modules\Sales\Services\CartService;

class SalesAjaxController extends Controller
{
    public function __construct(private CartService $cart)
    {
        $this->middleware('permission:create sale')->except(['storeCustomer']);
        $this->middleware('permission:create customer')->only(['storeCustomer']);
    }

    public function searchProduct(Request $request): ProductResource|JsonResponse
    {
        $term = $request->term;
        $product = Product::active()
            ->where(function ($query) use ($term) {
                $query->where('code', $term)
                    ->orWhere('description', 'LIKE', "%$term%");
            })->first();

        if ($product) {
            return new ProductResource($product);
        }

        return JsonEnvelope::error('Not found', null, 404);
    }

    public function getCart(): AnonymousResourceCollection
    {
        return $this->cartResource($this->cart->get($this->cartToken()));
    }

    public function addToCart(AddToCartRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();

        return $this->cartResource($this->cart->add(
            $this->cartToken(),
            (int) $data['product_id'],
            (int) $data['quantity'],
        ));
    }

    public function removeFromCart(RemoveFromCartRequest $request): AnonymousResourceCollection
    {
        return $this->cartResource($this->cart->remove($this->cartToken(), $request->validated('id')));
    }

    public function clearCart(): JsonResponse
    {
        $this->cart->clear($this->cartToken());

        return JsonEnvelope::success('Carrito vaciado.');
    }

    public function searchCustomer(Request $request): CustomerResource|JsonResponse
    {
        $term = $request->term;
        $customer = Customer::active()
            ->where(function ($query) use ($term) {
                $query->where('nit', $term)
                    ->orWhere('name', 'LIKE', "%$term%");
            })->first();

        if ($customer) {
            return new CustomerResource($customer);
        }

        return JsonEnvelope::error('Not found', null, 404);
    }

    public function listCustomers(): AnonymousResourceCollection
    {
        return CustomerResource::collection(Customer::active()->orderBy('name')->get());
    }

    public function storeCustomer(StorePosCustomerRequest $request): JsonResponse
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

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }

    public function updateCartItem(UpdateCartItemRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();

        return $this->cartResource($this->cart->update(
            $this->cartToken(),
            $data['id'],
            (int) $data['quantity'],
            parse_currency($data['discount'] ?? 0),
            parse_currency($data['interest'] ?? 0),
        ));
    }

    public function processSale(ProcessSaleRequest $request, ProcessSale $processSale): JsonResponse
    {
        $result = $processSale->execute(
            $this->cartToken(),
            $request->validated(),
            (int) auth()->id(),
        );

        return (new SaleResource($result['sale'], $result['change']))
            ->response()
            ->setStatusCode(201);
    }

    private function cartToken(): string
    {
        return md5((string) auth()->id());
    }

    private function cartResource(array $cart): AnonymousResourceCollection
    {
        return TemporaryDetailResource::collection($cart['details'])
            ->additional([
                'meta' => [
                    'sub_total' => $cart['sub_total'],
                ],
            ]);
    }
}
