<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Products\Entities\Category;
use Modules\Products\Http\Requests\StoreCategoryRequest;

class CategoryController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('category');
    }

    public function index(): View
    {
        $categories = Category::latest()->paginate(10);

        return view('products::categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('products::categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create([
            'name' => $request->validated('name'),
            'user_id' => auth()->id(),
            'status' => 1,
        ]);

        return redirect()->route('categories.index')->with('success', 'Categoría creada con éxito.');
    }

    public function show($id): View
    {
        return view('products::show');
    }

    public function edit($id): View
    {
        return view('products::edit');
    }

    public function update($id): void
    {
        //
    }

    public function destroy($id): void
    {
        //
    }
}
