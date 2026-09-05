<?php

namespace Modules\StockAdjustments\Http\Controllers;

use App\Models\InventoryAdjustment;
use Illuminate\Routing\Controller;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\StockAdjustments\Actions\AdjustInventory;
use Modules\StockAdjustments\Http\Requests\StoreInventoryAdjustmentRequest;

class InventoryAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read stock')->only(['index']);
        $this->middleware('permission:create stock')->only(['store']);
    }

    public function index()
    {
        $products = Product::with('brand')->active()->get();
        $categories = Category::all();
        $history = InventoryAdjustment::with('product', 'user')
            ->latest()
            ->paginate(20);

        return view('stockadjustments::index', compact('products', 'categories', 'history'));
    }

    public function store(StoreInventoryAdjustmentRequest $request, AdjustInventory $adjust)
    {
        return response()->json($adjust->execute($request->validated(), (int) auth()->id()));
    }
}
