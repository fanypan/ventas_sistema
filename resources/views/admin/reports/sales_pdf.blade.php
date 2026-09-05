@extends('admin.reports.layouts.pdf')

@section('title', 'Reporte de Ventas')

@section('content')
@php
    $grandTotal = $sales->sum('total');
    $salesCount = $sales->count();
    $avgTicket = $salesCount > 0 ? $grandTotal / $salesCount : 0;
    $byDay = $sales->groupBy(fn ($sale) => $sale->created_at->format('Y-m-d'))
        ->sortKeys()
        ->map(fn ($group, $day) => [
            'label' => \Carbon\Carbon::parse($day)->format('d/m'),
            'value' => $group->sum('total'),
            'display' => money($group->sum('total')),
        ])
        ->values();
@endphp

@include('admin.reports.partials.report-header', [
    'title' => 'Reporte de ventas',
    'subtitle' => 'Período: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'),
])

@include('admin.reports.partials.metrics', [
    'metrics' => [
        ['label' => 'Total vendido', 'value' => money($grandTotal)],
        ['label' => 'Operaciones', 'value' => number_format($salesCount, 0, ',', '.')],
        ['label' => 'Ticket promedio', 'value' => money($avgTicket)],
    ],
])

@if ($byDay->count() > 1)
    @include('admin.reports.partials.bar-chart', [
        'title' => 'Ventas por día',
        'items' => $byDay,
        'color' => '#059669',
    ])
@endif

<div class="section-title">Detalle de operaciones</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Referencia</th>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Estado</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($sales as $sale)
            <tr>
                <td>{{ $sale->reference_no ?? 'V-' . $sale->id }}</td>
                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $sale->customer->name ?? 'Consumidor Final' }}</td>
                <td>{{ $sale->status }}</td>
                <td class="text-right">{{ money($sale->total) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted">Sin ventas en el período seleccionado.</td>
            </tr>
        @endforelse
    </tbody>
    @if ($salesCount > 0)
        <tfoot>
            <tr>
                <td colspan="4">Total del período</td>
                <td class="text-right">{{ money($grandTotal) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
@endsection
