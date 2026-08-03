@extends('admin.layouts.master')

@section('title', 'Registrar Egreso')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Registrar Nuevo Egreso</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card card-danger card-outline shadow">
                    <form action="{{ route('financials.expenses.store') }}" method="POST" id="expense-form">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="d-block">Tipo de Egreso</label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                        <label class="btn btn-outline-danger active w-50 py-2">
                                            <input type="radio" name="type" id="type_gasto" value="gasto" checked> 
                                            <i class="fas fa-file-invoice-dollar mr-2"></i> Gasto General
                                        </label>
                                        <label class="btn btn-outline-danger w-50 py-2">
                                            <input type="radio" name="type" id="type_insumo" value="insumo"> 
                                            <i class="fas fa-boxes mr-2"></i> Insumo / Suministro
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" id="container-desc">
                                <label for="description" id="label-desc">Descripción / Concepto</label>
                                <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" id="description" placeholder="Ej. Pago de luz, Alquiler..." value="{{ old('description') }}" required>
                                @error('description')
                                    <span class="error invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group d-none" id="container-insumo-select">
                                <label for="insumo_id">Seleccionar Insumo Existente</label>
                                <div class="input-group">
                                    <select name="insumo_id" id="insumo_id" class="form-control select2">
                                        <option value="">-- Seleccionar Insumo --</option>
                                        @foreach($insumos as $insumo)
                                            <option value="{{ $insumo->id }}" data-price="{{ $insumo->price }}">{{ $insumo->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" id="btn-new-insumo" title="Nuevo Insumo">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="new_insumo" id="new_insumo" value="0">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Monto Total (Gs.)</label>
                                        <input type="text" name="amount" class="form-control @error('amount') is-invalid @enderror currency-format" id="amount" placeholder="0" value="{{ old('amount') }}" required>
                                        @error('amount')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 d-none" id="container-quantity">
                                    <div class="form-group">
                                        <label for="quantity">Cantidad</label>
                                        <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" id="quantity" placeholder="0.00" step="0.01" value="{{ old('quantity') }}">
                                        @error('quantity')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-save mr-2"></i> Guardar Egreso
                            </button>
                            <a href="{{ route('financials.expenses.index') }}" class="btn btn-default">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Información</h5>
                    <p id="info-text">Use <b>Gasto General</b> para egresos administrativos como servicios básicos o alquiler.</p>
                    <p class="d-none" id="info-text-insumo">Use <b>Insumo</b> para compras de suministros que desea catalogar y controlar su stock (ej. tornillos, cables, repuestos).</p>
                </div>
                
                @if(!$openCash)
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Advertencia!</h5>
                        No hay una caja abierta actualmente. Debe abrir caja para registrar egresos.
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    $('input[name="type"]').change(function() {
        if (this.value === 'insumo') {
            $('#container-insumo-select').removeClass('d-none');
            $('#container-quantity').removeClass('d-none');
            $('#info-text-insumo').removeClass('d-none');
            $('#info-text').addClass('d-none');
            $('#container-desc').addClass('d-none');
            $('#insumo_id').attr('required', true);
            $('#quantity').attr('required', true);
            $('#description').attr('required', false);
        } else {
            $('#container-insumo-select').addClass('d-none');
            $('#container-quantity').addClass('d-none');
            $('#info-text-insumo').addClass('d-none');
            $('#info-text').removeClass('d-none');
            $('#container-desc').removeClass('d-none');
            $('#insumo_id').attr('required', false);
            $('#quantity').attr('required', false);
            $('#description').attr('required', true);
            $('#new_insumo').val(0);
        }
    });

    $('#btn-new-insumo').click(function() {
        $('#container-desc').removeClass('d-none');
        $('#container-insumo-select').addClass('d-none');
        $('#new_insumo').val(1);
        $('#label-desc').text('Nombre del Nuevo Insumo');
        $('#description').attr('placeholder', 'Ej. Tornillos Hexagonales...').attr('required', true).focus();
        $('#insumo_id').attr('required', false);
    });

    $('#insumo_id').change(function() {
        let name = $(this).find('option:selected').text();
        if ($(this).val()) {
            $('#description').val(name);
        }
    });
});
</script>
@endpush
