@extends('platform.layout')
@section('title', 'Editar plan')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>{{ $plan->name }}</h1>
        <p class="platform-lead">Los cambios aplican a la landing y a los clientes nuevos.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('platform.plans.index') }}">Volver</a>
</div>

<form method="POST" action="{{ route('platform.plans.update', $plan) }}" class="card card-body">
    @csrf @method('PUT')
    <div class="platform-form-grid">
        <div class="form-group">
            <label for="name">Nombre</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $plan->name) }}" required>
        </div>
        <div class="form-group">
            <label for="is_active">Estado</label>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $plan->is_active))>
                <label class="form-check-label" for="is_active">Activo</label>
            </div>
        </div>
        <div class="form-group">
            <label for="price_monthly">Precio mensual (Gs.)</label>
            <input class="form-control @error('price_monthly') is-invalid @enderror" id="price_monthly" type="number" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" min="0" required>
        </div>
        <div class="form-group">
            <label for="price_yearly">Precio anual (Gs.)</label>
            <input class="form-control @error('price_yearly') is-invalid @enderror" id="price_yearly" type="number" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly) }}" min="0" required>
        </div>
        <div class="form-group">
            <label for="max_users">Máx. usuarios</label>
            <input class="form-control @error('max_users') is-invalid @enderror" id="max_users" type="number" name="max_users" value="{{ old('max_users', $plan->max_users) }}" min="0" required>
        </div>
        <div class="form-group">
            <label for="max_cajas">Máx. cajas abiertas</label>
            <input class="form-control @error('max_cajas') is-invalid @enderror" id="max_cajas" type="number" name="max_cajas" value="{{ old('max_cajas', $plan->max_cajas) }}" min="0" required>
        </div>
        <div class="form-group">
            <label for="sifen_documents_monthly">Documentos SIFEN / mes</label>
            <input class="form-control @error('sifen_documents_monthly') is-invalid @enderror" id="sifen_documents_monthly" type="number" name="sifen_documents_monthly" value="{{ old('sifen_documents_monthly', $plan->sifen_documents_monthly) }}" min="0" required>
        </div>
        <div class="form-group span-2">
            <label for="description">Descripción</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $plan->description) }}</textarea>
        </div>
        <div class="platform-form-actions">
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </div>
</form>
@endsection
