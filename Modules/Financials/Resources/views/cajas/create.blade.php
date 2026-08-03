@extends('admin.layouts.master')

@section('title', 'Abrir Caja')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Abrir Nueva Caja</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Ingresar Monto Inicial</h3>
                    </div>
                    <form action="{{ route('financials.cajas.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="monto_inicial">Monto Inicial (Efectivo)</label>
                                <input type="text" name="monto_inicial" class="form-control @error('monto_inicial') is-invalid @enderror currency-format" id="monto_inicial" placeholder="0" value="0" required>
                                @error('monto_inicial')
                                    <span class="error invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Abrir Caja</button>
                            <a href="{{ route('financials.cajas.index') }}" class="btn btn-default">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
@endsection
