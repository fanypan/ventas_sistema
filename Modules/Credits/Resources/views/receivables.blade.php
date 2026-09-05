@extends('admin.layouts.master')

@section('title', 'Cuentas por Cobrar')

@section('content')
<style>
    .anim-pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
    .row-overdue {
        background-color: rgba(220, 53, 69, 0.05) !important;
    }
    .receivables-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }
    .receivables-actions .btn {
        margin: 0;
    }
    .sale-meta {
        display: block;
        margin-top: .2rem;
        color: #6c757d;
        font-size: .85rem;
        line-height: 1.35;
    }
    .sale-meta strong {
        color: #495057;
    }
    .sale-items {
        margin-top: .35rem;
        padding-left: 1rem;
        color: #6c757d;
        font-size: .84rem;
    }
    .sale-items li + li {
        margin-top: .15rem;
    }
    .sale-items-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: .5rem;
        padding: .75rem;
        margin-bottom: 1rem;
    }
    .credit-alert-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .2rem .65rem;
        font-size: .78rem;
        font-weight: 700;
        margin-top: .35rem;
        margin-right: .35rem;
    }
    .credit-alert-danger {
        background: #fee2e2;
        color: #991b1b;
    }
    .credit-alert-warning {
        background: #fef3c7;
        color: #92400e;
    }
    .credit-alert-info {
        background: #dbeafe;
        color: #1d4ed8;
    }
    @media (max-width: 767.98px) {
        .mobile-stack-table thead {
            display: none;
        }
        .mobile-stack-table,
        .mobile-stack-table tbody,
        .mobile-stack-table tr,
        .mobile-stack-table td {
            display: block;
            width: 100%;
        }
        .mobile-stack-table tr {
            border-bottom: 1px solid #dee2e6;
            padding: .75rem;
        }
        .mobile-stack-table td {
            border: 0;
            padding: .45rem 0;
            text-align: left !important;
        }
        .mobile-stack-table td::before {
            content: attr(data-label);
            display: block;
            font-weight: 700;
            color: #495057;
            margin-bottom: .2rem;
        }
        .receivables-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .receivables-actions .btn,
        .receivables-actions .input-group,
        .receivables-actions .form-control,
        .receivables-actions .input-group-append {
            width: 100%;
        }
        .modal-dialog {
            margin: .5rem;
        }
        .modal-body {
            padding: .75rem;
        }
    }
