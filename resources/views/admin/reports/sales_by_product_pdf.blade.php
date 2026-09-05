@extends('admin.reports.layouts.pdf')

@section('title', 'Ventas por producto')

@section('content')
@php
    $rows = collect();
    $grandTotal = 0;
    $grandUnits = 0;

    foreach ($details as $group) {
        $product = $group->first()->product;
        $units = $group->sum('quantity');
        $revenue = $group->sum(fn ($detail) => $detail->quantity * $detail->price);
        $grandTotal += $revenue;
        $grandUnits += $units;

        $rows->push([
            'label' => $product->description ?? 'Producto',
            'code' => $product->code ?? '-',
            'brand' => $product->brand ?? '',
            'units' => $units,
            'price' => $group->first()->price,
            'revenue' => $revenue,
        ]);
    }

    $rows = $rows->sortByDesc('revenue')->values();
    $chartItems = $rows->take(8)->map(fn ($row) => [
        'label' => \Illuminate\Support\Str::limit($row['label'], 28),
        'value' => $row['revenue'],
        'display' => money($row['revenue']),
        'color' => '#4f46e5',
    ]);
@endphp

@include('admin.reports.partials.report-header', [
    'title' => 'Rendimiento por producto',
    'subtitle' => 'Período: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'),
])

@include('admin.reports.partials.metrics', [
    'metrics' => [
        ['label' => 'Productos vendidos', 'value' => number_format($rows->count(), 0, ',', '.')],
        ['label' => 'Unidades', 'value' => number_format($grandUnits, 0, ',', '.')],
        ['label' => 'Facturación', 'value' => money($grandTotal)],
    ],
])

@include('admin.reports.partials.bar-chart', [
    'title' => 'Top productos por facturación',
    'items' => $chartItems,
])

<div class="section-title">Detalle por producto</div>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Producto</th>
            <th>Código</th>
            <th class="text-right">Unidades</th>
            <th class="text-right">Precio</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $row['label'] }}</strong>
                    @if ($row['brand'])
                        <br><span class="text-muted">{{ $row['brand'] }}</span>
                    @endif
                </td>
                <td>{{ $row['code'] }}</td>
                <td class="text-right">{{ number_format($row['units'], 0, ',', '.') }}</td>
                <td class="text-right">{{ money($row['price']) }}</td>
                <td class="text-right">{{ money($row['revenue']) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">Totales</td>
            <td class="text-right">{{ number_format($grandUnits, 0, ',', '.') }}</td>
            <td></td>
            <td class="text-right">{{ money($grandTotal) }}</td>
        </tr>
    </tfoot>
</table>
@endsection
