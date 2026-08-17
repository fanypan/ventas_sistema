<?php

namespace Modules\StockAdjustments\Http\Controllers;

use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;

class InventoryAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read stock')->only(['index']);
        $this->middleware('permission:create stock')->only(['store']);
    }

    public function index()
    {
        $products   = Product::with('brand')->where('status', 1)->get();
        $categories = Category::all();
        $history    = InventoryAdjustment::with('product', 'user')
                        ->latest()
                        ->paginate(20);

        return view('stockadjustments::index', compact('products', 'categories', 'history'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:entrada,salida',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'nullable|string|max:255',
            'notes'      => 'nullable|string|max:500',
        ]);

        $product  = Product::findOrFail($request->product_id);
        $quantity = (int) $request->quantity;
        $type     = $request->type;

        if ($type === 'salida' && $product->stock < $quantity) {
            return response()->json([
                'error' => "Stock insuficiente. Stock actual: {$product->stock}"
            ], 422);
        }

        if ($type === 'entrada') {
            $product->stock += $quantity;
        } else {
            $product->stock -= $quantity;
        }
        $product->save();

        InventoryAdjustment::create([
            'product_id' => $product->id,
            'user_id'    => auth()->id(),
            'type'       => $type,
            'quantity'   => $quantity,
            'reason'     => $request->reason,
            'notes'      => $request->notes,
        ]);

        return response()->json([
            'success'   => true,
            'new_stock' => $product->stock,
            'message'   => "Ajuste registrado. Nuevo stock de '{$product->description}': {$product->stock}",
        ]);
    }
}
