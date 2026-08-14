@extends('admin.layouts.master')

@section('title', 'Ajuste de Stock')

@section('content')
<style>
    .adj-product-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 10px;
        overflow: hidden;
    }
    .adj-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border-color: var(--warning) !important;
    }
    .product-img-placeholder {
        height: 90px;
        background: linear-gradient(135deg, #fff8e1 0%, #ffe082 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #f9a825;
    }
    .category-filter { overflow-x: auto; white-space: nowrap; padding-bottom: 8px; }
    .category-filter::-webkit-scrollbar { height: 6px; }
    .category-filter::-webkit-scrollbar-thumb { background-color: #ffe082; border-radius: 10px; }
    .split-layout { height: calc(100vh - 135px); display: flex; gap: 20px; }
    .left-panel  { flex: 7; display: flex; flex-direction: column; overflow: hidden; }
    .right-panel { flex: 3; display: flex; flex-direction: column; }
    .grid-container { overflow-y: auto; padding-right: 5px; flex: 1; }
    .history-panel  { overflow-y: auto; flex: 1; }
    .stock-badge-low  { background: #fee2e2; color: #dc2626; }
    .stock-badge-ok   { background: #d1fae5; color: #059669; }
</style>

<div class="content-wrapper">
    <div class="content-header pb-1">
        <div class="container-fluid">
            <h1 class="m-0 text-warning"><i class="fas fa-sliders-h mr-2"></i>Ajuste de Stock Manual</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="split-layout">

                <!-- LEFT: PRODUCT GRID -->
                <div class="left-panel">
                    <div class="card shadow-sm border-0 mb-3 flex-shrink-0">
                        <div class="card-body p-3">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="filter_search" class="form-control border-left-0 form-control-lg" placeholder="Buscar producto por código, nombre o marca..." autofocus>
                            </div>
                            <div class="category-filter">
                                <button class="btn btn-warning rounded-pill px-4 mr-2 filter-btn active" data-filter="all">Todas</button>
                                @foreach($categories as $cat)
                                    <button class="btn btn-outline-secondary text-dark rounded-pill px-4 mr-2 filter-btn" data-filter="{{ $cat->id }}">{{ $cat->name }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid-container">
                        <div class="row" id="products_grid">
                            @foreach($products as $prod)
                                <div class="col-xl-3 col-lg-4 col-md-6 col-6 mb-3 product-item"
                                     data-category="{{ $prod->category_id }}"
                                     data-search="{{ strtolower($prod->code.' '.$prod->description.' '.$prod->brand) }}">
                                    <div class="card h-100 shadow-sm border adj-product-card"
                                         onclick="openAdjustModal({{ $prod->id }}, '{{ addslashes($prod->description) }}', {{ $prod->stock }})">
                                        <div class="product-img-placeholder">
                                            <i class="fas fa-box-open"></i>
                                        </div>
                                        <div class="card-body p-2 d-flex flex-column">
                                            <span class="badge badge-secondary align-self-start mb-1 text-xs">{{ $prod->code }}</span>
                                            <h6 class="font-weight-bold mb-1 text-truncate" style="font-size:.82rem;" title="{{ $prod->description }}">{{ $prod->description }}</h6>
                                            <small class="text-muted mb-2 d-block text-truncate">{{ $prod->brand }}</small>
                                            <div class="mt-auto">
                                                <span class="px-2 py-1 rounded font-weight-bold small {{ $prod->stock > 0 ? 'stock-badge-ok' : 'stock-badge-low' }}">
                                                    <i class="fas fa-cubes mr-1"></i>{{ $prod->stock }} en stock
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div id="no_results_msg" class="col-12 text-center text-muted py-5" style="display:none;">
                                <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                                <h5>Sin resultados</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: HISTORY -->
                <div class="right-panel">
                    <div class="card shadow-lg border-0 h-100 d-flex flex-column">
                        <div class="card-header bg-warning text-dark p-3">
                            <h4 class="card-title font-weight-bold mb-0"><i class="fas fa-history mr-2"></i>Historial de Ajustes</h4>
                        </div>
                        <div class="history-panel p-0">
                            <table class="table table-hover table-sm m-0">
                                <thead class="bg-white sticky-top">
                                    <tr>
                                        <th width="40%">Producto</th>
                                        <th width="15%" class="text-center">Tipo</th>
                                        <th width="15%" class="text-center">Cant.</th>
                                        <th width="30%">Razón</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($history as $adj)
                                    <tr>
                                        <td class="align-middle border-top-0" style="font-size:.8rem;">
                                            <span class="font-weight-bold d-block text-truncate" style="max-width: 120px;">{{ $adj->product->description ?? 'N/A' }}</span>
                                            <small class="text-muted">{{ $adj->created_at->format('d/m/y H:i') }}</small>
                                        </td>
                                        <td class="align-middle text-center border-top-0">
                                            @if($adj->type === 'entrada')
                                                <span class="badge badge-success"><i class="fas fa-plus"></i> Entrada</span>
                                            @else
                                                <span class="badge badge-danger"><i class="fas fa-minus"></i> Salida</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center border-top-0 font-weight-bold">{{ $adj->quantity }}</td>
                                        <td class="align-middle border-top-0" style="font-size:.75rem;">{{ $adj->reason ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Sin ajustes registrados</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-light border-top p-2 text-right">
                            {{ $history->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- MODAL: Ajuste Rápido -->
<div class="modal fade" id="adjustModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-sliders-h mr-2"></i>Ajustar Stock</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <h5 id="modal_product_name" class="font-weight-bold text-dark border-bottom pb-2 mb-3"></h5>
                <input type="hidden" id="modal_product_id">

                <div class="form-group mb-3">
                    <label class="text-muted">Stock Actual</label>
                    <div class="h3 font-weight-bold text-center p-2 bg-light rounded" id="modal_current_stock">-</div>
                </div>

                <!-- Tipo: Entrada / Salida -->
                <div class="btn-group btn-group-lg w-100 mb-3" role="group">
                    <button type="button" class="btn btn-outline-success btn-type font-weight-bold" data-type="entrada">
                        <i class="fas fa-plus-circle mr-1"></i> ENTRADA
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-type font-weight-bold" data-type="salida">
                        <i class="fas fa-minus-circle mr-1"></i> SALIDA
                    </button>
                </div>
                <input type="hidden" id="modal_type" value="">

                <div class="form-group">
                    <label class="text-muted">Cantidad a Ajustar</label>
                    <input type="number" id="modal_quantity" class="form-control form-control-lg text-center font-weight-bold" value="1" min="1">
                </div>

                <div class="form-group">
                    <label class="text-muted">Razón del Ajuste</label>
                    <select id="modal_reason" class="form-control">
                        <option value="">-- Sin especificar --</option>
                        <option value="Error de conteo">Error de conteo</option>
                        <option value="Merma / Daño">Merma / Daño</option>
                        <option value="Producto devuelto">Producto devuelto</option>
                        <option value="Inventario físico">Inventario físico</option>
                        <option value="Pérdida">Pérdida</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="text-muted">Notas Adicionales (opcional)</label>
                    <textarea id="modal_notes" class="form-control" rows="2" placeholder="Observaciones..."></textarea>
                </div>

                <div id="adj_alert" class="alert" style="display:none;"></div>

                @can('create stock')
                <button type="button" class="btn btn-warning btn-lg btn-block font-weight-bold mt-2" id="btn_apply_adjustment" disabled>
                    <i class="fas fa-check-circle mr-2"></i>APLICAR AJUSTE
                </button>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {

    // Grid filtering
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('btn-warning active').addClass('btn-outline-secondary text-dark');
        $(this).removeClass('btn-outline-secondary text-dark').addClass('btn-warning active');
        filterProducts($(this).attr('data-filter'), $('#filter_search').val().toLowerCase());
    });

    $('#filter_search').on('keyup', function() {
        filterProducts($('.filter-btn.active').attr('data-filter'), $(this).val().toLowerCase());
    });

    function filterProducts(category, search) {
        let visible = 0;
        $('.product-item').each(function() {
            let mc = (category === 'all' || $(this).attr('data-category') == category);
            let ms = (search === '' || $(this).attr('data-search').includes(search));
            if (mc && ms) { $(this).show(); visible++; } else { $(this).hide(); }
        });
        if(visible === 0) $('#no_results_msg').show(); else $('#no_results_msg').hide();
    }

    // Type selection buttons
    $('.btn-type').click(function() {
        $('.btn-type').removeClass('btn-success btn-danger').addClass(function() {
            return $(this).attr('data-type') === 'entrada' ? 'btn-outline-success' : 'btn-outline-danger';
        });
        let type = $(this).attr('data-type');
        if(type === 'entrada') {
            $(this).removeClass('btn-outline-success').addClass('btn-success');
        } else {
            $(this).removeClass('btn-outline-danger').addClass('btn-danger');
        }
        $('#modal_type').val(type);
        $('#btn_apply_adjustment').prop('disabled', false);
    });

    // Apply Adjustment
    $('#btn_apply_adjustment').click(function() {
        let type = $('#modal_type').val();
        if(!type) { alert('Seleccione Entrada o Salida'); return; }

        let qty = parseInt($('#modal_quantity').val());
        if(qty < 1 || isNaN(qty)) { alert('Cantidad inválida'); return; }

        $(this).html('<i class="fas fa-spinner fa-spin mr-2"></i> Aplicando...').prop('disabled', true);
        $('#adj_alert').hide();

        $.post("{{ route('stock.adjustments.store') }}", {
            _token: "{{ csrf_token() }}",
            product_id: $('#modal_product_id').val(),
            type: type,
            quantity: qty,
            reason: $('#modal_reason').val(),
            notes: $('#modal_notes').val()
        }, function(res) {
            $('#adj_alert').removeClass('alert-danger').addClass('alert-success')
                .html('<i class="fas fa-check mr-1"></i> ' + res.message).show();
            $('#modal_current_stock').text(res.new_stock);

            // Update card badge
            let productId = $('#modal_product_id').val();
            $(`[onclick*="${productId}"]`).closest('.product-item')
                .find('.stock-badge-ok, .stock-badge-low')
                .text(res.new_stock + ' en stock');

            setTimeout(() => {
                $('#adjustModal').modal('hide');
                location.reload();
            }, 1500);
        }).fail(function(res) {
            let msg = res.responseJSON ? res.responseJSON.error : 'Error al aplicar ajuste';
            $('#adj_alert').removeClass('alert-success').addClass('alert-danger')
                .html('<i class="fas fa-times mr-1"></i> ' + msg).show();
            $('#btn_apply_adjustment').html('<i class="fas fa-check-circle mr-2"></i>APLICAR AJUSTE').prop('disabled', false);
        });
    });
});

function openAdjustModal(id, name, stock) {
    $('#modal_product_id').val(id);
    $('#modal_product_name').text(name);
    $('#modal_current_stock').text(stock);
    $('#modal_type').val('');
    $('#modal_quantity').val(1);
    $('#modal_reason').val('');
    $('#modal_notes').val('');
    $('#adj_alert').hide();
    $('#btn_apply_adjustment').prop('disabled', true);

    // Reset type buttons
    $('.btn-type').each(function() {
        let t = $(this).attr('data-type');
        $(this).removeClass('btn-success btn-danger')
               .addClass(t === 'entrada' ? 'btn-outline-success' : 'btn-outline-danger');
    });

    $('#adjustModal').modal('show');
    setTimeout(() => { $('#modal_quantity').focus(); }, 400);
}
</script>
@endpush
