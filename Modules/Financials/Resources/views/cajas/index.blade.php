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
                <a href="{{ route('financials.cajas.create') }}" class="btn btn-primary">
                    <i class="fas fa-unlock"></i> Abrir Nueva Caja
                </a>
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
                            <td class="text-success" title="Ventas Contado + Abonos"><strong>Gs. {{ number_format($caja->sales->where('status', 1)->sum('total') + $caja->abonos->sum('amount'), 0, ',', '.') }}</strong></td>
                            <td>Gs. {{ number_format($caja->closing_amount, 0, ',', '.') }}</td>
                            <td>{{ $caja->opened_at }}</td>
                            <td>{{ $caja->closed_at ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $caja->status == 1 ? 'success' : 'danger' }}">
                                    {{ $caja->status == 1 ? 'Abierta' : 'Cerrada' }}
                                </span>
                            </td>
                            <td>
                                @if($caja->status == 1)
                                <a href="{{ route('financials.cajas.arqueo', $caja->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-balance-scale"></i> Arqueo
                                </a>
                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalCloseCaja{{ $caja->id }}">
                                    <i class="fas fa-lock"></i> Cerrar
                                </button>

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
@endsection
