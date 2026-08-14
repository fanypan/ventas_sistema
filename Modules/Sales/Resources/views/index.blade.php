@extends('admin.layouts.master')

@section('title', 'Historial de Ventas')

@section('content')
<style>
    .badge-pagada    { background: #d1fae5; color: #065f46; }
    .badge-credito   { background: #dbeafe; color: #1e40af; }
    .badge-abono     { background: #fef3c7; color: #92400e; }
    .badge-anulada   { background: #fee2e2; color: #991b1b; }
    .pay-icon        { font-size: .85rem; }
</style>

<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-receipt mr-2 text-primary"></i>Historial de Ventas</h1>
            </div>
            <div class="col-sm-6 text-right">
                @can('create sale')
                <a href="{{ route('sales.pos') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Venta
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Filtros -->
        <div class="card card-outline card-primary mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('sales.index') }}" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="text-muted text-xs mb-1">Desde</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted text-xs mb-1">Hasta</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted text-xs mb-1">Método</label>
                        <select name="payment_type" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="efectivo" {{ request('payment_type') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="qr" {{ request('payment_type') == 'qr' ? 'selected' : '' }}>QR</option>
                            <option value="tarjeta" {{ request('payment_type') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            <option value="transferencia" {{ request('payment_type') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="credito" {{ request('payment_type') == 'credito' ? 'selected' : '' }}>Crédito</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted text-xs mb-1">Estado</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pagada</option>
                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Crédito</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Anulada</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover table-striped m-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nº</th>
                            <th>Fecha/Hora</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th class="text-center">Método</th>
                            <th class="text-center">Estado</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td class="font-weight-bold text-primary">#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $sale->customer->name ?? 'Público General' }}</td>
                            <td>{{ $sale->creator->name ?? '-' }}</td>
                            <td class="text-center">
                                @php
                                    $icons = [
                                        'efectivo' => '<i class="fas fa-money-bill-wave text-success pay-icon" title="Efectivo"></i>',
                                        'qr' => '<i class="fas fa-qrcode text-info pay-icon" title="QR"></i>',
                                        'tarjeta' => '<i class="fas fa-credit-card text-primary pay-icon" title="Tarjeta"></i>',
                                        'transferencia' => '<i class="fas fa-university text-warning pay-icon" title="Transferencia"></i>',
                                        'credito' => '<i class="fas fa-clock text-secondary pay-icon" title="Crédito"></i>',
                                    ];
                                @endphp
                                {!! $icons[$sale->payment_type] ?? '<span class="text-muted">-</span>' !!}
                                <small class="d-block text-muted" style="font-size:.7rem;">{{ ucfirst($sale->payment_type) }}</small>
                                @if($sale->reference_number)
                                    <span class="badge badge-light border text-xs" title="Referencia: {{ $sale->reference_number }}">
                                        <i class="fas fa-hashtag mr-1"></i>{{ $sale->reference_number }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $statuses = [
                                        1 => ['Pagada', 'badge-pagada'],
                                        2 => ['Crédito', 'badge-credito'],
                                        3 => ['Abono', 'badge-abono'],
                                        0 => ['Anulada', 'badge-anulada'],
                                    ];
                                    $st = $statuses[$sale->status] ?? ['?', 'badge-secondary'];
                                @endphp
                                <span class="badge {{ $st[1] }} px-2 py-1 rounded-pill">{{ $st[0] }}</span>
                                @if($sale->payment_type === 'credito' && $sale->installments_count > 1)
                                    <small class="d-block text-muted mt-1">{{ $sale->installments()->where('status', 1)->count() }} / {{ $sale->installments_count }} cuotas</small>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold">
                                {{ money($sale->total) }}
                                @if($sale->interest_amount > 0)
                                    <small class="d-block text-danger" title="Interés incluido">+{{ money($sale->interest_amount) }} i</small>
                                @endif
                            </td>
                            <td class="text-center">
                                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-info btn-sm" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($sale->status != 0)
                                        @can('update sale')
                                        <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning btn-sm" title="Editar cabecera">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('void sale')
                                        <form action="{{ route('sales.void', $sale->id) }}" method="POST" class="d-inline form-void">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm" title="Anular Venta" onclick="return confirm('¿Está seguro de anular esta venta? El stock será devuelto y las cuotas canceladas.')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                        @endcan
                                        @endif
                                        <a href="{{ route('sales.print_ticket', $sale->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Imprimir Ticket">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <a href="{{ route('sales.print_invoice', $sale->id) }}" target="_blank" class="btn btn-primary btn-sm" title="Imprimir Factura">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-receipt fa-3x mb-2 opacity-25 d-block"></i>
                                Sin ventas registradas
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                {{ $sales->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</section>
</div>
@endsection
