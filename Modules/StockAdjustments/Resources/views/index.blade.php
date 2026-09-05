@extends('admin.layouts.master')

@section('title', 'Ajuste de Stock')

@section('content')
<style>
    .history-panel { overflow-y: auto; flex: 1; }
</style>

<div class="content-wrapper">
    <div class="content-header pb-1">
        <div class="container-fluid">
            <h1 class="m-0 text-warning"><i class="fas fa-sliders-h mr-2"></i>Ajuste de Stock Manual</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="split-layout split-layout--adjustment">

                <!-- LEFT: PRODUCT GRID -->
                <div class="left-panel">
                    @include('products::partials.product-grid-toolbar', [
                        'categories' => $categories,
                        'accent' => 'warning',
                        'placeholder' => 'Buscar producto por código, nombre o marca...',
                    ])

                    <div class="grid-container">
                        <div class="row" id="products_grid">
                            @foreach($products as $prod)
                                @include('products::partials.product-grid-item', [
                                    'product' => $prod,
                                    'variant' => 'adjustment',
                                    'colClass' => 'col-xl-3 col-lg-4 col-md-6 col-6',
                                    'onclick' => 'openAdjustModal('.$prod->id.', \''.addslashes($prod->description).'\', '.$prod->stock.')',
                                ])
                            @endforeach

                            @include('products::partials.product-grid-empty', ['message' => 'Sin resultados'])
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
    initProductGridFilter({ accentClass: 'btn-warning' });

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
            $('#modal_current_stock').text(res.data.new_stock);

            // Update card badge
            let productId = $('#modal_product_id').val();
            $(`[onclick*="${productId}"]`).closest('.product-item')
                .find('.stock-badge-ok, .stock-badge-low')
                .text(res.data.new_stock + ' en stock');

            setTimeout(() => {
                $('#adjustModal').modal('hide');
                location.reload();
            }, 1500);
        }).fail(function(res) {
            let msg = res.responseJSON ? res.responseJSON.message : 'Error al aplicar ajuste';
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
