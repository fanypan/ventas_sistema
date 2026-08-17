@php
    $variant = $variant ?? 'sale';
    $colClass = $colClass ?? 'col-xl-3 col-lg-4 col-md-4 col-6';
    $brandName = optional($product->brand)->name ?? '';
    $search = strtolower(trim($product->code.' '.$product->description.' '.$brandName.' '.($product->model_name ?? '')));
    $wrapperData = $wrapperData ?? [];
@endphp

<div class="{{ $colClass }} mb-3 product-item"
     data-category="{{ $product->category_id }}"
     data-search="{{ $search }}"
     @foreach($wrapperData as $attrKey => $attrValue)
         data-{{ $attrKey }}="{{ $attrValue }}"
     @endforeach
>
    <div class="card h-100 shadow-sm border product-picker-card product-picker-card--{{ $variant }}"
         @if(!empty($onclick)) onclick="{{ $onclick }}" @endif>
        <div class="product-img-placeholder">
            @if(!$product->usesDefaultImage())
                <img src="{{ $product->imageUrl() }}" alt="" loading="lazy">
            @else
                <i class="fas fa-box-open product-picker-card__icon"></i>
            @endif
        </div>
        <div class="card-body p-2 d-flex flex-column">
            <span class="badge product-picker-code align-self-start mb-1">{{ $product->code }}</span>
            <h6 class="font-weight-bold mb-1 text-truncate" title="{{ $product->description }}">{{ $product->description }}</h6>
            <small class="product-picker-meta text-truncate d-block mb-2">{{ $brandName }} {{ $product->model_name }}</small>

            @if($variant === 'adjustment')
                <div class="mt-auto">
                    <span class="px-2 py-1 rounded font-weight-bold small {{ $product->stock > 0 ? 'stock-badge-ok' : 'stock-badge-low' }}">
                        <i class="fas fa-cubes mr-1"></i>{{ $product->stock }} en stock
                    </span>
                </div>
            @else
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    @if($variant === 'purchase')
                        <h5 class="product-picker-price font-weight-bold mb-0">{{ money((int) round($product->cost)) }}</h5>
                        <span class="badge product-picker-stock {{ $product->stock > 0 ? '' : 'is-empty' }}">{{ $product->stock }} stk</span>
                    @else
                        <h5 class="product-picker-price font-weight-bold mb-0">{{ money($product->price) }}</h5>
                        <span class="badge product-picker-stock {{ $product->stock > 0 ? '' : 'is-empty' }}">{{ $product->stock }} en stock</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
