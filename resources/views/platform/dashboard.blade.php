@extends('platform.layout')
@section('title', 'Panel')
@section('content')
<h1 class="mb-4">Panel</h1>
<div class="row">
    <div class="col-md-3"><div class="small-box bg-info"><div class="inner"><h3>{{ $tenants }}</h3><p>Clientes</p></div></div></div>
    <div class="col-md-3"><div class="small-box bg-success"><div class="inner"><h3>{{ $active }}</h3><p>Activos</p></div></div></div>
    <div class="col-md-3"><div class="small-box bg-warning"><div class="inner"><h3>{{ $grace }}</h3><p>En gracia</p></div></div></div>
    <div class="col-md-3"><div class="small-box bg-danger"><div class="inner"><h3>{{ $suspended }}</h3><p>Pausados</p></div></div></div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="card"><div class="card-header">Últimos clientes</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    @foreach ($recentTenants as $tenant)
                        <tr>
                            <td><a href="{{ route('platform.tenants.show', $tenant) }}">{{ $tenant->name }}</a></td>
                            <td>{{ $tenant->status }}</td>
                            <td>{{ $tenant->plan?->name }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-header">Últimos pagos</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    @foreach ($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->tenant?->name }}</td>
                            <td>Gs. {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td>{{ $payment->method }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
