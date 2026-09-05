<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Products\Entities\Product;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with('category')->active()->get();
    }

    public function headings(): array
    {
        return [
            'Código',
            'Descripción',
            'Marca',
            'Modelo',
            'Categoría',
            'Stock',
            'Costo',
            'Precio',
        ];
    }

    public function map($product): array
    {
        return [
            $product->code,
            $product->description,
            $product->brand,
            $product->model_name,
            $product->category ? $product->category->name : 'N/A',
            $product->stock,
            $product->cost,
            $product->price,
        ];
    }
}
