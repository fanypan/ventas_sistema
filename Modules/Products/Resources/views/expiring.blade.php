@extends('admin.layouts.master')

@section('title', 'Productos por Vencer')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0"><i class="fas fa-exclamation-triangle text-warning"></i> Productos Próximos a Vencer</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">Alertas para los próximos 30 días</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover m-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Proveedor</th>
                            <th class="text-center">Stock Lote</th>
                            <th class="text-center">Vencimiento</th>
                            <th class="text-center">Días Restantes</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expiringBatches as $batch)
                            @php
                                $days = now()->diffInDays($batch->expiration_date, false);
                                $class = '';
                                $badge = '';
                                if($days <= 7) {
                                    $class = 'bg-danger-light';
                                    $badge = '<span class="badge badge-danger">URGENTE</span>';
                                } elseif($days <= 15) {
                                    $class = 'bg-warning-light';
                                    $badge = '<span class="badge badge-warning">PRÓXIMO</span>';
                                } else {
                                    $badge = '<span class="badge badge-success">NORMAL</span>';
                                }
                            @endphp
                            <tr class="{{ $class }}">
                                <td>{{ $batch->product->description }}</td>
                                <td>{{ $batch->purchase->supplier->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $batch->quantity }}</td>
                                <td class="text-center text-bold">{{ \Carbon\Carbon::parse($batch->expiration_date)->format('d/m/Y') }}</td>
                                <td class="text-center">{{ ceil($days) }} días</td>
                                <td class="text-center">{!! $badge !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-5">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h4>¡Todo en orden!</h4>
                                    <p>No hay productos próximos a vencer en los próximos 30 días.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
</div>

<style>
    .bg-danger-light { background-color: #fff1f0 !important; }
    .bg-warning-light { background-color: #fffbe6 !important; }
</style>
@endsection
