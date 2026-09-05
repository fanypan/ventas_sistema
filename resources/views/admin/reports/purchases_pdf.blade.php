@extends('admin.reports.layouts.pdf')

@section('title', 'Reporte de compras')

@section('content')
@php
    $grandTotal = $purchases->sum('total');
    $purchaseCount = $purchases->count();
    $bySupplier = $purchases->groupBy(fn ($purchase) => $purchase->supplier->supplier_name ?? 'Sin proveedor')
        ->map(fn ($group) => $group->sum('total'))
        ->sortDesc()
        ->take(8)
        ->map(fn ($value, $label) => [
            'label' => \Illuminate\Support\Str::limit($label, 28),
            'value' => $value,
            'display' => money($value),
            'color' => '#dc2626',
        ])
        ->values();
@endphp

@include('admin.reports.partials.report-header', [
    'title' => 'Reporte de compras',
    'subtitle' => 'Período: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'),
])

@include('admin.reports.partials.metrics', [
    'metrics' => [
        ['label' => 'Total comprado', 'value' => money($grandTotal)],
        ['label' => 'Operaciones', 'value' => number_format($purchaseCount, 0, ',', '.')],
        ['label' => 'Proveedores', 'value' => number_format($bySupplier->count(), 0, ',', '.')],
    ],
])

@if ($bySupplier->isNotEmpty())
    @include('admin.reports.partials.bar-chart', [
        'title' => 'Compras por proveedor',
        'items' => $bySupplier,
    ])
@endif

<div class="section-title">Detalle de compras</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Referencia</th>
            <th>Fecha</th>
            <th>Proveedor</th>
            <th>Estado</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($purchases as $purchase)
            <tr>
                <td>{{ $purchase->reference_no ?? 'C-' . $purchase->id }}</td>
                <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $purchase->supplier->supplier_name ?? 'N/A' }}</td>
                <td>{{ $purchase->status }}</td>
                <td class="text-right">{{ money($purchase->total) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted">Sin compras en el período seleccionado.</td>
            </tr>
        @endforelse
    </tbody>
    @if ($purchaseCount > 0)
        <tfoot>
            <tr>
                <td colspan="4">Total del período</td>
                <td class="text-right">{{ money($grandTotal) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
@endsection