</style>
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Cuentas por Cobrar (Clientes)</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        {{-- Mensajes Flash --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i><strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-body">
                <form method="GET" action="{{ route('credits.receivables') }}">
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <label for="customer_search" class="font-weight-bold mb-2">Buscar cliente</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input
                                    type="text"
                                    id="customer_search"
                                    name="customer"
                                    class="form-control"
                                    value="{{ $term ?? '' }}"
                                    placeholder="Escriba nombre, NIT o Nro. de venta">
                            </div>
                            <small class="text-muted">Al buscar un cliente aparecerán directamente sus cuentas pendientes.</small>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <div class="d-flex flex-wrap">
                                <button type="submit" class="btn btn-primary mr-2 mb-2">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                                @if(!empty($term))
                                <a href="{{ route('credits.receivables', ['show_all' => 1]) }}" class="btn btn-secondary mb-2">
                                    <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                                </a>
                                @elseif(!empty($showAll))
                                <a href="{{ route('credits.receivables') }}" class="btn btn-secondary mb-2">
                                    <i class="fas fa-search mr-1"></i> Solo buscador
                                </a>
                                @else
                                <a href="{{ route('credits.receivables', ['show_all' => 1]) }}" class="btn btn-outline-secondary mb-2">
                                    <i class="fas fa-list mr-1"></i> Ver listado completo
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(!is_null($sales))
        @php
            $globalOverdueCount = 0;
            $globalDueTodayCount = 0;
            $globalDueSoonCount = 0;

            foreach ($sales as $saleSummary) {
                foreach ($saleSummary->installments as $instSummary) {
                    if ((int) $instSummary->status !== 0) {
                        continue;
                    }

                    $summaryDueDate = \Carbon\Carbon::parse($instSummary->due_date);

                    if ($summaryDueDate->isPast() && !$summaryDueDate->isToday()) {
                        $globalOverdueCount++;
                    } elseif ($summaryDueDate->isToday()) {
                        $globalDueTodayCount++;
                    } elseif ($summaryDueDate->isFuture() && now()->diffInDays($summaryDueDate) <= 3) {
                        $globalDueSoonCount++;
                    }
                }
            }
        @endphp

        @if($globalOverdueCount > 0 || $globalDueTodayCount > 0 || $globalDueSoonCount > 0)
        <div class="alert alert-warning shadow-sm">
            <div class="d-flex flex-wrap align-items-center">
                <strong class="mr-2"><i class="fas fa-bell mr-1"></i> Alertas de cuotas:</strong>
                @if($globalOverdueCount > 0)
                <span class="credit-alert-chip credit-alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ $globalOverdueCount }} vencida(s)
                </span>
                @endif
                @if($globalDueTodayCount > 0)
                <span class="credit-alert-chip credit-alert-warning">
                    <i class="fas fa-calendar-day"></i> {{ $globalDueTodayCount }} vence(n) hoy
                </span>
                @endif
                @if($globalDueSoonCount > 0)
                <span class="credit-alert-chip credit-alert-info">
                    <i class="fas fa-clock"></i> {{ $globalDueSoonCount }} próxima(s)
                </span>
                @endif
            </div>
        </div>
        @endif

        <div class="card card-outline card-warning">
            <div class="card-header">
                @if(!empty($term))
                <h3 class="card-title mb-0">
                    @if($matchingSales->count() > 1 && !empty($selectedSaleId))
                    Deuda seleccionada para: <strong>{{ $term }}</strong>
                    @else
                    Resultados para: <strong>{{ $term }}</strong>
                    @endif
                </h3>
                @else
                <h3 class="card-title mb-0">Listado completo de cuentas por cobrar</h3>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover m-0 mobile-stack-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Venta #</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Pendiente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        @php
                            $overdueInstallments = $sale->installments->filter(function ($installment) {
                                $dueDate = \Carbon\Carbon::parse($installment->due_date);
                                return $installment->isPending() && $dueDate->isPast() && !$dueDate->isToday();
                            });

                            $dueTodayInstallments = $sale->installments->filter(function ($installment) {
                                return $installment->isPending() && \Carbon\Carbon::parse($installment->due_date)->isToday();
                            });

                            $dueSoonInstallments = $sale->installments->filter(function ($installment) {
                                $dueDate = \Carbon\Carbon::parse($installment->due_date);
                                return $installment->isPending() && $dueDate->isFuture() && now()->diffInDays($dueDate) <= 3;
                            });

                            $nextPendingInstallment = $sale->installments
                                ->filter->isPending()
                                ->sortBy('due_date')
                                ->first();
                        @endphp
                        <tr>
                            <td data-label="Venta #">
                                <strong>#{{ $sale->id }}</strong>
                                <span class="sale-meta">
                                    Fecha: <strong>{{ optional($sale->created_at)->format('d/m/Y H:i') }}</strong>
                                </span>
                            </td>
                            <td data-label="Cliente">
                                <strong>{{ $sale->customer->name ?? 'Consumidor Final' }}</strong>
                                <span class="sale-meta">
                                    Pago: <strong>{{ ucfirst($sale->payment_type ?? 'credito') }}</strong>
                                </span>
                                @if(($sale->installments_count ?? 0) > 0)
                                <span class="sale-meta">
                                    Cuotas: <strong>{{ $sale->installments()->paid()->count() }}</strong> / <strong>{{ $sale->installments_count }}</strong>
                                </span>
                                @endif
                                @if($nextPendingInstallment)
                                <span class="sale-meta">
                                    Próximo vencimiento:
                                    <strong>{{ \Carbon\Carbon::parse($nextPendingInstallment->due_date)->format('d/m/Y') }}</strong>
                                </span>
                                @endif
                                @if($overdueInstallments->count() > 0)
                                <span class="credit-alert-chip credit-alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $overdueInstallments->count() }} vencida(s)
                                </span>
                                @endif
                                @if($dueTodayInstallments->count() > 0)
                                <span class="credit-alert-chip credit-alert-warning">
                                    <i class="fas fa-calendar-day"></i> vence hoy
                                </span>
                                @endif
                                @if($dueSoonInstallments->count() > 0)
                                <span class="credit-alert-chip credit-alert-info">
                                    <i class="fas fa-hourglass-half"></i> vence pronto
                                </span>
                                @endif
                                @if($sale->details->isNotEmpty())
                                <ul class="sale-items mb-0">
                                    @foreach($sale->details->take(2) as $detail)
                                    <li>{{ $detail->product->description ?? 'Artículo' }} x{{ (int) $detail->quantity }}</li>
                                    @endforeach
                                    @if($sale->details->count() > 2)
                                    <li>y {{ $sale->details->count() - 2 }} artículo(s) más</li>
                                    @endif
                                </ul>
                                @endif
                            </td>
                            <td data-label="Total">{{ number_format($sale->total, 0, ',', '.') }} Gs.</td>
                            <td data-label="Pagado" class="text-success">{{ number_format($sale->total_paid(), 0, ',', '.') }} Gs.</td>
                            <td data-label="Pendiente" class="text-danger"><strong>{{ number_format($sale->pending_balance(), 0, ',', '.') }} Gs.</strong></td>
                            <td data-label="Acciones">
                                <div class="receivables-actions">
                                @can('create credit')
                                <button class="btn btn-primary btn-sm btn-abonar-libre @if($sale->pending_balance() <= 0) disabled @endif" 
                                    data-sale="{{ $sale->id }}" 
                                    data-pending="{{ $sale->pending_balance() }}" 
                                    data-url="{{ route('credits.abono.store') }}" 
                                    data-receipt="{{ route('credits.receipt.print', '___') }}" 
                                    data-token="{{ csrf_token() }}"
                                    @if($sale->pending_balance() <= 0) disabled @endif>
                                    <i class="fas fa-money-bill-wave"></i> Abonar
                                </button>
                                @endcan
                                @if($sale->customer_id)
                                <a href="{{ route('credits.kardex.customer', $sale->customer_id) }}" class="btn btn-outline-primary btn-sm" title="Estado de cuenta">
                                    <i class="far fa-newspaper"></i>
                                </a>
                                @endif
                                @if($sale->installments_count > 0)
                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalCuotas{{ $sale->id }}">
                                    <i class="fas fa-list-ol"></i> Ver Cuotas
                                </button>
                                @endif
                                
                                <!-- Modal Cuotas Detalladas -->
                                <div class="modal fade" id="modalCuotas{{ $sale->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-info text-white">
                                                <h5 class="modal-title">Cronograma de Pagos - Venta #{{ $sale->id }}</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                @if($sale->details->isNotEmpty())
                                                <div class="sale-items-card">
                                                    <div class="font-weight-bold mb-2">Articulos de la venta</div>
                                                    <ul class="sale-items mb-0">
                                                        @foreach($sale->details as $detail)
                                                        <li>
                                                            {{ $detail->product->description ?? 'Artículo' }}
                                                            x{{ (int) $detail->quantity }}
                                                            <span class="text-muted">- Gs. {{ number_format($detail->quantity * $detail->price, 0, ',', '.') }}</span>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                @endif
                                                <table class="table table-bordered table-sm text-center mobile-stack-table">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th># Cuota</th>
                                                            <th>Vencimiento</th>
                                                            <th>Monto Total</th>
                                                            <th>Pagado</th>
                                                            <th>Pendiente</th>
                                                            <th>Estado</th>
                                                            <th>Acción</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($sale->installments as $inst)
                                                        @php
                                                            $dueDate = \Carbon\Carbon::parse($inst->due_date);
                                                            $isOverdue = $inst->status == 0 && $dueDate->isPast() && !$dueDate->isToday();
                                                        @endphp
                                                        <tr class="{{ $isOverdue ? 'row-overdue' : '' }}">
                                                            <td data-label="# Cuota">{{ $inst->installment_number }}</td>
                                                            <td data-label="Vencimiento">
                                                                {{ \Carbon\Carbon::parse($inst->due_date)->format('d/m/Y') }}
                                                                @if($inst->status == 0)
                                                                    @php
                                                                        $dueDate = \Carbon\Carbon::parse($inst->due_date);
                                                                    @endphp
                                                                    @if($dueDate->isPast() && !$dueDate->isToday())
                                                                        <div><small class="text-danger font-weight-bold">Cuota vencida</small></div>
                                                                    @elseif($dueDate->isToday())
                                                                        <div><small class="text-warning font-weight-bold">Vence hoy</small></div>
                                                                    @elseif($dueDate->isFuture() && now()->diffInDays($dueDate) <= 3)
                                                                        <div><small class="text-primary font-weight-bold">Vence pronto</small></div>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                            <td data-label="Monto Total">{{ number_format($inst->amount, 0, ',', '.') }} Gs.</td>
                                                            <td data-label="Pagado" class="text-success">{{ number_format($inst->paid_amount, 0, ',', '.') }} Gs.</td>
                                                            <td data-label="Pendiente" class="text-danger font-weight-bold">{{ number_format($inst->amount - $inst->paid_amount, 0, ',', '.') }} Gs.</td>
                                                            <td data-label="Estado">
                                                                @if($inst->status == 1)
                                                                    <span class="badge badge-success">Pagado</span>
                                                                    <a href="{{ route('credits.receipt.print', $sale->abonos->where('amount', $inst->amount)->last()->id ?? 0) }}" target="_blank" class="btn btn-xs btn-default ml-1" title="Recibo">
                                                                        <i class="fas fa-print"></i>
                                                                    </a>
                                                                    <br><small>{{ \Carbon\Carbon::parse($inst->paid_at)->format('d/m/Y') }}</small>
                                                                @else
                                                                    @php
                                                                        $dueDate = \Carbon\Carbon::parse($inst->due_date);
                                                                        $isOverdue = $dueDate->isPast() && !$dueDate->isToday();
                                                                        $isDueSoon = $dueDate->isToday() || ($dueDate->isFuture() && $dueDate->diffInDays(now()) <= 3);
                                                                    @endphp
                                                                    
                                                                    @if($isOverdue)
                                                                        <span class="badge badge-danger shadow-sm anim-pulse" title="Vencido">
                                                                            <i class="fas fa-exclamation-circle mr-1"></i>VENCIDO
                                                                        </span>
                                                                    @elseif($isDueSoon)
                                                                        <span class="badge badge-warning" title="Vence Pronto">
                                                                            <i class="fas fa-clock mr-1"></i>PRÓXIMO
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-secondary">Pendiente</span>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                            <td data-label="Acción">
                                                                 @if($inst->status == 0)
                                                                @can('create credit')
                                                                <div class="input-group input-group-sm receivables-actions">
                                                                    <select class="form-control form-control-sm" id="metodo-{{ $inst->id }}">
                                                                        <option value="Efectivo">&#x1F4B5; Efec.</option>
                                                                        <option value="Transferencia">&#x1F3E6; Trans.</option>
                                                                        <option value="QR">&#x1F4F1; QR</option>
                                                                        <option value="Tarjeta">&#x1F4B3; Tarj.</option>
                                                                    </select>
                                                                    <div class="input-group-append">
                                                                        <button type="button"
                                                                            class="btn btn-success btn-sm btn-cobrar-cuota"
                                                                            data-installment="{{ $inst->id }}"
                                                                            data-url="{{ route('credits.installment.pay') }}"
                                                                            data-receipt="{{ route('credits.receipt.print', '___') }}"
                                                                            data-token="{{ csrf_token() }}">
                                                                            <i class="fas fa-cash-register"></i> Cobrar
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                @endcan
                                                                 @else
                                                                     <span class="text-success"><i class="fas fa-check-circle"></i> Pagado</span>
                                                                 @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-default btn-sm" title="Detalle de Venta">
                                    <i class="fas fa-eye"></i>
                                </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                @if(!empty($term) && $matchingSales->count() > 1)
                                Se encontraron varias deudas. Seleccione una en la ventana emergente para verla aquí.
                                @else
                                No se encontraron cuentas pendientes para la búsqueda realizada.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sales instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="card-footer">
                {{ $sales->links() }}
            </div>
            @endif
        </div>
        @else
        <div class="card card-outline card-warning">
            <div class="card-body text-center py-5">
                <i class="fas fa-search-dollar fa-3x text-warning mb-3"></i>
                <h4 class="mb-2">Busque un cliente para ver sus cuentas</h4>
                <p class="text-muted mb-0">Use el buscador de arriba para encontrar rápido las cuentas por cobrar sin cargar primero todo el listado.</p>
            </div>
        </div>
        @endif
    </div>
</section>

@if(!empty($term) && $matchingSales->count() > 1)
<div class="modal fade" id="modalSeleccionDeuda" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-list mr-2"></i>Seleccionar deuda
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3 text-muted">
                    El cliente/búsqueda <strong>{{ $term }}</strong> tiene varias deudas pendientes. Elija cuál desea ver.
                </p>
                <div class="list-group">
                    @foreach($matchingSales as $matchedSale)
                    <a href="{{ route('credits.receivables', ['customer' => $term, 'selected_sale' => $matchedSale->id]) }}"
                        class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div class="mb-2 mb-md-0">
                            <strong>
                                Venta #{{ $matchedSale->id }}
                                @if($matchedSale->details->isNotEmpty())
                                - {{ $matchedSale->details->first()->product->description ?? 'Artículo' }}
                                @if($matchedSale->details->count() > 1)
                                (+{{ $matchedSale->details->count() - 1 }})
                                @endif
                                @endif
                            </strong><br>
                            <span>{{ $matchedSale->customer->name ?? 'Consumidor Final' }}</span>
                            <span class="sale-meta">
                                Fecha: <strong>{{ optional($matchedSale->created_at)->format('d/m/Y H:i') }}</strong>
                            </span>
                            <span class="sale-meta">
                                Pago: <strong>{{ ucfirst($matchedSale->payment_type ?? 'credito') }}</strong>
                                @if(($matchedSale->installments_count ?? 0) > 0)
                                | Cuotas: <strong>{{ $matchedSale->installments()->paid()->count() }}</strong> / <strong>{{ $matchedSale->installments_count }}</strong>
                                @endif
                            </span>
                            @if($matchedSale->details->isNotEmpty())
                            <ul class="sale-items mb-0">
                                @foreach($matchedSale->details->take(2) as $detail)
                                <li>{{ $detail->product->description ?? 'Artículo' }} x{{ (int) $detail->quantity }}</li>
                                @endforeach
                                @if($matchedSale->details->count() > 2)
                                <li>y {{ $matchedSale->details->count() - 2 }} artículo(s) más</li>
                                @endif
                            </ul>
                            @endif
                        </div>
                        <div class="text-md-right">
                            <span class="d-block text-muted small">Total: {{ number_format($matchedSale->total, 0, ',', '.') }} Gs.</span>
                            <span class="d-block text-muted small">Pendiente</span>
                            <strong class="text-danger">{{ number_format($matchedSale->pending_balance(), 0, ',', '.') }} Gs.</strong>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('credits.receivables') }}" class="btn btn-secondary">Cerrar</a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- MODAL: CONFIRMAR PAGO DE CUOTA -->
