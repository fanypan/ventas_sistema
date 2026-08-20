@extends('platform.layout')
@section('title', 'Planes')
@section('content')
<h1>Planes</h1>
<table class="table">
    <thead><tr><th>Plan</th><th>Mensual</th><th>Usuarios</th><th>Cajas</th><th>SIFEN</th><th></th></tr></thead>
    <tbody>
    @foreach ($plans as $plan)
        <tr>
            <td>{{ $plan->name }}</td>
            <td>Gs. {{ number_format($plan->price_monthly, 0, ',', '.') }}</td>
            <td>{{ $plan->max_users }}</td>
            <td>{{ $plan->max_cajas }}</td>
            <td>{{ $plan->sifen_documents_monthly }}</td>
            <td><a href="{{ route('platform.plans.edit', $plan) }}">Editar</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
