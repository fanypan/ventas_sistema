<?php

namespace Modules\Products\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Concerns\AuthorizesCrud;

class ProductController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('product', [
            'expiringProducts',
            'zeroStock',
            'zeroStockExcel',
            'printBarcode',
        ]);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $products = \Modules\Products\Entities\Product::with('category')->latest()->paginate(10);
        return view('products::index', compact('products'));
    }

    public function expiringProducts()
    {
        $expiringBatches = \Modules\Purchases\Entities\PurchaseDetail::with(['product', 'purchase.supplier'])
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<=', now()->addDays(30))
            ->where('expiration_date', '>=', now())
            ->orderBy('expiration_date', 'asc')
            ->get();

        return view('products::expiring', compact('expiringBatches'));
    }

    public function zeroStock()
    {
        $products = \Modules\Products\Entities\Product::with('category', 'brand')
            ->where('status', 1)
            ->where('stock', '<=', 0)
            ->orderBy('description')
            ->get();

        return view('products::zero', compact('products'));
    }

    public function zeroStockExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ZeroStockExport,
            'productos_sin_existencia.xlsx'
        );
    }

    public function printBarcode($id)
    {
        $product = \Modules\Products\Entities\Product::findOrFail($id);

        if (empty($product->code)) {
            return back()->with('error', 'Este producto no tiene código para imprimir barras.');
        }

        return view('products::barcode', compact('product'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $categories = \Modules\Products\Entities\Category::where('status', 1)->get();
        $brands = \Modules\Products\Entities\Brand::where('status', 1)->get();
        return view('products::create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|unique:products,code',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'stock' => 'integer|min:0',
            'model_name' => 'nullable|string|max:100',
            'warranty_months' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        \Modules\Products\Entities\Product::create([
            'code' => $request->code,
            'description' => $request->description,
            'price' => $request->price,
            'cost' => $request->cost,
            'stock' => $request->stock ?? 0,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'user_id' => auth()->id(),
            'status' => 1,
            'model_name' => $request->model_name,
            'warranty_months' => $request->warranty_months ?? 12,
            'image' => $imagePath,
        ]);

        return redirect()->route('products.index')->with('success', 'Producto creado con éxito.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('products::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $product = \Modules\Products\Entities\Product::findOrFail($id);
        $categories = \Modules\Products\Entities\Category::where('status', 1)->get();
        $brands = \Modules\Products\Entities\Brand::where('status', 1)->get();
        return view('products::edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $product = \Modules\Products\Entities\Product::findOrFail($id);

        $request->validate([
            'code' => 'nullable|string|unique:products,code,' . $product->id,
            'description' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'stock' => 'integer|min:0',
            'model_name' => 'nullable|string|max:100',
            'warranty_months' => 'nullable|integer|min:0',
            'status' => 'required|integer|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        $updateData = [
            'code' => $request->code,
            'description' => $request->description,
            'price' => $request->price,
            'cost' => $request->cost,
            'stock' => $request->stock ?? 0,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'model_name' => $request->model_name,
            'warranty_months' => $request->warranty_months ?? 12,
            'status' => $request->status,
        ];

        if (isset($imagePath)) {
            $updateData['image'] = $imagePath;
        }

        $product->update($updateData);

        return redirect()->route('products.index')->with('success', 'Producto actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $product = \Modules\Products\Entities\Product::findOrFail($id);
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
