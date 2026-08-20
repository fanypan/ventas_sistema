@extends('platform.layout')
@section('title', 'Clientes')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1>Clientes</h1>
    <a class="btn btn-primary" href="{{ route('platform.tenants.create') }}">Nuevo cliente</a>
</div>
<div class="card">
    <table class="table mb-0">
        <thead><tr><th>Comercio</th><th>Subdominio</th><th>Plan</th><th>Estado</th><th>Vence</th></tr></thead>
        <tbody>
        @foreach ($tenants as $tenant)
            <tr>
                <td><a href="{{ route('platform.tenants.show', $tenant) }}">{{ $tenant->name }}</a></td>
                <td>{{ $tenant->primaryDomain() }}</td>
                <td>{{ $tenant->plan?->name }}</td>
                <td>{{ $tenant->status }}</td>
                <td>{{ optional($tenant->subscription?->ends_at)->format('d/m/Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $tenants->links() }}
@endsection
