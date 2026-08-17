@php
    $previewUrl = $previewUrl ?? asset('images/no-image.png');
    $inputLabel = $inputLabel ?? 'Elegir foto';
@endphp

<style>
    .product-image-dropzone {
        min-height: 220px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }

    .product-image-dropzone.dragover {
        border-color: #007bff !important;
        background-color: #eef5ff !important;
    }

    .product-image-dropzone img {
        max-height: 180px;
        object-fit: contain;
    }

    .product-image-dropzone .dropzone-hint {
        margin-top: 0.75rem;
        line-height: 1.4;
    }
</style>

<div id="image-dropzone" class="product-image-dropzone mb-3 border rounded p-3 bg-light">
    <img id="image-preview" src="{{ $previewUrl }}" class="img-fluid rounded" alt="Vista previa del producto">
    <div class="dropzone-hint text-muted small text-center">
        <i class="fas fa-cloud-upload-alt fa-lg d-block mb-2"></i>
        Arrastrá una imagen aquí o hacé clic para seleccionar
    </div>
</div>

<input type="file" class="d-none @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
<label for="image" class="btn btn-outline-secondary btn-sm btn-block mb-0">{{ $inputLabel }}</label>

@error('image')
    <small class="text-danger mt-2 d-block">{{ $message }}</small>
@enderror

<p class="text-muted small mt-2 mb-0">Formatos sugeridos: JPG, PNG.<br>Peso máximo: 2MB.</p>

@once
@push('script')
<script>
$(document).ready(function() {
    const $dropzone = $('#image-dropzone');
    const $input = $('#image');
    const $preview = $('#image-preview');
    const $hint = $dropzone.find('.dropzone-hint');

    if ($preview.attr('src') && !String($preview.attr('src')).includes('no-image.png')) {
        $hint.hide();
    }

    function previewImage(file) {
        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            $preview.attr('src', event.target.result);
            $hint.hide();
        };
        reader.readAsDataURL(file);
    }

    function assignFile(file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        $input[0].files = dataTransfer.files;
        previewImage(file);
    }

    $dropzone.on('click', function(e) {
        if ($(e.target).is('#image')) {
            return;
        }
        $input.trigger('click');
    });

    $input.on('change', function() {
        if (this.files[0]) {
            previewImage(this.files[0]);
        }
    });

    $dropzone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });

    $dropzone.on('dragleave dragend drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });

    $dropzone.on('drop', function(e) {
        const file = e.originalEvent.dataTransfer.files[0];
        if (file) {
            assignFile(file);
        }
    });
});
</script>
@endpush
@endonce
