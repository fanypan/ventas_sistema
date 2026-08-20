@extends('platform.layout')
@section('title', 'Ingresar')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Staff AranduTech</div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.login.attempt') }}">
                    @csrf
                    <div class="form-group">
                        <label>Correo</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input class="form-control" type="password" name="password" required>
                    </div>
                    <button class="btn btn-primary btn-block">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
