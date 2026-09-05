<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Products\Entities\Brand;
use Modules\Products\Http\Requests\StoreBrandRequest;
use Modules\Products\Http\Requests\UpdateBrandRequest;

class BrandController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('brand', extraUpdate: ['changeStatus']);
    }

    public function index(): View
    {
        $brands = Brand::latest()->get();

        return view('products::brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('products::brands.create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Brand::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'country' => $data['country'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 1,
        ]);

        return redirect()->route('brands.index')->with('success', 'Marca creada correctamente.');
    }

    public function edit($id): View
    {
        $brand = Brand::findOrFail($id);

        return view('products::brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, $id): RedirectResponse
    {
        $data = $request->validated();
        $brand = Brand::findOrFail($id);
        $brand->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'country' => $data['country'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 1,
        ]);

        return redirect()->route('brands.index')->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        $brand = Brand::findOrFail($id);

        if ($brand->products()->count() > 0) {
            return redirect()->route('brands.index')->with('error', 'No se puede eliminar la marca porque tiene productos asociados.');
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Marca eliminada correctamente.');
    }

    public function changeStatus($id): RedirectResponse
    {
        $brand = Brand::findOrFail($id);
        $brand->status = $brand->status == 1 ? 0 : 1;
        $brand->save();

        return redirect()->route('brands.index')->with('success', 'Estado de la marca actualizado.');
    }
}
