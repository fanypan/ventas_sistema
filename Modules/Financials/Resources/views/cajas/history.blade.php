@extends('admin.layouts.master')

@section('title', 'Histórico de arqueos')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-history mr-2"></i>Histórico de arqueos</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('financials.cajas.index') }}" class="btn btn-secondary">Volver a cajas</a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary mb-3">
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <label class="mr-2">Desde</label>
                    <input type="date" name="from" class="form-control form-control-sm mr-3" value="{{ $from }}">
                    <label class="mr-2">Hasta</label>
                    <input type="date" name="to" class="form-control form-control-sm mr-3" value="{{ $to }}">
                    <label class="mr-2">Mes</label>
                    <input type="month" name="month" class="form-control form-control-sm mr-3" value="{{ request('month') }}">
                    <button class="btn btn-primary btn-sm">Filtrar</button>
                </form>
            </div>
        </div>

        <div class="row">
            @php
                $cards = [
                    ['Inicio', $resumen['inicio'], 'secondary'],
                    ['Efectivo', $resumen['efectivo'], 'success'],
                    ['Transferencia', $resumen['transferencia'], 'warning'],
                    ['QR', $resumen['qr'], 'info'],
                    ['Tarjeta', $resumen['tarjeta'], 'primary'],
                    ['Crédito', $resumen['credito'], 'dark'],
                    ['Abonos', $resumen['abonos'], 'success'],
                    ['Egresos', $resumen['egresos'], 'danger'],
                ];
            @endphp
            @foreach($cards as $card)
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-{{ $card[2] }}">
                    <div class="inner">
                        <h4>{{ money($card[1]) }}</h4>
                        <p>{{ $card[0] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="alert alert-light border">
            Efectivo esperado en sistema (inicio + efectivo + abonos − egresos):
            <strong>{{ money($expectedCash) }}</strong>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped m-0">
                    <thead>
                        <tr>
                            <th>Caja</th>
                            <th>Usuario</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Inicio</th>
                            <th>Cierre físico</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cajas as $caja)
                        <tr>
                            <td>#{{ $caja->id }}</td>
                            <td>{{ $caja->user->name ?? '-' }}</td>
                            <td>{{ $caja->opened_at ? $caja->opened_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $caja->closed_at ? $caja->closed_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ money($caja->opening_amount) }}</td>
                            <td>{{ money($caja->closing_amount) }}</td>
                            <td>
                                <span class="badge badge-{{ $caja->status == 1 ? 'success' : 'secondary' }}">
                                    {{ $caja->status == 1 ? 'Abierta' : 'Cerrada' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('financials.cajas.arqueo', $caja->id) }}" class="btn btn-sm btn-warning">
                                    Ver arqueo
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted">No hay cajas en este período</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
</div>
@endsection
