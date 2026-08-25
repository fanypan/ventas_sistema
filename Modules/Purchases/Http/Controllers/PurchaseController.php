<?php

namespace Modules\Purchases\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Routing\Controller;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Purchases\Actions\DestroyPurchase;
use Modules\Purchases\Actions\StorePurchase;
use Modules\Purchases\Entities\Purchase;
use Modules\Purchases\Http\Requests\StorePurchaseRequest;
use Modules\Suppliers\Entities\Supplier;

class PurchaseController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('purchase');
    }

    public function index()
    {
        $purchases = Purchase::with('supplier', 'creator')->latest()->paginate(10);

        return view('purchases::index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 1)->orderBy('name')->get();
        $products = Product::with('brand', 'category')->where('status', 1)->orderBy('description')->get();
        $categories = Category::orderBy('name')->get();

        return view('purchases::create', compact('suppliers', 'products', 'categories'));
    }

    public function store(StorePurchaseRequest $request, StorePurchase $storePurchase)
    {
        try {
            $purchase = $storePurchase->execute($request->validated(), (int) auth()->id());
        } catch (BusinessRuleException $e) {
            return response()->json($e->payload(), $e->status());
        }

        return response()->json([
            'success' => true,
            'purchase_id' => $purchase->id,
            'total' => (int) $purchase->total,
        ]);
    }

    public function show($id)
    {
        $purchase = Purchase::with('supplier', 'creator', 'details.product')->findOrFail($id);

        return view('purchases::show', compact('purchase'));
    }

    public function destroy($id, DestroyPurchase $destroyPurchase)
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
