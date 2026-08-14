@extends('admin.layouts.master')

@section('title', 'Estado de cuenta — ' . $customer->name)

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="far fa-newspaper mr-2"></i>Estado de cuenta</h1>
                <p class="text-muted mb-0">{{ $customer->name }} @if($customer->nit) · NIT {{ $customer->nit }} @endif</p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('credits.kardex.customer.pdf', ['id' => $customer->id, 'from' => $from, 'to' => $to]) }}" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('credits.receivables') }}" class="btn btn-secondary">Volver</a>
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
                    <button class="btn btn-primary btn-sm">Filtrar</button>
                </form>
            </div>
        </div>

        <div class="alert alert-light border">
            Saldo: <strong class="{{ $saldo > 0 ? 'text-danger' : 'text-success' }}">{{ money($saldo) }}</strong>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped m-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Usuario</th>
                            <th class="text-right">Cargo</th>
                            <th class="text-right">Abono</th>
                            <th class="text-right">Saldo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y H:i') }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td>{{ $row['user'] }}</td>
                            <td class="text-right">{{ $row['cargo'] ? money($row['cargo']) : '' }}</td>
                            <td class="text-right text-success">{{ $row['abono'] ? money($row['abono']) : '' }}</td>
                            <td class="text-right font-weight-bold">{{ money($row['saldo']) }}</td>
                            <td>
                                @if($row['abono_id'])
                                    <a href="{{ route('credits.receipt.print', $row['abono_id']) }}" target="_blank" class="btn btn-xs btn-default" title="Recibo">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                @elseif($row['type'] === 'factura')
                                    <a href="{{ route('sales.print_ticket', $row['ref']) }}" target="_blank" class="btn btn-xs btn-default" title="Ticket">
                                        <i class="fas fa-clipboard-list"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">Sin movimientos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
</div>
@endsection
