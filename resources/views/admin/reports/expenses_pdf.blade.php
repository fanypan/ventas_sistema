@extends('admin.reports.layouts.pdf')

@section('title', 'Reporte de egresos')

@section('content')
@php
    $chartItems = collect([
        ['label' => 'Gastos generales', 'value' => $totalGeneral, 'display' => money($totalGeneral), 'color' => '#64748b'],
        ['label' => 'Insumos', 'value' => $totalInsumos, 'display' => money($totalInsumos), 'color' => '#0284c7'],
    ])->filter(fn ($item) => $item['value'] > 0)->values();
@endphp

@include('admin.reports.partials.report-header', [
    'title' => 'Reporte de egresos',
    'subtitle' => 'Período: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'),
])

@include('admin.reports.partials.metrics', [
    'metrics' => [
        ['label' => 'Gastos generales', 'value' => money($totalGeneral)],
        ['label' => 'Insumos', 'value' => money($totalInsumos)],
        ['label' => 'Total egresos', 'value' => money($totalEgresos)],
    ],
])

@include('admin.reports.partials.bar-chart', [
    'title' => 'Composición de egresos',
    'items' => $chartItems,
])

<div class="section-title">Detalle de movimientos</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Usuario</th>
            <th class="text-center">Cant.</th>
            <th class="text-right">Monto</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($expenses as $expense)
            <tr>
                <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                <td>{{ $expense->type === 'insumo' ? 'Insumo' : 'Gasto' }}</td>
                <td>
                    {{ $expense->description }}
                    @if ($expense->insumo)
                        <br><span class="text-muted">{{ $expense->insumo->name }}</span>
                    @endif
                </td>
                <td>{{ $expense->user->name ?? '-' }}</td>
                <td class="text-center">{{ $expense->quantity ? number_format($expense->quantity, 2) : '-' }}</td>
                <td class="text-right">{{ money($expense->amount) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted">Sin egresos en el período seleccionado.</td>
            </tr>
        @endforelse
    </tbody>
    @if ($expenses->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="5">Total del período</td>
                <td class="text-right">{{ money($totalEgresos) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
@endsection
