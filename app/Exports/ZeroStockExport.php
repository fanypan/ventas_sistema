<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Products\Entities\Product;

class ZeroStockExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with('category', 'brand')
            ->active()
            ->zeroStock()
            ->orderBy('description')
            ->get();
    }

    public function headings(): array
    {
        return ['Código', 'Descripción', 'Existencia', 'Costo', 'Precio', 'Categoría'];
    }

    public function map($product): array
    {
        return [
            $product->code,
            $product->description,
            $product->stock,
            $product->cost,
            $product->price,
            $product->category->name ?? 'N/A',
        ];
    }
}
