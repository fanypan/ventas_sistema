@extends('admin.layouts.master')

@section('title', 'Nueva Compra')

@section('content')
<style>
    .cart-container {
        overflow-y: auto;
        flex: 1;
    }
    .cart-table {
        table-layout: fixed;
    }
    .cart-product-name {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-weight: 700;
        font-size: .95rem;
        line-height: 1.3;
        max-width: 100%;
    }
    .cart-product-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        align-items: center;
        margin-top: .2rem;
    }
    .cart-table thead th {
        font-size: .8rem;
        letter-spacing: .02em;
        padding: .7rem .75rem;
    }
    .cart-table td {
        padding: .85rem .75rem;
    }
    .cart-qty {
        font-size: .95rem;
        min-width: 2.25rem;
        padding: .35rem .55rem;
    }
    .cart-line-total {
        font-size: 1rem;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .cart-checkout-bar {
        display: flex;
        flex-direction: column;
        gap: .75rem;
        padding: .85rem 1rem 1rem;
        background: var(--surface);
        border-top: 1px solid var(--border);
    }
    .cart-checkout-total {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: .75rem;
    }
    .cart-checkout-total span {
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
    }
    .cart-checkout-total strong {
        font-size: 1.65rem;
        font-weight: 700;
        line-height: 1.1;
        color: #059669;
        font-variant-numeric: tabular-nums;
        text-align: right;
        word-break: break-word;
    }
    .pos-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
    }
    .cart-checkout-actions .btn {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
    .cart-table td:first-child {
        min-width: 0;
    }
    .supplier-feedback-slot {
        margin-top: .5rem;
    }
    .supplier-feedback-slot.has-feedback {
        min-height: 2.75rem;
    }
    .supplier-selected-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .65rem;
        padding: .5rem .75rem;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 8px;
        font-size: .88rem;
    }
    .supplier-selected-details {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
    }
    .supplier-selected-details strong {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .supplier-not-found-alert {
        font-size: .82rem;
        line-height: 1.35;
        margin-bottom: 0;
    }
    .purchase-success-icon {
        font-size: 3.5rem;
        line-height: 1;
    }
    .purchase-success-total {
        font-size: 1.75rem;
        font-weight: 700;
        color: #059669;
        font-variant-numeric: tabular-nums;
        margin: 0;
    }
    .purchase-inline-alert {
        font-size: .82rem;
        line-height: 1.35;
        margin: 0;
    }
    .right-panel .card-header.bg-primary {
        background-color: var(--primary) !important;
        border-bottom: none !important;
    }
    .right-panel .card-header.bg-primary .card-title {
        color: #fff !important;
    }
    .checkout-hero {
        text-align: center;
        margin-bottom: 1.25rem;
    }
    .checkout-hero .checkout-total {
        font-size: 2.35rem;
        font-weight: 700;
        line-height: 1.1;
        color: #059669;
        font-variant-numeric: tabular-nums;
        margin: 0;
    }
    @media (max-width: 1199.98px) {
        .grid-container,
        .cart-container {
            overflow: visible;
        }
    }
    @media (max-width: 767.98px) {
        .cart-checkout-total strong { font-size: 1.4rem; }
        .checkout-hero .checkout-total { font-size: 1.85rem; }
        .cart-table thead th { font-size: .78rem; padding: .6rem .4rem; }
        .cart-table td { padding: .7rem .4rem; }
    }
</style>

<div class="content-wrapper">
    <div class="content-header pb-1">
        <div class="container-fluid">
            <div class="pos-page-header">
                <h1 class="m-0 text-premium"><i class="fas fa-truck-loading mr-2"></i>Nueva Compra</h1>
                @include('admin.partials.pos-screen-actions', ['context' => 'purchase'])
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="split-layout">
                <div class="left-panel">
                    @include('products::partials.product-grid-toolbar', [
                        'categories' => $categories,
                        'accent' => 'primary',
                        'placeholder' => 'Buscar por código, descripción o marca...',
                    ])

                    <div class="grid-container">
                        <div class="row" id="products_grid">
                            @foreach($products as $prod)
                                @include('products::partials.product-grid-item', [
                                    'product' => $prod,
                                    'variant' => 'purchase',
                                    'wrapperData' => [
                                        'id' => $prod->id,
                                        'code' => $prod->code,
                                        'name' => $prod->description,
                                        'cost' => (int) round($prod->cost),
                                        'stock' => $prod->stock,
                                    ],
                                ])
                            @endforeach

                            @include('products::partials.product-grid-empty')
                        </div>
                    </div>
                </div>

                <div class="right-panel">
                    <div class="card shadow-lg border-0 h-100 d-flex flex-column">
                        <div class="card-header bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                            <h4 class="card-title font-weight-bold mb-0 text-white"><i class="fas fa-clipboard-list mr-2"></i>Lista de ingreso</h4>
                            <div>
                                <button type="button" class="btn btn-xs btn-danger mr-1" id="btn_clear_cart" title="Vaciar lista">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <span class="badge badge-light text-primary px-3 py-1 rounded-pill" id="item_count">0</span>
                            </div>
                        </div>

                        <div class="p-3 bg-light border-bottom">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-truck text-muted"></i></span>
                                </div>
                                <input type="text" id="supplier_search" class="form-control border-left-0" placeholder="Nombre o RUC del proveedor..." autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="button" id="btn_search_supplier" title="Buscar proveedor"><i class="fas fa-search"></i></button>
                                    <button class="btn btn-outline-info" type="button" id="btn_list_suppliers" title="Seleccionar de la lista"><i class="fas fa-list"></i></button>
                                </div>
                            </div>
                            <div class="supplier-feedback-slot">
                                <div id="supplier_not_found_alert" class="alert alert-warning supplier-not-found-alert py-2 px-3" style="display:none;" role="alert">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    No se encontró proveedor con ese nombre o RUC.
                                </div>
                                <div id="supplier_info_panel" class="supplier-selected-card" style="display:none;">
                                    <input type="hidden" id="supplier_id" value="">
                                    <div class="supplier-selected-details">
                                        <strong><span id="lbl_supplier_name" class="text-primary"></span></strong>
                                        <span id="lbl_supplier_nit" class="text-muted small"></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0" id="btn_clear_supplier" title="Quitar proveedor">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="cart-container p-0">
                            <table class="table table-hover table-striped m-0 cart-table">
                                <thead class="bg-white sticky-top">
                                    <tr>
                                        <th style="width:42%">Producto</th>
                                        <th style="width:16%" class="text-center">Cant.</th>
                                        <th style="width:24%" class="text-right">Total</th>
                                        <th style="width:18%" class="text-center"><span class="sr-only">Acciones</span></th>
                                    </tr>
                                </thead>
                                <tbody id="purchase_tbody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fas fa-box-open fa-3x mb-2 opacity-50"></i><br>
                                            Agregue productos a la lista
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="cart-checkout-bar mt-auto">
                            <div id="purchase_error_slot"></div>
                            <div class="cart-checkout-total">
                                <span>Total inversión</span>
                                <strong id="purchase_total_label">Gs. 0</strong>
                            </div>
                            <div class="cart-checkout-actions">
                                <button type="button" class="btn btn-success font-weight-bold text-uppercase shadow-sm" id="btn_finalize_purchase" disabled aria-keyshortcuts="F8 Enter">
                                    <i class="fas fa-save mr-2"></i> Registrar compra <kbd>F8</kbd>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="itemDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Datos de ingreso</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <h5 id="modal_product_name" class="font-weight-bold mb-3"></h5>
                <input type="hidden" id="modal_product_id">
                <input type="hidden" id="modal_edit_index" value="">

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="text-muted small font-weight-bold text-uppercase">Cantidad</label>
                        <input type="number" id="modal_qty" class="form-control form-control-lg text-center font-weight-bold" value="1" min="1">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-muted small font-weight-bold text-uppercase">Costo unitario (Gs.)</label>
                        <input type="text" id="modal_cost" class="form-control form-control-lg text-right font-weight-bold currency-format" placeholder="0" inputmode="numeric">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-0">
                        <label class="text-muted small font-weight-bold text-uppercase">Nº lote <span class="font-weight-normal">(opcional)</span></label>
                        <input type="text" id="modal_lot" class="form-control" maxlength="80">
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <label class="text-muted small font-weight-bold text-uppercase">Vencimiento <span class="font-weight-normal">(opcional)</span></label>
                        <input type="date" id="modal_expiration" class="form-control">
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0">Este costo queda en esta compra. El producto usa el último costo cargado, no un promedio.</p>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary font-weight-bold" id="btn_add_to_purchase_list">
                    Añadir a la lista
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSupplierList" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-truck mr-2"></i>Seleccionar proveedor</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom">
                    <input type="text" id="search_supplier_table" class="form-control" placeholder="Filtrar por nombre o RUC...">
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover m-0">
                        <thead class="bg-white">
                            <tr>
                                <th>RUC</th>
                                <th>Proveedor</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="supplier_list_tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmPurchase" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-save mr-2"></i>Confirmar compra</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Volver">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="checkout-hero">
                    <p class="checkout-total" id="confirm_total_display">Gs. 0</p>
                    <p class="text-muted mb-0" id="confirm_summary">0 productos</p>
                </div>
                <div class="alert alert-light border mb-0">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Proveedor</span>
                        <strong id="confirm_supplier_name"></strong>
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0">Se suma el stock y el costo del producto pasa a ser el de esta compra. El precio de venta no cambia.</p>
            </div>
            <div class="modal-footer bg-light p-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Volver a la lista</button>
                <button type="button" id="btn_confirm_purchase" class="btn btn-success font-weight-bold" aria-keyshortcuts="F8 Enter">
                    <i class="fas fa-check-circle mr-2"></i> Registrar compra <kbd class="checkout-confirm-kbd">Enter</kbd>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPurchaseSuccess" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center overflow-hidden">
            <div class="modal-body p-4 pt-5">
                <div class="purchase-success-icon text-success mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4 class="font-weight-bold mb-2">Compra registrada</h4>
                <p class="text-muted mb-0" id="purchase_success_message">El stock se actualizó correctamente.</p>
                <div class="checkout-change-box mt-3 mb-0">
                    <p class="text-muted small font-weight-bold mb-1">Total</p>
                    <p class="purchase-success-total" id="purchase_success_total">Gs. 0</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
                <button type="button" class="btn btn-success btn-lg px-5 font-weight-bold shadow-sm" id="btn_purchase_success_continue">
                    <i class="fas fa-plus-circle mr-2"></i> Nueva compra
                </button>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.pos-shortcuts-modal', ['context' => 'purchase'])
@endsection

@push('script')
<script>
$(document).ready(function() {
    const suppliers = @json($suppliers->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'nit' => $s->nit])->values());
    let purchaseItems = [];
    let isProcessing = false;

    initProductGridFilter({ accentClass: 'btn-primary', skipEnterOnKeyup: true });

    function formatGs(amount) {
        return 'Gs. ' + Math.round(amount || 0).toLocaleString('de-DE');
    }

    function escapeHtml(str) {
        return $('<div>').text(str == null ? '' : String(str)).html().replace(/"/g, '&quot;');
    }

    function setCurrencyValue($el, amount) {
        $el.val(String(Math.round(amount || 0))).trigger('input');
    }

    function showInlineError(message) {
        $('#purchase_error_slot').html(
            '<div class="alert alert-warning purchase-inline-alert py-2 px-3" role="alert">' +
            '<i class="fas fa-exclamation-circle mr-1"></i>' + escapeHtml(message) +
            '</div>'
        );
    }

    function clearInlineError() {
        $('#purchase_error_slot').empty();
    }

    function syncSupplierFeedbackSlot() {
        const hasFeedback = $('#supplier_not_found_alert').is(':visible') || $('#supplier_info_panel').is(':visible');
        $('.supplier-feedback-slot').toggleClass('has-feedback', hasFeedback);
    }

    function hideSupplierNotFound() {
        $('#supplier_not_found_alert').hide();
        syncSupplierFeedbackSlot();
    }

    function showSupplierNotFound() {
        $('#supplier_info_panel').hide();
        $('#supplier_id').val('');
        $('#supplier_not_found_alert').show();
        syncSupplierFeedbackSlot();
        updateFinalizeState();
    }

    function setSelectedSupplier(supplier) {
        $('#supplier_id').val(supplier.id);
        $('#lbl_supplier_name').text(supplier.name);
        $('#lbl_supplier_nit').text(supplier.nit || '');
        $('#supplier_search').val(supplier.nit || supplier.name);
        hideSupplierNotFound();
        $('#supplier_info_panel').show();
        syncSupplierFeedbackSlot();
        clearInlineError();
        updateFinalizeState();
        $('#filter_search').trigger('focus');
    }

    function clearSelectedSupplier() {
        $('#supplier_id').val('');
        $('#supplier_info_panel').hide();
        hideSupplierNotFound();
        $('#supplier_search').val('').trigger('focus');
        updateFinalizeState();
    }

    function findSuppliers(term) {
        term = (term || '').toLowerCase().trim();
        if (!term) return [];
        return suppliers.filter(function(s) {
            return String(s.name || '').toLowerCase().includes(term) || String(s.nit || '').toLowerCase().includes(term);
        });
    }

    function searchSupplier() {
        const term = $('#supplier_search').val().trim();
        if (!term) {
            hideSupplierNotFound();
            return;
        }
        const matches = findSuppliers(term);
        if (matches.length === 1) {
            setSelectedSupplier(matches[0]);
            return;
        }
        const exact = matches.find(function(s) {
            return String(s.nit || '').toLowerCase() === term.toLowerCase();
        });
        if (exact) {
            setSelectedSupplier(exact);
            return;
        }
        showSupplierNotFound();
    }

    function renderSupplierTable(term) {
        const rows = term ? findSuppliers(term) : suppliers;
        let html = '';
        if (!rows.length) {
            html = '<tr><td colspan="3" class="text-center text-muted py-4">No se encontraron proveedores.</td></tr>';
        } else {
            rows.forEach(function(s) {
                html += '<tr>' +
                    '<td class="align-middle">' + escapeHtml(s.nit || '-') + '</td>' +
                    '<td class="align-middle font-weight-bold">' + escapeHtml(s.name) + '</td>' +
                    '<td class="text-right">' +
                    '<button type="button" class="btn btn-sm btn-success btn-select-supplier rounded-pill px-3" data-id="' + s.id + '">' +
                    '<i class="fas fa-check mr-1"></i> Seleccionar</button>' +
                    '</td></tr>';
            });
        }
        $('#supplier_list_tbody').html(html);
    }

    function openItemModal(product, editIndex) {
        $('#modal_product_id').val(product.id);
        $('#modal_product_name').text(product.description);
        $('#modal_edit_index').val(editIndex == null ? '' : editIndex);
        $('#modal_qty').val(product.quantity || 1);
        setCurrencyValue($('#modal_cost'), product.price || product.cost || 0);
        $('#modal_lot').val(product.lot || '');
        $('#modal_expiration').val(product.expiration || '');
        $('#btn_add_to_purchase_list').text(editIndex == null ? 'Añadir a la lista' : 'Actualizar ítem');
        $('#itemDetailsModal').modal('show');
    }

    $(document).on('click', '.product-picker-card', function() {
        const $item = $(this).closest('.product-item');
        openItemModal({
            id: $item.data('id'),
            description: $item.data('name'),
            cost: parseInt($item.data('cost'), 10) || 0,
            quantity: 1,
            lot: '',
            expiration: ''
        });
    });

    $('#itemDetailsModal').on('shown.bs.modal', function() {
        $('#modal_qty').trigger('focus').trigger('select');
    });

    $('#modal_qty, #modal_cost').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#btn_add_to_purchase_list').trigger('click');
        }
    });

    $('#btn_add_to_purchase_list').click(function() {
        const id = parseInt($('#modal_product_id').val(), 10);
        const desc = $('#modal_product_name').text();
        const qty = parseInt($('#modal_qty').val(), 10);
        const cost = Math.round(window.getCleanNumber($('#modal_cost').val()));
        const lot = $('#modal_lot').val().trim();
        const exp = $('#modal_expiration').val();
        const editIndex = $('#modal_edit_index').val();

        if (!id || qty < 1 || isNaN(qty)) {
            $('#modal_qty').trigger('focus');
            return;
        }
        if (isNaN(cost) || cost < 0) {
            $('#modal_cost').trigger('focus');
            return;
        }

        const item = {
            id: id,
            description: desc,
            quantity: qty,
            price: cost,
            expiration: exp,
            lot: lot
        };

        if (editIndex !== '') {
            purchaseItems[parseInt(editIndex, 10)] = item;
        } else {
            const existing = purchaseItems.findIndex(function(row) {
                return row.id === item.id && (row.lot || '') === item.lot && row.price === item.price;
            });
            if (existing >= 0) {
                purchaseItems[existing].quantity += item.quantity;
            } else {
                purchaseItems.push(item);
            }
        }

        renderPurchaseTable();
        $('#itemDetailsModal').modal('hide');
        $('#filter_search').val('').trigger('focus');
        filterProducts($('.filter-btn.active').attr('data-filter') || 'all', '');
    });

    function renderPurchaseTable() {
        let html = '';
        let total = 0;

        purchaseItems.forEach(function(item, index) {
            const subtotal = item.quantity * item.price;
            total += subtotal;
            const meta = [];
            meta.push('<small class="text-primary">' + formatGs(item.price) + '</small>');
            if (item.lot) meta.push('<span class="badge badge-warning">L: ' + escapeHtml(item.lot) + '</span>');
            if (item.expiration) meta.push('<small class="text-muted">Vence ' + escapeHtml(item.expiration.split('-').reverse().join('/')) + '</small>');

            html += '<tr>' +
                '<td class="align-middle">' +
                    '<span class="cart-product-name" title="' + escapeHtml(item.description) + '">' + escapeHtml(item.description) + '</span>' +
                    '<div class="cart-product-meta">' + meta.join('') + '</div>' +
                '</td>' +
                '<td class="align-middle text-center"><span class="badge badge-light border cart-qty font-weight-bold">' + item.quantity + '</span></td>' +
                '<td class="align-middle text-right font-weight-bold text-success cart-line-total">' + formatGs(subtotal) + '</td>' +
                '<td class="align-middle text-center">' +
                    '<div class="btn-group">' +
                        '<button type="button" class="btn btn-sm btn-outline-info btn-edit-item" data-index="' + index + '" title="Ajustar"><i class="fas fa-edit"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" data-index="' + index + '" title="Quitar"><i class="fas fa-times"></i></button>' +
                    '</div>' +
                '</td>' +
            '</tr>';
        });

        if (!purchaseItems.length) {
            html = '<tr><td colspan="4" class="text-center text-muted py-5"><i class="fas fa-box-open fa-3x mb-2 opacity-50"></i><br>Agregue productos a la lista</td></tr>';
        }

        $('#purchase_tbody').html(html);
        $('#purchase_total_label').text(formatGs(total));
        $('#item_count').text(purchaseItems.length);
        updateFinalizeState();
    }

    function updateFinalizeState() {
        const ready = purchaseItems.length > 0 && !!$('#supplier_id').val();
        $('#btn_finalize_purchase').prop('disabled', !ready || isProcessing);
    }

    $(document).on('click', '.btn-edit-item', function() {
        const index = parseInt($(this).data('index'), 10);
        const item = purchaseItems[index];
        if (!item) return;
        openItemModal(item, index);
    });

    $(document).on('click', '.btn-remove-item', function() {
        purchaseItems.splice(parseInt($(this).data('index'), 10), 1);
        renderPurchaseTable();
    });

    $('#btn_clear_cart').click(function() {
        if (!purchaseItems.length) return;
        purchaseItems = [];
        renderPurchaseTable();
    });

    $('#btn_search_supplier').click(searchSupplier);
    $('#btn_clear_supplier').click(clearSelectedSupplier);
    $('#btn_list_suppliers').click(function() {
        renderSupplierTable($('#search_supplier_table').val());
        $('#modalSupplierList').modal('show');
    });
    $('#search_supplier_table').on('keyup', function() {
        renderSupplierTable($(this).val());
    });
    $(document).on('click', '.btn-select-supplier', function() {
        const id = parseInt($(this).data('id'), 10);
        const supplier = suppliers.find(function(s) { return s.id === id; });
        if (supplier) setSelectedSupplier(supplier);
        $('#modalSupplierList').modal('hide');
    });

    $('#supplier_search').on('input', function() {
        hideSupplierNotFound();
        if (!$(this).val().trim()) {
            $('#supplier_id').val('');
            $('#supplier_info_panel').hide();
            syncSupplierFeedbackSlot();
            updateFinalizeState();
        }
    });

    $('#supplier_search').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchSupplier();
        }
    });

    function searchAndAddProduct(term) {
        term = (term || '').trim().toLowerCase();
        if (!term) return;
        let $match = $('.product-item').filter(function() {
            return String($(this).data('code') || '').toLowerCase() === term;
        }).first();
        if (!$match.length) {
            $match = $('.product-item:visible').filter(function() {
                return String($(this).attr('data-search') || '').includes(term);
            }).first();
        }
        if (!$match.length) {
            showInlineError('Producto no encontrado.');
            return;
        }
        clearInlineError();
        $match.find('.product-picker-card').trigger('click');
        $('#filter_search').val('');
        filterProducts($('.filter-btn.active').attr('data-filter') || 'all', '');
    }

    $('#filter_search').on('keydown', function(e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const term = $(this).val().trim();
        if (term) {
            searchAndAddProduct(term);
            return;
        }
        if (!$('#btn_finalize_purchase').prop('disabled')) {
            $('#btn_finalize_purchase').trigger('click');
        }
    });

    $('#btn_finalize_purchase').click(function() {
        clearInlineError();
        const supplierId = $('#supplier_id').val();
        if (!supplierId) {
            showInlineError('Seleccioná un proveedor para registrar la compra.');
            $('#supplier_search').trigger('focus');
            return;
        }
        if (!purchaseItems.length) {
            showInlineError('Agregá al menos un producto.');
            return;
        }
        const total = purchaseItems.reduce(function(sum, item) { return sum + (item.quantity * item.price); }, 0);
        $('#confirm_total_display').text(formatGs(total));
        $('#confirm_summary').text(purchaseItems.length + (purchaseItems.length === 1 ? ' producto' : ' productos'));
        $('#confirm_supplier_name').text($('#lbl_supplier_name').text());
        $('#modalConfirmPurchase').modal('show');
    });

    $('#btn_confirm_purchase').click(function() {
        if (isProcessing) return;
        isProcessing = true;
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Registrando...');
        $('#btn_finalize_purchase').prop('disabled', true);

        $.post("{{ route('purchases.store') }}", {
            _token: "{{ csrf_token() }}",
            supplier_id: $('#supplier_id').val(),
            items: purchaseItems
        }, function(res) {
            $('#modalConfirmPurchase').modal('hide');
            $('#purchase_success_message').text('Compra #' + res.data.id + ' registrada. Stock actualizado.');
            $('#purchase_success_total').text(formatGs(res.data.attributes.total));
            $('#modalPurchaseSuccess').modal('show');
        }).fail(function(xhr) {
            isProcessing = false;
            $btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> Registrar compra <kbd class="checkout-confirm-kbd">Enter</kbd>');
            updateFinalizeState();
            $('#modalConfirmPurchase').modal('hide');
            const payload = xhr.responseJSON || {};
            const msg = payload.message || 'No se pudo registrar la compra.';
            showInlineError(msg);
        });
    });

    function finishPurchaseAndReset() {
        window.location.href = "{{ route('purchases.create') }}";
    }

    $('#btn_purchase_success_continue').click(finishPurchaseAndReset);

    $(document).on('keydown', function(e) {
        if ($('#modalPurchaseSuccess').hasClass('show') && (e.key === 'Escape' || e.key === 'Enter')) {
            e.preventDefault();
            finishPurchaseAndReset();
            return;
        }
        if (e.key !== 'Enter' || e.shiftKey || e.ctrlKey || e.altKey || e.repeat) return;
        const $target = $(e.target);
        if ($target.is('textarea')) return;
        if ($('#modalConfirmPurchase').hasClass('show')) {
            e.preventDefault();
            $('#btn_confirm_purchase').trigger('click');
        }
    });

    function toggleShortcutHelp() {
        $('#modalPosShortcuts').modal('toggle');
    }

    function focusProductSearch() {
        $('#filter_search').trigger('focus').trigger('select');
    }

    initPosShortcuts({
        allowWhenTyping: ['F2', 'F4', 'F8', 'Shift+F4', 'Alt+Shift+H'],
        actions: {
            F2: focusProductSearch,
            '/': focusProductSearch,
            F4: function () {
                $('#supplier_search').trigger('focus').trigger('select');
            },
            'Shift+F4': function () {
                $('#btn_list_suppliers').trigger('click');
            },
            F8: function () {
                if ($('#modalPurchaseSuccess').hasClass('show')) {
                    finishPurchaseAndReset();
                    return;
                }
                if ($('#modalConfirmPurchase').hasClass('show')) {
                    $('#btn_confirm_purchase').trigger('click');
                    return;
                }
                if ($('#itemDetailsModal').hasClass('show')) {
                    $('#btn_add_to_purchase_list').trigger('click');
                    return;
                }
                if ($('.modal.show').length) {
                    return;
                }
                if (!$('#btn_finalize_purchase').prop('disabled')) {
                    $('#btn_finalize_purchase').trigger('click');
                }
            },
            '?': toggleShortcutHelp,
            'Alt+Shift+H': toggleShortcutHelp
        }
    });
});
</script>
@endpush