<div class="modal fade" id="modalPayment" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-cash-register mr-2"></i> Confirmar Pago de Cuota
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <p class="text-muted text-uppercase small font-weight-bold mb-1" id="lbl_monto_cobrar">Monto a Cobrar</p>
                    <h1 class="display-4 font-weight-bold text-success mb-0" id="pay_modal_total_display" style="display:none;">Gs. 0</h1>
                    <input type="text" id="pay_modal_amount_input" class="form-control form-control-lg font-weight-bold text-success text-center mx-auto currency-format" style="font-size: 2.5rem; max-width: 300px; display:none;" placeholder="0">
                    <small id="pay_modal_max_help" class="form-text text-muted" style="display:none;"></small>
                </div>

                <!-- Método seleccionado visualmente -->
                <div class="text-center mb-3" id="pay_modal_method_display_container">
                    <span class="badge badge-pill badge-info px-3 py-2" id="pay_modal_method_display" style="font-size: 1rem;">Método: Efectivo</span>
                </div>

                <!-- Selector de Método de Pago (Abono Libre) -->
                <div class="form-group mb-3 text-center" id="pay_modal_method_selector_container" style="display:none;">
                    <label class="font-weight-bold text-muted small text-uppercase">Método de Pago</label>
                    <select id="pay_modal_method_select" class="form-control form-control-lg text-center font-weight-bold mx-auto" style="max-width: 300px;">
                        <option value="Efectivo">💵 Efectivo</option>
                        <option value="Transferencia">🏦 Transferencia</option>
                        <option value="QR">📱 QR</option>
                        <option value="Tarjeta">💳 Tarjeta</option>
                    </select>
                </div>

                <!-- Sección Efectivo -->
                <div id="section_pay_cash" style="display:none;">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase">Monto Recibido (Gs.)</label>
                        <input type="text" id="pay_modal_received" class="form-control form-control-lg font-weight-bold text-primary text-center currency-format" style="font-size: 2rem;" placeholder="0">
                    </div>
                    <div class="bg-light p-3 rounded border text-center">
                        <p class="text-muted small text-uppercase font-weight-bold mb-1">Vuelto</p>
                        <h2 class="font-weight-bold text-dark mb-0" id="pay_modal_change">Gs. 0</h2>
                    </div>
                </div>

                <!-- Sección Electrónica (QR/Tarjeta/Transf) -->
                <div id="section_pay_electronic" style="display:none;">
                    <div class="form-group">
                        <label class="font-weight-bold text-muted small text-uppercase" id="lbl_reference_title">Nro. Arqueo / Referencia</label>
                        <input type="text" id="pay_modal_reference" class="form-control form-control-lg font-weight-bold text-center" placeholder="Ingrese el número o ID de transacción...">
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label class="font-weight-bold text-muted small text-uppercase">Nota / Observación (Opcional)</label>
                    <textarea id="pay_modal_note" class="form-control" rows="2" placeholder="Alguna observación sobre este pago..."></textarea>
                </div>

                <input type="hidden" id="pay_modal_type">
                <input type="hidden" id="pay_modal_installment_id">
                <input type="hidden" id="pay_modal_url">
                <input type="hidden" id="pay_modal_token">
                <input type="hidden" id="pay_modal_receipt_base">
                <input type="hidden" id="pay_modal_method">
                <input type="hidden" id="pay_modal_amount_val">
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-lg px-4" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_confirm_final_payment" class="btn btn-success btn-lg px-5 font-weight-bold shadow">
                    <i class="fas fa-check-circle mr-2"></i> REGISTRAR COBRO
                </button>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    $(document).ready(function() {
        console.log("Módulo Receivables con Modal Unificado listo.");

        @if($shouldOpenSelector)
        $('#modalSeleccionDeuda').modal('show');
        @endif

        @if(!empty($autoOpenSaleId))
        $('#modalCuotas{{ $autoOpenSaleId }}').modal('show');
        @endif

        // Función para cambiar vistas de Efectivo / Electrónico
        function togglePaymentSections(metodo) {
            if (metodo === 'Efectivo') {
                $('#section_pay_cash').show();
                $('#section_pay_electronic').hide();
                setTimeout(() => $('#pay_modal_received').focus(), 500);
            } else {
                $('#section_pay_cash').hide();
                $('#section_pay_electronic').show();
                
                // Cambiar etiqueta según método
                if(metodo === 'Transferencia') $('#lbl_reference_title').text('Nro. de Transferencia / Comprobante (Opcional)');
                else if(metodo === 'Tarjeta') $('#lbl_reference_title').text('Nro. de Lote / Arqueo / Factura POS (Opcional)');
                else $('#lbl_reference_title').text('ID Transacción QR / Comprobante (Opcional)');
                
                setTimeout(() => $('#pay_modal_reference').focus(), 500);
            }
        }

        // Cambio de método desde el selector interno (Para Abono Libre)
        $('#pay_modal_method_select').change(function() {
            var metodo = $(this).val();
            $('#pay_modal_method').val(metodo);
            togglePaymentSections(metodo);
        });

        // ========== ABRIR MODAL: COBRAR CUOTA ==========
        $(document).on('click', '.btn-cobrar-cuota', function() {
            var $btn = $(this);
            var installmentId = $btn.data('installment');
            var url = $btn.data('url');
            var token = $btn.data('token');
            var receiptBase = $btn.data('receipt');
            
            var metodo = $('#metodo-' + installmentId).val();
            var metodoTexto = $('#metodo-' + installmentId + ' option:selected').text();
            
            var montoTexto = $btn.closest('tr').find('td:nth-child(5)').text();
            var montoNum = window.getCleanNumber(montoTexto);

            if (!metodo) {
                alert('Seleccione el método de pago primero.');
                return;
            }

            // Configurar modal
            $('#pay_modal_type').val('cuota');
            $('#pay_modal_installment_id').val(installmentId);
            $('#pay_modal_url').val(url);
            $('#pay_modal_token').val(token);
            $('#pay_modal_receipt_base').val(receiptBase);
            $('#pay_modal_method').val(metodo);
            $('#pay_modal_amount_val').val(montoNum);

            // UI
            $('#lbl_monto_cobrar').text('Monto a Cobrar');
            $('#pay_modal_total_display').text('Gs. ' + montoNum.toLocaleString('de-DE')).show();
            $('#pay_modal_amount_input').hide();
            $('#pay_modal_max_help').hide();
            
            $('#pay_modal_method_display').text('Método: ' + metodoTexto);
            $('#pay_modal_method_display_container').show();
            $('#pay_modal_method_selector_container').hide();

            // Limpiar campos
            $('#pay_modal_received').val('');
            $('#pay_modal_change').text('Gs. 0').css('color', '#dc2626');
            $('#pay_modal_reference').val('');
            $('#pay_modal_note').val('');

            togglePaymentSections(metodo);
            $('#modalPayment').modal('show');
        });

        // ========== ABRIR MODAL: ABONO LIBRE ==========
        $(document).on('click', '.btn-abonar-libre', function() {
            var $btn = $(this);
            var saleId = $btn.data('sale');
            var url = $btn.data('url');
            var token = $btn.data('token');
            var receiptBase = $btn.data('receipt');
            var pendingAmount = window.getCleanNumber($btn.data('pending'));

            // Configurar modal
            $('#pay_modal_type').val('libre');
            $('#pay_modal_installment_id').val(saleId); // Guardamos ID de Venta aquí
            $('#pay_modal_url').val(url);
            $('#pay_modal_token').val(token);
            $('#pay_modal_receipt_base').val(receiptBase);
            $('#pay_modal_amount_val').val(pendingAmount);

            // UI
            $('#lbl_monto_cobrar').text('Monto a Abonar (Máx: ' + pendingAmount.toLocaleString('de-DE') + ' Gs.)');
            $('#pay_modal_total_display').hide();
            $('#pay_modal_amount_input').val(pendingAmount).show().attr('max', pendingAmount);
            $('#pay_modal_max_help').text('Puede editar este valor si el cliente abona un monto menor.').show();
            
            $('#pay_modal_method_display_container').hide();
            $('#pay_modal_method_selector_container').show();

            // Limpiar y resetear método a Efectivo por defecto
            $('#pay_modal_received').val('');
            $('#pay_modal_change').text('Gs. 0').css('color', '#dc2626');
            $('#pay_modal_reference').val('');
            $('#pay_modal_note').val('');
            
            $('#pay_modal_method_select').val('Efectivo').trigger('change');

            $('#modalPayment').modal('show');
        });

        // ========== OBTENER MONTO ACTUAL ==========
        function getTotalToPay() {
            var type = $('#pay_modal_type').val();
            if (type === 'libre') {
                return window.getCleanNumber($('#pay_modal_amount_input').val());
            }
            return window.getCleanNumber($('#pay_modal_amount_val').val());
        }

        // ========== CÁLCULO DE VUELTO ==========
        $('#pay_modal_received, #pay_modal_amount_input').on('keyup input change', function() {
            let received = window.getCleanNumber($('#pay_modal_received').val());
            let totalToPay = getTotalToPay();
            let change = received - totalToPay;

            let display = change >= 0 
                ? 'Gs. ' + Math.round(change).toLocaleString('de-DE')
                : '-Gs. ' + Math.round(Math.abs(change)).toLocaleString('de-DE');
            
            let color = change >= 0 ? '#059669' : '#dc2626';

            $('#pay_modal_change').text(display).css('color', color);
        });

        // ========== CONFIRMAR Y REGISTRAR COBRO (AJAX) ==========
        $('#btn_confirm_final_payment').click(function() {
            var $btn = $(this);
            var type = $('#pay_modal_type').val();
            var id = $('#pay_modal_installment_id').val();
            var url = $('#pay_modal_url').val();
            var token = $('#pay_modal_token').val();
            var receiptBase = $('#pay_modal_receipt_base').val();
            var metodo = $('#pay_modal_method').val();
            
            var receivedAmount = $('#pay_modal_received').val();
            var reference = $('#pay_modal_reference').val();
            var note = $('#pay_modal_note').val();
            var totalToPay = getTotalToPay();

            // Validaciones
            if (metodo === 'Efectivo') {
                let rec = window.getCleanNumber(receivedAmount);
                if (rec < totalToPay) {
                    alert('El monto recibido no puede ser menor al monto a pagar.');
                    $('#pay_modal_received').focus();
                    return;
                }
            }

            if (type === 'libre') {
                let max = window.getCleanNumber($('#pay_modal_amount_val').val());
                if (totalToPay <= 0 || totalToPay > max) {
                    alert('El monto ingresado es inválido o supera la deuda pendiente.');
                    $('#pay_modal_amount_input').focus();
                    return;
                }
            }

            // Deshabilitar botón
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> PROCESANDO...');

            // Preparar datos Ajax variando según si es cuota o libre
            var ajaxData = {
                _token: token,
                payment_method: metodo,
                received_amount: window.getCleanNumber(receivedAmount),
                reference: reference, // Ya no se valida que sea obligatorio en JS
                note: note
            };

            if (type === 'libre') {
                ajaxData.abonable_id = id;
                ajaxData.abonable_type = 'Modules\\Sales\\Entities\\Sale';
                ajaxData.amount = totalToPay;
            } else {
                ajaxData.installment_id = id;
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: ajaxData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log('Cobro exitoso:', response);

                    $('#modalPayment').modal('hide');

                    var alertHtml = '<div class="alert alert-success alert-dismissible fade show shadow-sm mt-2" role="alert">'
                        + '<i class="fas fa-check-circle mr-2"></i><strong>¡Éxito!</strong> ' + response.message
                        + '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>';
                    $('.container-fluid').first().prepend(alertHtml);

                    // Abrir recibo
                    if (response.abono_id) {
                        var receiptUrl = receiptBase.replace('___', response.abono_id);
                        window.open(receiptUrl, '_blank');
                    }

                    // Recargar tabla
                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                },
                error: function(xhr) {
                    console.error('Error en cobro:', xhr.responseJSON);
                    var msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Error desconocido al registrar el cobro.';

                    alert('Error: ' + msg);

                    $btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> REGISTRAR COBRO');
                }
            });
        });
    });
</script>
@endpush
@endsection
