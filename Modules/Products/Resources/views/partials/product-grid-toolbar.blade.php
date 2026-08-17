@php
    $accent = $accent ?? 'primary';
    $placeholder = $placeholder ?? 'Buscar por código, descripción o marca...';
    $autofocus = $autofocus ?? true;
    $accentBtn = 'btn-' . $accent;
@endphp

<div class="card shadow-sm border-0 mb-3 flex-shrink-0 product-grid-toolbar product-grid-toolbar--{{ $accent }}">
    <div class="card-body p-3">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
            </div>
            <input type="text"
                   id="filter_search"
                   class="form-control border-left-0 form-control-lg"
                   placeholder="{{ $placeholder }}"
                   autocomplete="off"
                   @if($autofocus) autofocus @endif>
        </div>
        <div class="category-filter" id="category_filters">
            <button type="button" class="btn {{ $accentBtn }} rounded-pill px-4 mr-2 filter-btn active" data-filter="all">Todas</button>
            @foreach($categories as $cat)
                <button type="button" class="btn rounded-pill px-4 mr-2 filter-btn filter-btn--idle" data-filter="{{ $cat->id }}">{{ $cat->name }}</button>
            @endforeach
        </div>
    </div>
</div>
