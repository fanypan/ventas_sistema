@extends('admin.layouts.master')

@section('content')
<div class="content-wrapper">
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Editar Producto</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Productos</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-9">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Formulario de Edición</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="code">Código/Barras</label>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" placeholder="Opcional" value="{{ old('code', $product->code) }}">
                                        @error('code')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category_id">Categoría</label>
                                        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                            <option value="">Seleccione una categoria</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Descripción / Nombre del Equipo</label>
                                <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" placeholder="Ej. Heladera No Frost" value="{{ old('description', $product->description) }}" required>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <!-- Appliance Specific Fields -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="brand_id">Marca</label>
                                        <select name="brand_id" id="brand_id" class="form-control @error('brand_id') is-invalid @enderror">
                                            <option value="">Seleccione marca (opcional)</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('brand_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="model_name">Modelo</label>
                                        <input type="text" class="form-control @error('model_name') is-invalid @enderror" id="model_name" name="model_name" placeholder="Ej. WRM56" value="{{ old('model_name', $product->model_name) }}">
                                        @error('model_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="warranty_months">Garantía (Meses)</label>
                                        <input type="number" class="form-control @error('warranty_months') is-invalid @enderror" id="warranty_months" name="warranty_months" placeholder="12" value="{{ old('warranty_months', $product->warranty_months) }}" min="0">
                                        @error('warranty_months')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cost">Costo de Compra</label>
                                        <input type="text" class="form-control @error('cost') is-invalid @enderror currency-format" id="cost" name="cost" placeholder="0" value="{{ old('cost', $product->cost) }}" required>
                                        @error('cost')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="profit_margin">Margen Ganancia %</label>
                                        <input type="number" step="0.1" class="form-control" id="profit_margin" placeholder="70" value="{{ $product->cost > 0 ? number_format((($product->price - $product->cost) / $product->cost) * 100, 1) : 0 }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="price">Precio de Venta</label>
                                        <input type="text" class="form-control @error('price') is-invalid @enderror currency-format" id="price" name="price" placeholder="0" value="{{ old('price', $product->price) }}" required>
                                        @error('price')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="stock">Stock</label>
                                        <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" placeholder="0" value="{{ old('stock', $product->stock) }}" required>
                                        @error('stock')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status', $product->status) == 1 ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('status', $product->status) == 0 ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-warning shadow-sm">
                                <i class="fas fa-sync-alt mr-2"></i> Actualizar Producto
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-default shadow-sm">Cancelar</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title text-center d-block w-100">Foto Producto</h3>
                        </div>
                        <div class="card-body text-center">
                            <div id="image-preview-container" class="mb-3 border rounded p-2 bg-light" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                @if($product->image)
                                    <img id="image-preview" src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Foto actual">
                                @else
                                    <img id="image-preview" src="{{ asset('images/no-image.png') }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Sin foto">
                                @endif
                            </div>
                            <div class="form-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                    <label class="custom-file-label" for="image">Cambiar foto</label>
                                </div>
                                @error('image')
                                    <small class="text-danger mt-2 d-block">{{ $message }}</small>
                                @enderror
                            </div>
                            <p class="text-muted small mt-2">Formatos sugeridos: JPG, PNG.<br>Peso máximo: 2MB.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    const $cost = $('#cost');
    const $margin = $('#profit_margin');
    const $price = $('#price');

    function updatePrice() {
        let costVal = window.getCleanNumber($cost.val());
        let marginVal = parseFloat($margin.val()) || 0;
        if (costVal > 0) {
            let priceVal = costVal * (1 + (marginVal / 100));
            $price.val(Math.round(priceVal)).trigger('input');
        }
    }

    function updateMargin() {
        let costVal = window.getCleanNumber($cost.val());
        let priceVal = window.getCleanNumber($price.val());
        if (costVal > 0 && priceVal > 0) {
            let marginVal = ((priceVal - costVal) / costVal) * 100;
            $margin.val(marginVal.toFixed(1));
        }
    }

    $cost.on('input', updatePrice);
    $margin.on('input', updatePrice);
    $price.on('input', updateMargin);

    // Image preview
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#image-preview').attr('src', event.target.result);
            }
            reader.readAsDataURL(file);
            $(this).next('.custom-file-label').html(file.name);
        }
    });

    // Custom file input label update
    $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
});
</script>
@endpush
