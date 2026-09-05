<?php

namespace Modules\Purchases\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Http\Responses\JsonEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Purchases\Actions\DestroyPurchase;
use Modules\Purchases\Actions\StorePurchase;
use Modules\Purchases\Entities\Purchase;
use Modules\Purchases\Http\Requests\StorePurchaseRequest;
use Modules\Purchases\Http\Resources\PurchaseResource;
use Modules\Suppliers\Entities\Supplier;

class PurchaseController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('purchase');
    }

    public function index(): View
    {
        $purchases = Purchase::with('supplier', 'creator')->latest()->paginate(10);

        return view('purchases::index', compact('purchases'));
    }

    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::with('brand', 'category')->active()->orderBy('description')->get();
        $categories = Category::orderBy('name')->get();

        return view('purchases::create', compact('suppliers', 'products', 'categories'));
    }

    public function store(StorePurchaseRequest $request, StorePurchase $storePurchase): JsonResponse
    {
        try {
            $purchase = $storePurchase->execute($request->validated(), (int) auth()->id());
        } catch (BusinessRuleException $e) {
            return JsonEnvelope::error($e->getMessage(), null, $e->status());
        }

        return (new PurchaseResource($purchase))
            ->response()
            ->setStatusCode(201);
    }

    public function show($id): View
    {
        $purchase = Purchase::with('supplier', 'creator', 'details.product')->findOrFail($id);

        return view('purchases::show', compact('purchase'));
    }

    public function destroy($id, DestroyPurchase $destroyPurchase): RedirectResponse
    {
        $purchase = Purchase::with('details.product')->findOrFail($id);

        try {
            $destroyPurchase->execute($purchase);
        } catch (BusinessRuleException $e) {
            $error = $e->getMessage();

            return back()->with('error', $error);
        }

        return back()->with('success', 'Compra anulada correctamente. Stock descontado.');
    }
}
