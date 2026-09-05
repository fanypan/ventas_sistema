@extends('admin.reports.layouts.pdf')

@section('title', 'Ventas por tipo de pago')

@section('content')
@php
    $methodLabels = [
        'efectivo' => 'Efectivo',
        'qr' => 'QR / Digital',
        'tarjeta' => 'Tarjeta',
        'transferencia' => 'Transferencia',
        'credito' => 'Crédito',
    ];
    $methodColors = [
        'efectivo' => '#059669',
        'qr' => '#0284c7',
        'tarjeta' => '#4f46e5',
        'transferencia' => '#d97706',
        'credito' => '#64748b',
    ];
    $grandTotal = $byMethod->sum('total');
    $chartItems = $byMethod->map(function ($data, $method) use ($methodLabels, $methodColors, $grandTotal) {
        return [
            'label' => $methodLabels[$method] ?? ucfirst((string) $method),
            'value' => $data['total'],
            'display' => money($data['total']) . ' (' . ($grandTotal > 0 ? number_format(($data['total'] / $grandTotal) * 100, 1) : 0) . '%)',
            'color' => $methodColors[$method] ?? '#4f46e5',
        ];
    })->values();
@endphp

@include('admin.reports.partials.report-header', [
    'title' => 'Ventas por tipo de pago',
    'subtitle' => 'Período: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y') . ' · ' . $sales->count() . ' operaciones',
])

@include('admin.reports.partials.metrics', [
    'metrics' => [
        ['label' => 'Total recaudado', 'value' => money($grandTotal)],
        ['label' => 'Operaciones', 'value' => number_format($sales->count(), 0, ',', '.')],
        ['label' => 'Medios de pago', 'value' => number_format($byMethod->count(), 0, ',', '.')],
    ],
])

@include('admin.reports.partials.bar-chart', [
    'title' => 'Distribución por medio de pago',
    'items' => $chartItems,
])

<div class="section-title">Resumen por método</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Método</th>
            <th class="text-right">Operaciones</th>
            <th class="text-right">Total</th>
            <th class="text-right">Participación</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($byMethod as $method => $data)
            <tr>
                <td>{{ $methodLabels[$method] ?? ucfirst((string) $method) }}</td>
                <td class="text-right">{{ $data['count'] }}</td>
                <td class="text-right">{{ money($data['total']) }}</td>
                <td class="text-right">{{ $grandTotal > 0 ? number_format(($data['total'] / $grandTotal) * 100, 1) : 0 }}%</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>Total</td>
            <td class="text-right">{{ $sales->count() }}</td>
            <td class="text-right">{{ money($grandTotal) }}</td>
            <td class="text-right">100%</td>
        </tr>
    </tfoot>
</table>

<div class="section-title">Detalle de transacciones</div>
<table class="data-table">
    <thead>
        <tr>
            <th>#Venta</th>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Pago</th>
            <th class="text-right">Total</th>
            <th class="text-right">Cobrado</th>
            <th class="text-right">Vuelto</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sales as $sale)
            <tr>
                <td>#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $sale->customer->name ?? 'Público General' }}</td>
                <td>{{ $methodLabels[$sale->payment_type] ?? ucfirst((string) $sale->payment_type) }}</td>
                <td class="text-right">{{ money($sale->total) }}</td>
                <td class="text-right">{{ money($sale->payment_with) }}</td>
                <td class="text-right">{{ money($sale->change) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
