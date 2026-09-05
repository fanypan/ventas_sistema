@extends('admin.reports.layouts.pdf')

@section('title', 'Stock mínimo')

@section('content')
@php
    $agotados = $products->where('stock', '<=', 0)->count();
    $criticos = $products->where('stock', '>', 0)->count();
    $chartItems = $products->take(10)->map(fn ($product) => [
        'label' => \Illuminate\Support\Str::limit($product->description, 26),
        'value' => max(0, (float) $product->stock),
        'display' => number_format($product->stock, 0, ',', '.') . ' u.',
        'color' => $product->stock <= 0 ? '#dc2626' : '#d97706',
    ])->values();
@endphp

@include('admin.reports.partials.report-header', [
    'title' => 'Stock mínimo y agotados',
    'subtitle' => 'Umbral: ' . $threshold . ' unidades · Generado el ' . now()->format('d/m/Y H:i'),
])

@include('admin.reports.partials.metrics', [
    'metrics' => [
        ['label' => 'Productos alerta', 'value' => number_format($products->count(), 0, ',', '.')],
        ['label' => 'Agotados', 'value' => number_format($agotados, 0, ',', '.')],
        ['label' => 'Stock crítico', 'value' => number_format($criticos, 0, ',', '.')],
    ],
])

@if ($products->isEmpty())
    <div class="alert-box ok">No hay productos con stock menor o igual a {{ $threshold }} unidades.</div>
@else
    <div class="alert-box">
        Se encontraron {{ $products->count() }} producto(s) que requieren reposición inmediata.
    </div>

    @include('admin.reports.partials.bar-chart', [
        'title' => 'Nivel de stock (top 10)',
        'items' => $chartItems,
        'max' => max($threshold, $products->max('stock') ?: 1),
    ])

    <div class="section-title">Detalle de productos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="text-right">Costo</th>
                <th class="text-right">Precio</th>
                <th class="text-right">Stock</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->code }}</td>
                    <td>
                        <strong>{{ $product->description }}</strong>
                        @if ($product->brand)
                            <br><span class="text-muted">{{ $product->brand }}</span>
                        @endif
                    </td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td class="text-right">{{ money($product->cost) }}</td>
                    <td class="text-right">{{ money($product->price) }}</td>
                    <td class="text-right {{ $product->stock <= 0 ? 'text-danger' : 'text-danger' }}">{{ $product->stock }}</td>
                    <td>{{ $product->stock <= 0 ? 'AGOTADO' : 'CRÍTICO' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
