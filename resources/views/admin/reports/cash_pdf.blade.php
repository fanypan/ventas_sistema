@extends('admin.reports.layouts.pdf')

@section('title', 'Arqueo de caja')

@section('content')
@php
    $outflow = $purchasesTotal + $expensesTotal;
    $chartItems = collect([
        ['label' => 'Ventas', 'value' => $salesTotal, 'display' => money($salesTotal), 'color' => '#059669'],
        ['label' => 'Compras', 'value' => $purchasesTotal, 'display' => money($purchasesTotal), 'color' => '#dc2626'],
        ['label' => 'Gastos', 'value' => $expensesTotal, 'display' => money($expensesTotal), 'color' => '#d97706'],
    ])->filter(fn ($item) => $item['value'] > 0)->values();
@endphp

@include('admin.reports.partials.report-header', [
    'title' => 'Arqueo diario y flujo de caja',
    'subtitle' => 'Fecha de operación: ' . \Carbon\Carbon::parse($date)->format('d/m/Y'),
])

@include('admin.reports.partials.metrics', [
    'metrics' => [
        ['label' => 'Ingresos', 'value' => money($salesTotal)],
        ['label' => 'Egresos', 'value' => money($outflow)],
        ['label' => 'Balance neto', 'value' => money($net)],
    ],
])

@include('admin.reports.partials.bar-chart', [
    'title' => 'Movimiento del día',
    'items' => $chartItems,
])

<div class="section-title">Detalle del arqueo</div>
<table class="data-table">
    <tbody>
        <tr>
            <td><strong>(+) Ingresos por ventas</strong></td>
            <td class="text-right text-success">{{ money($salesTotal) }}</td>
        </tr>
        <tr>
            <td><strong>(-) Compras de inventario</strong></td>
            <td class="text-right text-danger">{{ money($purchasesTotal) }}</td>
        </tr>
        <tr>
            <td><strong>(-) Gastos generales</strong></td>
            <td class="text-right text-danger">{{ money($expensesTotal) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>Balance del día</td>
            <td class="text-right {{ $net >= 0 ? 'text-success' : 'text-danger' }}">{{ money($net) }}</td>
        </tr>
    </tfoot>
</table>
@endsection
