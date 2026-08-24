@extends('platform.layout')
@section('title', 'Alta de cliente')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>Alta de cliente</h1>
        <p class="platform-lead">Después del pago, completá este formulario. El sistema crea la base, el subdominio y manda las credenciales.</p>
    </div>
</div>

@if ($plans->isEmpty())
    <div class="card">
        @include('platform.partials.empty', [
            'title' => 'No hay planes activos',
            'body' => 'Activá un plan antes de dar de alta un comercio.',
            'actionUrl' => route('platform.plans.index'),
            'actionLabel' => 'Ver planes',
        ])
    </div>
@else
<form method="POST" action="{{ route('platform.tenants.store') }}" class="card card-body">
    @csrf
    <div class="platform-form-grid">
        <div class="form-group">
            <label for="name">Nombre del comercio</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug (subdominio)</label>
            <input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" maxlength="56" pattern="[a-z0-9]+" title="Solo letras minúsculas y números, sin guiones" required>
            <small class="form-text"><span id="slug-host">{{ old('slug', 'cliente') }}</span>.{{ config('saas.tenant_base_domain') }} · base <code>tenant_[slug]</code> (letras y números, máx. 56)</small>
        </div>
        <div class="form-group">
            <label for="ruc">RUC</label>
            <input class="form-control @error('ruc') is-invalid @enderror" id="ruc" name="ruc" value="{{ old('ruc') }}" placeholder="80012345-6" autocomplete="off">
            <small class="form-text">Formato: número-DV (ej. 80012345-6). Opcional.</small>
        </div>
        <div class="form-group">
            <label for="plan_id">Plan</label>
            <select class="form-control @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id" required>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }} — {{ money($plan->price_monthly) }}/mes</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="interval">Período</label>
            <select class="form-control" id="interval" name="interval">
                <option value="monthly" @selected(old('interval', 'monthly') === 'monthly')>Mensual</option>
                <option value="yearly" @selected(old('interval') === 'yearly')>Anual</option>
            </select>
        </div>
        <div class="form-group col-start">
            <label for="admin_name">Admin — nombre</label>
            <input class="form-control @error('admin_name') is-invalid @enderror" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required>
        </div>
        <div class="form-group">
            <label for="admin_email">Admin — correo</label>
            <input class="form-control @error('admin_email') is-invalid @enderror" id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}" required>
        </div>
        <div class="platform-form-actions">
            <button class="btn btn-primary" type="submit">Aprovisionar</button>
        </div>
    </div>
</form>
@endif

@push('scripts')
<script>
(function () {
    var name = document.getElementById('name');
    var slug = document.getElementById('slug');
    var host = document.getElementById('slug-host');
    if (!name || !slug || !host) return;
    var dirty = {{ old('slug') ? 'true' : 'false' }};
    function slugify(value) {
        return value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]/g, '').slice(0, 56);
    }
    function hint() {
        host.textContent = slug.value || 'cliente';
    }
    name.addEventListener('input', function () {
        if (!dirty) {
            slug.value = slugify(name.value);
            hint();
        }
    });
    slug.addEventListener('input', function () {
        dirty = true;
        hint();
    });
})();
</script>
@endpush
@endsection
