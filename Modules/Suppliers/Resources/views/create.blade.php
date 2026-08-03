@extends('admin.layouts.master')

@section('title', 'Nuevo Proveedor')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Registrar Nuevo Proveedor</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <form action="{{ route('suppliers.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="nit">NIT/RUC</label>
                                    <input type="text" name="nit" class="form-control" id="nit" placeholder="Opcional">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="name">Nombre / Razón Social</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" required>
                                    @error('name')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="phone">Teléfono</label>
                                    <input type="text" name="phone" class="form-control" id="phone">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" class="form-control" id="email">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="address">Dirección</label>
                                <textarea name="address" class="form-control" id="address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
                            <a href="{{ route('suppliers.index') }}" class="btn btn-default">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
@endsection
