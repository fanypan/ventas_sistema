<?php

namespace Modules\Products\Http\Controllers;

use App\Exports\ZeroStockExport;
use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Products\Entities\Brand;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Products\Http\Requests\StoreProductRequest;
use Modules\Products\Http\Requests\UpdateProductRequest;
use Modules\Products\Services\ProductImageService;
use Modules\Purchases\Entities\PurchaseDetail;

class ProductController extends Controller
{
    use AuthorizesCrud;

    public function __construct(private ProductImageService $images)
    {
        $this->authorizeCrud('product', [
            'expiringProducts',
            'zeroStock',
            'zeroStockExcel',
            'printBarcode',
        ]);
    }

    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);

        return view('products::index', compact('products'));
    }

    public function expiringProducts()
    {
        $expiringBatches = PurchaseDetail::with(['product', 'purchase.supplier'])
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<=', now()->addDays(30))
            ->where('expiration_date', '>=', now())
            ->orderBy('expiration_date', 'asc')
            ->get();

        return view('products::expiring', compact('expiringBatches'));
    }

    public function zeroStock()
    {
        $products = Product::with('category', 'brand')
            ->active()
            ->zeroStock()
            ->orderBy('description')
            ->get();

        return view('products::zero', compact('products'));
    }

    public function zeroStockExcel()
    {
        return Excel::download(
            new ZeroStockExport,
            'productos_sin_existencia.xlsx'
        );
    }

    public function printBarcode($id)
    {
        $product = Product::findOrFail($id);

        if (empty($product->code)) {
            return back()->with('error', 'Este producto no tiene código para imprimir barras.');
        }

        return view('products::barcode', compact('product'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        $brands = Brand::active()->get();

        return view('products::create', compact('categories', 'brands'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        Product::create([
            'code' => $data['code'] ?? null,
            'description' => $data['description'],
            'price' => $data['price'],
            'cost' => $data['cost'],
            'stock' => $data['stock'] ?? 0,
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'] ?? null,
            'user_id' => auth()->id(),
            'status' => 1,
            'model_name' => $data['model_name'] ?? null,
            'warranty_months' => $this->resolveWarrantyMonths($data),
            'image' => $this->images->storeUploaded($request->file('image')),
        ]);

        return redirect()->route('products.index')->with('success', 'Producto creado con éxito.');
    }

    /**
     * @return Renderable
     */
    public function show($id)
    {
        return view('products::show');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::active()->get();
        $brands = Brand::active()->get();

        return view('products::edit', compact('product', 'categories', 'brands'));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();

        $updateData = [
            'code' => $data['code'] ?? null,
            'description' => $data['description'],
            'price' => $data['price'],
            'cost' => $data['cost'],
            'stock' => $data['stock'] ?? 0,
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'] ?? null,
            'model_name' => $data['model_name'] ?? null,
            'warranty_months' => (int) ($data['warranty_months'] ?? 0),
            'status' => $data['status'],
        ];

        if ($request->hasFile('image')) {
            $updateData['image'] = $this->images->replace($product, $request->file('image'));
        }

        $product->update($updateData);

        return redirect()->route('products.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $this->images->deleteCustom($product);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Producto eliminado exitosamente.');
    }

    private function resolveWarrantyMonths(array $data): int
    {
        if (array_key_exists('warranty_months', $data) && $data['warranty_months'] !== null && $data['warranty_months'] !== '') {
            return (int) $data['warranty_months'];
        }

        return 12;
    }
}
