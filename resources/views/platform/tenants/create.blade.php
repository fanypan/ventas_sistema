@extends('platform.layout')
@section('title', 'Alta de cliente')
@section('content')
<h1>Alta de cliente</h1>
<p class="text-muted">Después del pago, completá este formulario. El sistema crea la base, el subdominio y manda las credenciales.</p>
<form method="POST" action="{{ route('platform.tenants.store') }}" class="card card-body">
    @csrf
    <div class="form-group"><label>Nombre del comercio</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
    <div class="form-group"><label>Slug (subdominio)</label><input class="form-control" name="slug" value="{{ old('slug') }}" required><small>{{ old('slug', 'cliente') }}.{{ config('saas.tenant_base_domain') }}</small></div>
    <div class="form-group"><label>RUC</label><input class="form-control" name="ruc" value="{{ old('ruc') }}"></div>
    <div class="form-group"><label>Plan</label>
        <select class="form-control" name="plan_id" required>
            @foreach ($plans as $plan)
                <option value="{{ $plan->id }}">{{ $plan->name }} — Gs. {{ number_format($plan->price_monthly, 0, ',', '.') }}/mes</option>
            @endforeach
        </select>
    </div>
    <div class="form-group"><label>Período</label>
        <select class="form-control" name="interval">
            <option value="monthly">Mensual</option>
            <option value="yearly">Anual</option>
        </select>
    </div>
    <div class="form-group"><label>Admin — nombre</label><input class="form-control" name="admin_name" value="{{ old('admin_name') }}" required></div>
    <div class="form-group"><label>Admin — correo</label><input class="form-control" type="email" name="admin_email" value="{{ old('admin_email') }}" required></div>
    <button class="btn btn-primary">Aprovisionar</button>
</form>
@endsection
