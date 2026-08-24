<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Purchases\Entities\Purchase;
use Modules\Purchases\Entities\PurchaseDetail;
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

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required',
            'items.*.lot' => 'nullable|string|max:80',
            'items.*.expiration' => 'nullable|date',
        ]);

        $items = collect($request->items)->map(function ($item) {
            $item['price'] = (int) round(parse_currency($item['price'] ?? 0));
            $item['quantity'] = (int) ($item['quantity'] ?? 0);
            $item['lot'] = trim((string) ($item['lot'] ?? '')) ?: null;
            $item['expiration'] = ! empty($item['expiration']) ? $item['expiration'] : null;

            return $item;
        });

        if ($items->contains(fn ($item) => $item['quantity'] < 1 || $item['price'] < 0)) {
            return response()->json(['error' => 'Hay ítems con cantidad o costo inválido.'], 422);
        }

        try {
            $purchase = DB::transaction(function () use ($request, $items) {
                $total = $items->sum(fn ($item) => $item['quantity'] * $item['price']);

                $purchase = Purchase::create([
                    'supplier_id' => $request->supplier_id,
                    'user_id' => auth()->id(),
                    'total' => $total,
                    'status' => 1,
                ]);

                foreach ($items as $item) {
                    PurchaseDetail::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'expiration_date' => $item['expiration'],
                        'lot_number' => $item['lot'],
                    ]);

                    $product = Product::lockForUpdate()->find($item['id']);
                    if (! $product) {
                        continue;
                    }

                    $product->stock = (int) $product->stock + $item['quantity'];
                    $product->cost = $item['price'];
                    $product->save();
                }

                return $purchase;
            });

            return response()->json([
                'success' => true,
                'purchase_id' => $purchase->id,
                'total' => (int) $purchase->total,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'No se pudo guardar la compra. Intentá de nuevo.'], 500);
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with('supplier', 'creator', 'details.product')->findOrFail($id);

        return view('purchases::show', compact('purchase'));
    }

    public function destroy($id)
    {
        $purchase = Purchase::with('details.product')->findOrFail($id);

        if ($purchase->status == 0) {
            return back()->with('error', 'Esta compra ya ha sido anulada.');
        }

        try {
            DB::transaction(function () use ($purchase) {
                foreach ($purchase->details as $detail) {
                    $product = Product::lockForUpdate()->find($detail->product_id);
                    if (! $product) {
                        continue;
                    }

                    $product->decrement('stock', $detail->quantity);
                    $this->restoreLastPurchaseCost($product, $purchase->id);
                }

                $purchase->status = 0;
                $purchase->save();
            });

            return back()->with('success', 'Compra anulada correctamente. Stock descontado.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo anular la compra. Intentá de nuevo.');
        }
    }

    protected function restoreLastPurchaseCost(Product $product, int $exceptPurchaseId): void
    {
        $last = PurchaseDetail::where('product_id', $product->id)
            ->whereHas('purchase', function ($query) use ($exceptPurchaseId) {
                $query->where('status', 1)->where('id', '!=', $exceptPurchaseId);
            })
            ->latest('id')
            ->first();

        if ($last) {
            $product->cost = (int) round($last->price);
            $product->save();
        }
    }
}
