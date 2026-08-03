<?php

namespace Modules\Purchases\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $purchases = \Modules\Purchases\Entities\Purchase::with('supplier', 'creator')->latest()->paginate(10);
        return view('purchases::index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = \Modules\Suppliers\Entities\Supplier::where('status', 1)->get();
        $products = \Modules\Products\Entities\Product::where('status', 1)->get();
        $categories = \Modules\Products\Entities\Category::all();
        return view('purchases::create', compact('suppliers', 'products', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $total = collect($request->items)->sum(function($item) {
                return $item['quantity'] * $item['price'];
            });

            $purchase = \Modules\Purchases\Entities\Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'total' => $total,
                'status' => 1,
            ]);

            foreach ($request->items as $item) {
                \Modules\Purchases\Entities\PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'expiration_date' => $item['expiration'] ?: null,
                    'lot_number' => $item['lot'] ?: null,
                ]);

                // Update Stock and Weighted Average Cost (Replicando procesar_compra)
                $product = \Modules\Products\Entities\Product::find($item['id']);
                if ($product) {
                    $oldStock = $product->stock;
                    $oldCost = $product->cost;
                    $newQty = $item['quantity'];
                    $newPrice = $item['price'];

                    $totalQty = $oldStock + $newQty;
                    if ($totalQty > 0) {
                        $newWeightedCost = (($oldStock * $oldCost) + ($newQty * $newPrice)) / $totalQty;
                        $product->cost = $newWeightedCost;
                    }
                    
                    $product->stock = $totalQty;
                    $product->save();
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $purchase = \Modules\Purchases\Entities\Purchase::with('supplier', 'creator', 'details.product')->findOrFail($id);
        return view('purchases::show', compact('purchase'));
    }

    public function destroy($id)
    {
        $purchase = \Modules\Purchases\Entities\Purchase::with('details.product')->findOrFail($id);

        if ($purchase->status == 0) {
            return back()->with('error', 'Esta compra ya ha sido anulada.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Anular compra y restar stock (Replicando anular_compra)
            foreach ($purchase->details as $detail) {
                if ($detail->product) {
                    // Restamos el stock que ingresó en la compra
                    $detail->product->decrement('stock', $detail->quantity);
                }
            }

            $purchase->status = 0; // Anulada
            $purchase->save();

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Compra anulada correctamente. Stock descontado.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error al anular compra: ' . $e->getMessage());
        }
    }
}
