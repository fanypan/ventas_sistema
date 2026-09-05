@extends('admin.reports.layouts.pdf')

@section('title', 'Estado de resultados')

@section('content')
@php
    $margin = $salesTotal > 0 ? ($utilidadNeta / $salesTotal) * 100 : 0;
    $chartItems = collect([
        ['label' => 'Ventas', 'value' => $salesTotal, 'display' => money($salesTotal), 'color' => '#059669'],
        ['label' => 'Compras', 'value' => $purchasesTotal, 'display' => money($purchasesTotal), 'color' => '#dc2626'],
        ['label' => 'Gastos', 'value' => $expensesTotal, 'display' => money($expensesTotal), 'color' => '#d97706'],
        ['label' => 'Utilidad neta', 'value' => abs($utilidadNeta), 'display' => money($utilidadNeta), 'color' => $utilidadNeta >= 0 ? '#4f46e5' : '#dc2626'],
    ])->filter(fn ($item) => $item['value'] > 0)->values();
@endphp

@include('admin.reports.partials.report-header', [
    'title' => 'Estado de resultados',
    'subtitle' => 'Período: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'),
])

@include('admin.reports.partials.metrics', [
    'metrics' => [
        ['label' => 'Ventas', 'value' => money($salesTotal)],
        ['label' => 'Utilidad bruta', 'value' => money($utilidadBruta)],
        ['label' => 'Utilidad neta', 'value' => money($utilidadNeta)],
        ['label' => 'Margen neto', 'value' => number_format($margin, 1) . '%'],
    ],
])

@include('admin.reports.partials.bar-chart', [
    'title' => 'Composición del período',
    'items' => $chartItems,
])

<div class="panel">
    <div class="panel-title">Ingresos operativos</div>
    <table width="100%" class="pnl-row">
        <tr>
            <td>Ventas netas realizadas</td>
            <td class="text-right">{{ money($salesTotal) }}</td>
        </tr>
    </table>
</div>

<div class="panel">
    <div class="panel-title">Costos directos</div>
    <table width="100%" class="pnl-row">
        <tr>
            <td>Costo de mercadería (compras)</td>
            <td class="text-right text-danger">-{{ money($purchasesTotal) }}</td>
        </tr>
        <tr class="subtotal">
            <td>Utilidad bruta</td>
            <td class="text-right {{ $utilidadBruta >= 0 ? 'text-success' : 'text-danger' }}">{{ money($utilidadBruta) }}</td>
        </tr>
    </table>
</div>

<div class="panel">
    <div class="panel-title">Gastos operativos</div>
    <table width="100%" class="pnl-row">
        <tr>
            <td>Gastos generales / administrativos</td>
            <td class="text-right text-danger">-{{ money($expensesTotal) }}</td>
        </tr>
        <tr class="total">
            <td>Utilidad neta del ejercicio</td>
            <td class="text-right {{ $utilidadNeta >= 0 ? 'text-success' : 'text-danger' }}">{{ money($utilidadNeta) }}</td>
        </tr>
    </table>
</div>
@endsection
