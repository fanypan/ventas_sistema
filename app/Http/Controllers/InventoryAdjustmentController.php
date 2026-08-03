<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryAdjustment;
use Modules\Products\Entities\Product;
use Modules\Products\Entities\Category;

class InventoryAdjustmentController extends Controller
{
    public function index()
    {
        $products   = Product::where('status', 1)->get();
        $categories = Category::all();
        $history    = InventoryAdjustment::with('product', 'user')
                        ->latest()
                        ->paginate(20);

        return view('admin.inventory_adjustments.index', compact('products', 'categories', 'history'));
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

        // Validate sufficient stock for exits
        if ($type === 'salida' && $product->stock < $quantity) {
            return response()->json([
                'error' => "Stock insuficiente. Stock actual: {$product->stock}"
            ], 422);
        }

        // Adjust stock
        if ($type === 'entrada') {
            $product->stock += $quantity;
        } else {
            $product->stock -= $quantity;
        }
        $product->save();

        // Log the adjustment
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
