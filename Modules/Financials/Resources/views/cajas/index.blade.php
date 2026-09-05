@extends('admin.layouts.master')

@section('title', 'Gestión de Cajas')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gestión de Cajas</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('financials.cajas.history') }}" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-history"></i> Histórico de arqueos
                </a>
                @can('create cash')
                <a href="{{ route('financials.cajas.create') }}" class="btn btn-primary">
                    <i class="fas fa-unlock"></i> Abrir Nueva Caja
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-body p-0">
                <table class="table table-striped table-hover m-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Monto Inicial</th>
                            <th>Total Ventas</th>
                            <th>Monto Final</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cajas as $caja)
                        <tr>
                            <td>{{ $caja->user->name ?? 'Usuario no encontrado' }}</td>
                            <td>Gs. {{ number_format($caja->opening_amount, 0, ',', '.') }}</td>
                            <td class="text-success" title="Ventas Contado + Abonos"><strong>Gs. {{ number_format($caja->paidSalesTotal() + $caja->abonos->sum('amount'), 0, ',', '.') }}</strong></td>
                            <td>Gs. {{ number_format($caja->closing_amount, 0, ',', '.') }}</td>
                            <td>{{ $caja->opened_at }}</td>
                            <td>{{ $caja->closed_at ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $caja->isOpen() ? 'success' : 'danger' }}">
                                    {{ $caja->isOpen() ? 'Abierta' : 'Cerrada' }}
                                </span>
                            </td>
                            <td>
                                @if($caja->isOpen() && (int) $caja->user_id === (int) auth()->id())
                                    @can('create sale')
                                    <a href="{{ route('sales.pos') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-cash-register"></i> Ir a vender
                                    </a>
                                    @endcan
                                @endif
                                <a href="{{ route('financials.cajas.arqueo', $caja->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-balance-scale"></i> {{ $caja->isOpen() ? 'Arqueo' : 'Ver' }}
                                </a>
                                @if($caja->isOpen())
                                @can('update cash')
                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalCloseCaja{{ $caja->id }}">
                                    <i class="fas fa-lock"></i> Cerrar
                                </button>
                                @endcan
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $cajas->links() }}
            </div>
        </div>
    </div>
</section>
</div>
@endsection

@section('modal')
@can('update cash')
@foreach($cajas as $caja)
    @if($caja->status == 1)
    <!-- Modal Cerrar Caja -->
    <div class="modal fade" id="modalCloseCaja{{ $caja->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('financials.cajas.close', $caja->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Cerrar Caja #{{ $caja->id }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                            <div class="form-group">
                                <label>Monto Final en Efectivo</label>
                                <input type="text" name="monto_final" class="form-control currency-format" required placeholder="0">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar Cierre</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach
@endcan
@endsection
