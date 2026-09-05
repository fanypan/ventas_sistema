@extends('admin.reports.layouts.pdf')

@section('title', 'Catálogo de productos')

@section('content')
@php
    $totalStock = $products->sum('stock');
    $categoryChart = $products->groupBy(fn ($product) => $product->category->name ?? 'Sin categoría')
        ->map(fn ($group) => $group->sum(fn ($product) => $product->stock * $product->price))
        ->sortDesc()
        ->take(8)
        ->map(fn ($value, $label) => [
            'label' => $label,
            'value' => $value,
            'display' => money($value),
            'color' => '#0284c7',
        ])
        ->values();
@endphp

@include('admin.reports.partials.report-header', [
    'title' => 'Catálogo de productos',
    'subtitle' => 'Productos activos al ' . now()->format('d/m/Y H:i'),
])

@include('admin.reports.partials.metrics', [
    'metrics' => [
        ['label' => 'Productos', 'value' => number_format($products->count(), 0, ',', '.')],
        ['label' => 'Inversión', 'value' => money($inversion)],
        ['label' => 'Proyección', 'value' => money($proyeccion)],
        ['label' => 'Utilidad est.', 'value' => number_format($utilidad, 1) . '%'],
    ],
])

@include('admin.reports.partials.bar-chart', [
    'title' => 'Valor de stock por categoría',
    'items' => $categoryChart,
])

<div class="section-title">Listado de productos</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Categoría</th>
            <th class="text-right">Stock</th>
            <th class="text-right">Costo</th>
            <th class="text-right">Precio</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->code }}</td>
                <td>
                    <strong>{{ $product->description }}</strong>
                    @if ($product->brand || $product->model_name)
                        <br><span class="text-muted">{{ trim($product->brand . ' ' . $product->model_name) }}</span>
                    @endif
                </td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($product->stock, 0, ',', '.') }}</td>
                <td class="text-right">{{ money($product->cost, false) }}</td>
                <td class="text-right">{{ money($product->price, false) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">Totales</td>
            <td class="text-right">{{ number_format($totalStock, 0, ',', '.') }}</td>
            <td class="text-right">{{ money($inversion, false) }}</td>
            <td class="text-right">{{ money($proyeccion, false) }}</td>
        </tr>
    </tfoot>
</table>
@endsection
