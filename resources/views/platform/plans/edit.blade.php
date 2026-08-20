@extends('platform.layout')
@section('title', 'Editar plan')
@section('content')
<h1>{{ $plan->name }}</h1>
<form method="POST" action="{{ route('platform.plans.update', $plan) }}" class="card card-body">
    @csrf @method('PUT')
    <div class="form-group"><label>Nombre</label><input class="form-control" name="name" value="{{ $plan->name }}" required></div>
    <div class="form-group"><label>Precio mensual</label><input class="form-control" type="number" name="price_monthly" value="{{ $plan->price_monthly }}" required></div>
    <div class="form-group"><label>Precio anual</label><input class="form-control" type="number" name="price_yearly" value="{{ $plan->price_yearly }}" required></div>
    <div class="form-group"><label>Máx. usuarios</label><input class="form-control" type="number" name="max_users" value="{{ $plan->max_users }}" required></div>
    <div class="form-group"><label>Máx. cajas abiertas</label><input class="form-control" type="number" name="max_cajas" value="{{ $plan->max_cajas }}" required></div>
    <div class="form-group"><label>Documentos SIFEN / mes</label><input class="form-control" type="number" name="sifen_documents_monthly" value="{{ $plan->sifen_documents_monthly }}" required></div>
    <div class="form-group"><label>Descripción</label><textarea class="form-control" name="description">{{ $plan->description }}</textarea></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($plan->is_active)><label class="form-check-label">Activo</label></div>
    <button class="btn btn-primary">Guardar</button>
</form>
@endsection
