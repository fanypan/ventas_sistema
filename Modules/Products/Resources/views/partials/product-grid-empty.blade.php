@php
    $message = $message ?? 'No se encontraron productos';
@endphp

<div id="no_results_msg" class="col-12 text-center text-muted py-5 product-grid-empty" style="display:none;">
    <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
    <h5>{{ $message }}</h5>
</div>
