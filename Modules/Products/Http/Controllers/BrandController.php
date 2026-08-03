<?php

namespace Modules\Products\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Products\Entities\Brand;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::latest()->get();
        return view('products::brands.index', compact('brands'));
    }

    public function create()
    {
        return view('products::brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:brands,name',
        ]);

        Brand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'country' => $request->country,
            'description' => $request->description,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('brands.index')->with('success', 'Marca creada correctamente.');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('products::brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:brands,name,' . $id,
        ]);

        $brand = Brand::findOrFail($id);
        $brand->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'country' => $request->country,
            'description' => $request->description,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('brands.index')->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        
        // Verificar si la marca tiene productos asociados
        if ($brand->products()->count() > 0) {
            return redirect()->route('brands.index')->with('error', 'No se puede eliminar la marca porque tiene productos asociados.');
        }

        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'Marca eliminada correctamente.');
    }

    public function changeStatus($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->status = $brand->status == 1 ? 0 : 1;
        $brand->save();

        return redirect()->route('brands.index')->with('success', 'Estado de la marca actualizado.');
    }
}
