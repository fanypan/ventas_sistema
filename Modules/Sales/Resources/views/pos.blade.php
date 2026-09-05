@extends('admin.layouts.master')

@section('title', 'Punto de Venta')

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
    .cart-checkout-actions {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: .5rem;
    }
    .cart-checkout-actions .btn {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .pos-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
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
    .checkout-breakdown {
        display: flex;
        justify-content: center;
        gap: 1.25rem;
        margin-top: .5rem;
        font-size: .85rem;
        color: #64748b;
        font-variant-numeric: tabular-nums;
    }
    .checkout-breakdown .is-discount {
        color: #dc2626;
    }
    .cart-table td:first-child {
        min-width: 0;
    }
    .customer-not-found-alert {
        font-size: .82rem;
        line-height: 1.35;
        margin-bottom: 0;
    }
    .customer-not-found-alert .btn-link {
        font-size: inherit;
        vertical-align: baseline;
    }
    .customer-feedback-slot {
        margin-top: .5rem;
    }
    .customer-feedback-slot.has-feedback {
        min-height: 2.75rem;
    }
    .customer-selected-card {
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
    .customer-selected-details {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
    }
    .customer-selected-details strong {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sale-success-icon {
        font-size: 3.5rem;
        line-height: 1;
    }
    .sale-success-change {
        font-size: 1.75rem;
        font-weight: 700;
        color: #059669;
        font-variant-numeric: tabular-nums;
        margin: 0;
    }
    .pay-methods {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .5rem;
    }
    .pay-method-btn {
        width: 100%;
        min-height: 64px;
        line-height: 1.15;
        white-space: normal;
        font-size: .8rem;
    }
    .pay-method-btn:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
    .checkout-cash-received {
        font-size: 1.75rem;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }
    .checkout-change-box {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: .85rem 1rem;
        text-align: center;
    }
    .checkout-change-box .checkout-change {
        font-size: 1.5rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        margin: 0;
        color: #059669;
    }
    @media (max-width: 1199.98px) {
        .grid-container,
        .cart-container {
            overflow: visible;
        }
    }
    @media (max-width: 767.98px) {
        .pay-methods {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .cart-checkout-actions {
            grid-template-columns: 1fr;
        }
        .cart-checkout-total strong {
            font-size: 1.4rem;
        }
        .checkout-hero .checkout-total {
            font-size: 1.85rem;
        }
        .cart-table thead th {
            font-size: .78rem;
            padding: .6rem .4rem;
        }
        .cart-table td {
            padding: .7rem .4rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="content-header pb-1">
        <div class="container-fluid pos-page-header">
            <h1 class="m-0 text-premium"><i class="fas fa-th mr-2"></i>Punto de Venta Interactivo</h1>
            @if ($cashOpen)
                <p class="text-muted mb-0 small">Tu caja #{{ $cashOpen->id }} · inicial {{ money($cashOpen->opening_amount) }}</p>
            @endif
            @include('admin.partials.pos-screen-actions', ['context' => 'sale'])
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(!$cashOpen)
                <div class="alert alert-warning shadow-sm border-0 p-4 text-center mt-3">
                    <i class="fas fa-exclamation-triangle fa-4x mb-3 text-warning"></i>
                    <h2 class="font-weight-bold">CAJA CERRADA</h2>
                    <p class="lead">Abrí tu caja para vender. Cada cajero usa la suya.</p>
                    <a href="{{ route('financials.cajas.create') }}" class="btn btn-warning btn-lg px-5 font-weight-bold">
                        <i class="fas fa-cash-register mr-2"></i> ABRIR MI CAJA
                    </a>
                </div>
                <div style="opacity: 0.3; pointer-events: none;">
            @endif
            <div class="split-layout">
                
                <!-- LEFT PANEL: GRID -->
                <div class="left-panel">
                    @include('products::partials.product-grid-toolbar', [
                        'categories' => $categories,
                        'accent' => 'primary',
                        'placeholder' => 'Buscar por código, código de barras o descripción...',
                    ])

                    <div class="grid-container">
                        <div class="row" id="products_grid">
                            @foreach($products as $prod)
                                @include('products::partials.product-grid-item', [
                                    'product' => $prod,
                                    'variant' => 'sale',
                                    'onclick' => 'addToCartFast('.$prod->id.', '.$prod->stock.', \''.addslashes($prod->description).'\')',
                                ])
                            @endforeach

                            @include('products::partials.product-grid-empty')
                        </div>
                    </div>
                </div>

                <!-- RIGHT PANEL: CART -->
                <div class="right-panel">
                    <div class="card shadow-lg border-0 h-100 d-flex flex-column">
                        <div class="card-header bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                            <h4 class="card-title font-weight-bold mb-0"><i class="fas fa-shopping-cart mr-2"></i>Carrito</h4>
                            <div>
                                <button type="button" class="btn btn-xs btn-danger mr-1" id="btn_clear_cart" title="Vaciar Carrito">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <span class="badge badge-light text-primary px-3 py-1 rounded-pill" id="item_count">0</span>
                            </div>
                        </div>
                        
                        <!-- Info Cliente -->
                        <div class="p-3 bg-light border-bottom">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-user-tag text-muted"></i></span>
                                </div>
                                <input type="text" id="customer_search" class="form-control border-left-0" placeholder="NIT o Nombre del cliente..." autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="button" id="btn_search_customer" title="Buscar cliente"><i class="fas fa-search"></i></button>
                                    <button class="btn btn-outline-info" type="button" id="btn_list_customers" title="Seleccionar de la lista"><i class="fas fa-list"></i></button>
                                    @can('create customer')
                                    <button class="btn btn-success" type="button" id="btn_open_new_customer" title="Nuevo cliente"><i class="fas fa-plus"></i></button>
                                    @endcan
                                </div>
                            </div>
                            <div class="customer-feedback-slot">
                                <div id="customer_not_found_alert" class="alert alert-warning customer-not-found-alert py-2 px-3" style="display:none;" role="alert">
                                    <i class="fas fa-user-slash mr-1"></i>
                                    No se encontró cliente con ese NIT/RUC. Debe agregarlo para continuar.
                                    @can('create customer')
                                    <button type="button" class="btn btn-link p-0 ml-1 font-weight-bold" id="btn_add_customer_from_search">Agregar cliente</button>
                                    @endcan
                                </div>
                                <div id="customer_info_panel" class="customer-selected-card" style="display:none;">
                                    <input type="hidden" id="customer_id" value="1">
                                    <div class="customer-selected-details">
                                        <strong><span id="lbl_customer_name" class="text-primary">Público General</span></strong>
                                        <span id="lbl_customer_nit" class="text-muted small">0000000</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0" id="btn_clear_customer" title="Quitar cliente">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Carrito Tabla -->
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
                                <tbody id="cart_tbody">
                                    <!-- Items via AJAX -->
                                </tbody>
                            </table>
                        </div>

                        <div class="cart-checkout-bar mt-auto">
                            <div class="cart-checkout-total">
                                <span>Total</span>
                                <strong id="txt_total_sale_display">Gs. 0</strong>
                            </div>
                            <input type="hidden" id="txt_total_sale" value="0">
                            <input type="hidden" id="txt_raw_subtotal" value="0">
                            <input type="hidden" id="selected_payment_method" value="efectivo">
                            <div class="cart-checkout-actions">
                                <button type="button" class="btn btn-success font-weight-bold text-uppercase shadow-sm" id="btn_process_sale" disabled aria-keyshortcuts="F8 Enter">
                                    <i class="fas fa-cash-register mr-2"></i> Cobrar <kbd>F8</kbd>
                                </button>
                                @if (plan_has('credits'))
                                <button type="button" class="btn btn-warning font-weight-bold" id="btn_credit_sale" disabled title="Venta a crédito" aria-keyshortcuts="F9">
                                    <i class="fas fa-clock mr-1"></i> Crédito <kbd>F9</kbd>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
        </div>
    </section>
</div>
@if(!$cashOpen)
    </div>
@endif

<!-- Modal Crédito Avanzado -->
<div class="modal fade" id="creditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-hand-holding-usd mr-2"></i>Configuración de Crédito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="text-muted small font-weight-bold text-uppercase">Total Venta Base</label>
                        <h4 id="credit_base_total" class="font-weight-bold text-dark">Gs. 0</h4>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small font-weight-bold text-uppercase">Tipo de Recargo</label>
                        <select id="credit_interest_type" class="form-control">
                            <option value="percent">Porcentaje (%)</option>
                            <option value="amount">Monto Fijo (Gs.)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small font-weight-bold text-uppercase">Valor Recargo</label>
                        <input type="text" id="credit_interest_value" class="form-control font-weight-bold currency-format" value="0">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small font-weight-bold text-uppercase">Frecuencia de Pago</label>
                        <select id="credit_frequency" class="form-control font-weight-bold">
                            <option value="mensual" selected>Mensual</option>
                            <option value="quincenal">Quincenal</option>
                            <option value="semanal">Semanal</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small font-weight-bold text-uppercase">Nro. de Cuotas</label>
                        <select id="credit_installments" class="form-control font-weight-bold">
                            @for($i=1; $i<=36; $i++)
                                <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }} Cuotas</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <label class="text-muted small font-weight-bold text-uppercase">Monto sugerido por cuota</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Gs.</span></div>
                            <input type="text" id="credit_installment_amount_input" class="form-control form-control-lg font-weight-bold text-primary currency-format" placeholder="0">
                        </div>
                        <small class="text-muted">Puedes ajustar este monto manualmente si lo deseas.</small>
                    </div>
                </div>

                <div class="bg-light p-3 rounded border">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Recargo:</span>
                        <span id="credit_interest_display" class="font-weight-bold text-danger">+ Gs. 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="h5 font-weight-bold">Total con Interés:</span>
                        <span id="credit_final_total" class="h5 font-weight-bold text-success">Gs. 0</span>
                    </div>
                    <hr class="my-2">
                    <div class="text-center">
                        <p class="mb-1 text-muted small text-uppercase font-weight-bold">Valor de cada cuota</p>
                        <h3 id="credit_installment_amount" class="font-weight-bold text-primary mb-0">Gs. 0</h3>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light justify-content-between">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_confirm_credit" class="btn btn-warning px-5 font-weight-bold">
                    <i class="fas fa-check mr-2"></i> CONFIRMAR CRÉDITO
                </button>
            </div>
        </div>
    </div>
</div>
<!-- MODAL: LISTA DE CLIENTES -->
<div class="modal fade" id="modalCustomerList" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-users mr-2"></i>Seleccionar Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <input type="text" id="search_customer_table" class="form-control w-50" placeholder="Filtrar por nombre o NIT...">
                    @can('create customer')
                    <button class="btn btn-success" id="btn_new_customer_modal"><i class="fas fa-plus mr-1"></i> Nuevo Cliente</button>
                    @endcan
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover m-0">
                        <thead class="bg-white">
                            <tr>
                                <th>NIT/RUC</th>
                                <th>Nombre / Razón Social</th>
                                <th>Teléfono</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="customer_list_tbody">
                            <!-- JS Load -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: NUEVO CLIENTE (RÁPIDO) -->
@can('create customer')
<div class="modal fade" id="modalNewCustomer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Nuevo Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="form_new_customer">
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIT / RUC *</label>
                        <input type="text" name="nit" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre Completo / Razón Social *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" name="address" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success font-weight-bold">Guardar y Seleccionar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- MODAL: EDITAR ÍTEM (AJUSTE DE PRECIO) -->
<div class="modal fade" id="modalEditItem" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Ajustar Producto</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h6 id="edit_item_name" class="font-weight-bold text-primary mb-3 text-center">Nombre Producto</h6>
                
                <div class="form-group">
                    <label class="small text-muted text-uppercase">Cantidad</label>
                    <input type="number" id="edit_item_qty" class="form-control font-weight-bold text-center" min="1">
                </div>

                <div class="row">
                    <div class="col-6 pr-1">
                        <div class="form-group mb-2">
                            <label class="small text-muted text-uppercase mb-0">Dto. (%)</label>
                            <input type="number" id="edit_item_discount_percent" class="form-control form-control-sm text-danger" value="0" min="0" max="100">
                        </div>
                    </div>
                    <div class="col-6 pl-1">
                        <div class="form-group mb-2">
                            <label class="small text-muted text-uppercase mb-0">Rec. (%)</label>
                            <input type="number" id="edit_item_interest_percent" class="form-control form-control-sm text-success" value="0" min="0">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 pr-1">
                        <div class="form-group">
                            <label class="small text-muted text-uppercase mb-0">Dto. (Gs)</label>
                            <input type="text" id="edit_item_discount" class="form-control text-danger font-weight-bold currency-format" value="0">
                        </div>
                    </div>
                    <div class="col-6 pl-1">
                        <div class="form-group">
                            <label class="small text-muted text-uppercase mb-0">Rec. (Gs)</label>
                            <input type="text" id="edit_item_interest" class="form-control text-success font-weight-bold currency-format" value="0">
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="edit_item_id">
                <input type="hidden" id="edit_item_price">
            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-primary btn-block font-weight-bold" id="btn_save_item_changes">Actualizar Ítem</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: SELECCIONAR CLIENTE (LISTA) -->
<div class="modal fade" id="modalSelectCustomer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-users mr-2"></i>Seleccionar Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" id="filter_customer_list" class="form-control" placeholder="Filtrar por nombre o NIT...">
                </div>
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-sm table-hover" id="table_customers_list">
                        <thead class="bg-light">
                            <tr>
                                <th>NIT/RUC</th>
                                <th>Nombre</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se carga vía AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: COBRAR (etapa 2) -->
<div class="modal fade" id="modalPayment" tabindex="-1" role="dialog" aria-labelledby="payment_modal_title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold" id="payment_modal_title">
                    <i class="fas fa-cash-register mr-2"></i> Cobrar venta
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Volver al carrito">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="checkout-hero">
                    <p class="checkout-total" id="pay_modal_total_display">Gs. 0</p>
                    <div class="checkout-breakdown">
                        <span>Subtotal <span id="txt_subtotal_display">Gs. 0</span></span>
                        <span class="is-discount" id="checkout_discount_row" hidden>Descuento <span id="txt_discount_display">- Gs. 0</span></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tipo_comprobante">Comprobante</label>
                            <select id="tipo_comprobante" class="form-control">
                                <option value="ticket">Ticket</option>
                                <option value="factura">Factura</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="txt_discount">Descuento %</label>
                            <div class="input-group">
                                <input type="number" id="txt_discount" class="form-control text-center font-weight-bold" value="0" min="0" max="100" step="1">
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label>Método de pago</label>
                    <div class="pay-methods">
                        <button type="button" class="btn btn-sm pay-method-btn active" data-method="efectivo">
                            <i class="fas fa-money-bill-wave d-block mb-1"></i>Efectivo
                        </button>
                        <button type="button" class="btn btn-sm pay-method-btn" data-method="qr">
                            <i class="fas fa-qrcode d-block mb-1"></i>QR
                        </button>
                        <button type="button" class="btn btn-sm pay-method-btn" data-method="tarjeta">
                            <i class="fas fa-credit-card d-block mb-1"></i>Tarjeta
                        </button>
                        <button type="button" class="btn btn-sm pay-method-btn" data-method="transferencia">
                            <i class="fas fa-university d-block mb-1"></i>Transf.
                        </button>
                    </div>
                </div>

                <div id="section_pay_cash">
                    <div class="form-group mb-2">
                        <label for="pay_modal_received">Monto recibido (Gs.)</label>
                        <input type="text" id="pay_modal_received" class="form-control font-weight-bold text-primary checkout-cash-received currency-format" placeholder="0" autocomplete="off">
                    </div>
                    <div class="checkout-change-box mb-3">
                        <p class="text-muted small font-weight-bold mb-1">Vuelto</p>
                        <p class="checkout-change" id="pay_modal_change">Gs. 0</p>
                    </div>
                </div>

                <div id="section_pay_electronic" style="display:none;">
                    <div class="form-group">
                        <label for="pay_modal_reference" id="lbl_reference_title">Nro. de referencia / ID operación</label>
                        <input type="text" id="pay_modal_reference" class="form-control form-control-lg font-weight-bold text-center" placeholder="Número o ID de transacción" autocomplete="off">
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="pay_modal_note">Nota (opcional)</label>
                    <textarea id="pay_modal_note" class="form-control" rows="2" placeholder="Observación sobre este pago"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light p-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">Volver al carrito</button>
                <button type="button" id="btn_confirm_final_payment" class="btn btn-success btn-lg px-4 font-weight-bold shadow-sm" aria-keyshortcuts="F8 Enter">
                    <i class="fas fa-check-circle mr-2"></i> Registrar venta <kbd class="checkout-confirm-kbd">Enter</kbd>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: VENTA EXITOSA -->
<div class="modal fade" id="modalSaleSuccess" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center overflow-hidden">
            <div class="modal-body p-4 pt-5">
                <div class="sale-success-icon text-success mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4 class="font-weight-bold mb-2" id="sale_success_title">Venta registrada</h4>
                <p class="text-muted mb-0" id="sale_success_message">La venta se procesó correctamente.</p>
                <div id="sale_success_change_box" class="checkout-change-box mt-3 mb-0" style="display:none;">
                    <p class="text-muted small font-weight-bold mb-1">Vuelto</p>
                    <p class="sale-success-change" id="sale_success_change">Gs. 0</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
                <button type="button" class="btn btn-success btn-lg px-5 font-weight-bold shadow-sm" id="btn_sale_success_continue">
                    <i class="fas fa-plus-circle mr-2"></i> Nueva venta
                </button>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.pos-shortcuts-modal', ['context' => 'sale'])

@endsection

@push('script')
<script>
function escapeHtml(str) {
    return $('<div>').text(str == null ? '' : String(str)).html().replace(/"/g, '&quot;');
}

// Validaciones y Lógica AJAX heredada de functions.js del sistema Venta original
$(document).ready(function() {
    let isProcessingSale = false;

    initProductGridFilter({ accentClass: 'btn-primary' });

    $('.modal').modal('hide');

    loadCart();

    function searchAndAddProduct(term) {
        term = (term || '').trim();
        if (!term) return;

        $.post("{{ route('sales.ajax.search_product') }}", {
            _token: "{{ csrf_token() }}",
            term: term
        }, function(product) {
            addToCartFast(product.id, product.stock, product.description);
            $('#filter_search').val('');
            filterProducts($('.filter-btn.active').attr('data-filter') || 'all', '');
        }).fail(function() {
            alert('Producto no encontrado.');
        });
    }

    $('#filter_search').on('keydown', function(e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        let term = $(this).val().trim();
        if (term) {
            searchAndAddProduct(term);
            return;
        }
        if (!$('#btn_process_sale').prop('disabled')) {
            $('#btn_process_sale').trigger('click');
        }
    });

    $('#txt_discount, #pay_modal_received, #pay_modal_reference').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    $('#customer_search').on('input', function() {
        $('#customer_not_found_alert').hide();
        const term = $(this).val().trim();

        if (!term) {
            resetCustomerToDefault();
            return;
        }

        if ($('#customer_info_panel').is(':visible')) {
            const currentNit = $('#lbl_customer_nit').text().trim();
            if (term !== currentNit) {
                resetCustomerToDefault();
            }
        }
    });

    $('#customer_search').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchCustomer();
            return;
        }

        if (e.key === 'Tab' && !e.shiftKey) {
            const term = $(this).val().trim();
            if (!term) return;

            e.preventDefault();
            searchCustomer();
        }
    });

    function syncCustomerFeedbackSlot() {
        const hasFeedback = $('#customer_not_found_alert').is(':visible') || $('#customer_info_panel').is(':visible');
        $('.customer-feedback-slot').toggleClass('has-feedback', hasFeedback);
    }

    function hideCustomerNotFound() {
        $('#customer_not_found_alert').hide();
        syncCustomerFeedbackSlot();
    }

    function showCustomerNotFound() {
        $('#customer_info_panel').hide();
        $('#customer_not_found_alert').show();
        syncCustomerFeedbackSlot();
    }

    function setSelectedCustomer(customer) {
        $('#customer_id').val(customer.id);
        $('#lbl_customer_name').text(customer.name);
        $('#lbl_customer_nit').text(customer.nit);
        $('#customer_search').val(customer.nit);
        hideCustomerNotFound();
        $('#customer_info_panel').show();
        syncCustomerFeedbackSlot();
        $('#customer_search').blur();
        $('#filter_search').trigger('focus');
    }

    function resetCustomerToDefault() {
        $('#customer_id').val(1);
        $('#lbl_customer_name').text('Público General');
        $('#lbl_customer_nit').text('0000000');
        $('#customer_info_panel').hide();
        syncCustomerFeedbackSlot();
    }

    function clearSelectedCustomer() {
        resetCustomerToDefault();
        hideCustomerNotFound();
        $('#customer_search').val('').trigger('focus');
    }

    function getCustomerIdForSale() {
        if ($('#customer_info_panel').is(':visible')) {
            return $('#customer_id').val() || 1;
        }
        return 1;
    }

    function finishSaleAndReset() {
        clearSelectedCustomer();
        location.reload();
    }

    function showSaleSuccess(options) {
        options = options || {};
        $('#sale_success_title').text(options.title || 'Venta registrada');
        $('#sale_success_message').text(options.message || 'La venta se procesó correctamente.');

        if (options.change > 0) {
            $('#sale_success_change').text('Gs. ' + Math.round(options.change).toLocaleString('de-DE'));
            $('#sale_success_change_box').show();
        } else {
            $('#sale_success_change_box').hide();
        }

        $('#modalSaleSuccess').modal('show');
    }

    $('#btn_clear_customer').click(function() {
        clearSelectedCustomer();
    });

    $('#btn_sale_success_continue').click(function() {
        finishSaleAndReset();
    });

    function searchCustomer() {
        let term = $('#customer_search').val().trim();
        if (!term) {
            hideCustomerNotFound();
            return $.Deferred().reject().promise();
        }

        return $.post("{{ route('sales.ajax.search_customer') }}", {
            _token: "{{ csrf_token() }}",
            term: term
        }).done(function(res) {
            setSelectedCustomer(res);
        }).fail(function() {
            resetCustomerToDefault();
            showCustomerNotFound();
        });
    }

    function openNewCustomerModal(prefillNit) {
        if (!$('#modalNewCustomer').length) return;

        $('#form_new_customer')[0].reset();
        hideCustomerNotFound();

        if (prefillNit) {
            $('#form_new_customer input[name="nit"]').val(prefillNit);
        }

        $('#modalNewCustomer').modal('show');
    }

    $('#btn_search_customer').click(function() {
        searchCustomer();
    });

    $(document).on('click', '#btn_open_new_customer, #btn_add_customer_from_search', function() {
        openNewCustomerModal($('#customer_search').val().trim());
    });

    if ($('#modalNewCustomer').length) {
        $('#modalNewCustomer').on('shown.bs.modal', function() {
            const $nit = $('#form_new_customer input[name="nit"]');
            const $name = $('#form_new_customer input[name="name"]');
            if ($nit.val().trim()) {
                $name.trigger('focus');
            } else {
                $nit.trigger('focus');
            }
        });
    }

    $(document).on('keydown', function(e) {
        if ($('#modalSaleSuccess').hasClass('show') && e.key === 'Escape') {
            e.preventDefault();
            finishSaleAndReset();
            return;
        }

        if (e.key !== 'Enter' || e.shiftKey || e.ctrlKey || e.altKey || e.repeat) return;

        const $target = $(e.target);

        if ($target.is('textarea') || $target.closest('#form_new_customer').length) {
            return;
        }

        if ($('#modalSaleSuccess').hasClass('show')) {
            e.preventDefault();
            finishSaleAndReset();
            return;
        }

        if ($('#modalPayment').hasClass('show')) {
            e.preventDefault();
            $('#btn_confirm_final_payment').trigger('click');
            return;
        }

        if ($target.closest('#creditModal, #modalEditItem, #modalSelectCustomer, #modalNewCustomer, #modalCustomerList').length) {
            return;
        }

        if ($target.is('#filter_search, #customer_search, #filter_customer_list, #search_customer_table')) {
            return;
        }

        if (!$('#btn_process_sale').prop('disabled') && !$('.modal.show').length) {
            e.preventDefault();
            $('#btn_process_sale').trigger('click');
        }
    });

    // Customer Search (Misma lógica validada)

    // 4. Métodos de Pago — Toggle Visual


    // Estilos base
    const methodColors = {
        'efectivo':     'btn-success',
        'qr':           'btn-info',
        'tarjeta':      'btn-primary',
        'transferencia':'btn-warning'
    };
    
    // Inicializar primer botón activo
    updatePaymentButtons('efectivo');

    $('.pay-method-btn').click(function() {
        let method = $(this).attr('data-method');
        $('#selected_payment_method').val(method);
        updatePaymentButtons(method);
        syncPaymentSections(method);
        if ($('#modalPayment').hasClass('show')) {
            focusCheckoutField(method);
        }
    });

    function updatePaymentButtons(activeMethod) {
        $('.pay-method-btn').each(function() {
            let m = $(this).attr('data-method');
            let color = methodColors[m] || 'btn-secondary';
            $(this).removeClass('btn-success btn-info btn-primary btn-warning btn-secondary active')
                   .addClass('btn-outline-secondary');
            if(m === activeMethod) {
                $(this).removeClass('btn-outline-secondary').addClass(color + ' active');
            }
        });
    }

    function syncPaymentSections(method) {
        if(method === 'efectivo') {
            $('#section_pay_cash').show();
            $('#section_pay_electronic').hide();
        } else {
            $('#section_pay_cash').hide();
            $('#section_pay_electronic').show();
            $('#lbl_reference_title').text('Nro. de referencia / ID operación');
        }
    }

    function focusCheckoutField(method) {
        if(method === 'efectivo') {
            $('#pay_modal_received').trigger('focus').trigger('select');
        } else {
            $('#pay_modal_reference').trigger('focus');
        }
    }

    // Calcular vuelto en tiempo real
    $('#pay_modal_received').on('keyup input', function() {
        let paga = window.getCleanNumber($(this).val());
        let total = parseFloat($('#txt_total_sale').val()) || 0;
        let vuelto = paga - total;

        let display = vuelto >= 0
            ? 'Gs. ' + Math.round(vuelto).toLocaleString('de-DE')
            : '-Gs. ' + Math.round(Math.abs(vuelto)).toLocaleString('de-DE');

        $('#pay_modal_change').text(display).css('color', vuelto >= 0 ? '#059669' : '#dc2626');
    });

    // Descuento en tiempo real
    $('#txt_discount').on('keyup input change', function() {
        recalcTotals();
    });

    // Remover item del carrito
    $(document).on('click', '.btn_remove_item', function() {
        let id = $(this).attr('data-id');
        $.post("{{ route('sales.ajax.remove_from_cart') }}", {
            _token: "{{ csrf_token() }}",
            id: id
        }, function(res) {
            updateCartTable(res);
        });
    });

    // Venta a Crédito    // Abrir Modal de Crédito
    $('#btn_credit_sale').click(function() {
        let total = parseFloat($('#txt_total_sale').val()) || 0;
        if(total <= 0) return;

        $('#credit_base_total').text('Gs. ' + total.toLocaleString('de-DE'));
        $('#credit_installment_amount_input').val(0); // Reset
        recalcCredit();
        $('#creditModal').modal('show');
    });

    // Recalcular Crédito cuando cambian cuotas o interés (resetear monto manual)
    $('#credit_interest_type, #credit_interest_value, #credit_installments, #credit_frequency').on('change keyup input', function() {
        $('#credit_installment_amount_input').val(0); // reset to auto-calculate
        recalcCredit();
    });

    // Si el usuario escribe manualmente el monto por cuota
    $('#credit_installment_amount_input').on('keyup input', function() {
        recalcCredit();
    });

    function recalcCredit() {
        let base = parseFloat($('#txt_total_sale').val()) || 0;
        let type = $('#credit_interest_type').val();
        let val = window.getCleanNumber($('#credit_interest_value').val());
        let inst = parseInt($('#credit_installments').val()) || 1;
        let manualAmount = window.getCleanNumber($('#credit_installment_amount_input').val());

        let interest = 0;
        if(type === 'percent') {
            interest = (base * val) / 100;
        } else {
            interest = val;
        }

        let total = base + interest;
        let amount = manualAmount > 0 ? manualAmount : Math.round(total / inst);

        $('#credit_interest_display').text('+ Gs. ' + Math.round(interest).toLocaleString('de-DE'));
        $('#credit_final_total').text('Gs. ' + Math.round(total).toLocaleString('de-DE'));
        
        // Always update the input if it has 0 (auto mode)
        if (manualAmount === 0) {
            $('#credit_installment_amount_input').val(amount).trigger('input');
        }
        $('#credit_installment_amount').text('Gs. ' + amount.toLocaleString('de-DE'));
    }

    // Confirmar Crédito FINAL
    $(document).on('click', '#btn_confirm_credit', function(e) {
        e.preventDefault();

        if (isProcessingSale || !$('#creditModal').hasClass('show')) return;
        
        let base = parseFloat($('#txt_total_sale').val()) || 0;
        let type = $('#credit_interest_type').val();
        let val = window.getCleanNumber($('#credit_interest_value').val());
        let inst = parseInt($('#credit_installments').val()) || 1;
        let amount = window.getCleanNumber($('#credit_installment_amount_input').val());
        let frequency = $('#credit_frequency').val();

        if(!base || base <= 0) {
            alert('El carrito está vacío o el total es inválido.');
            return;
        }
        
        let $btn = $(this);
        let originalHtml = $btn.html();
        isProcessingSale = true;
        $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...').prop('disabled', true);

        $.post("{{ route('sales.ajax.process_sale') }}", {
            _token: "{{ csrf_token() }}",
            customer_id: getCustomerIdForSale(),
            payment_type: 'credito',
            discount: parseFloat($('#txt_discount').val()) || 0,
            interest_type: type,
            interest_value: val,
            installments: inst,
            installment_amount: window.getCleanNumber($('#credit_installment_amount_input').val()),
            frequency: frequency,
            voucher_type: $('#tipo_comprobante').val()
        }, function(res) {
            if(res.success) {
                let printUrl = "{{ route('sales.print_ticket', ':id') }}".replace(':id', res.sale_id);
                window.open(printUrl, '_blank');
                $('#creditModal').modal('hide');
                showSaleSuccess({
                    title: 'Crédito registrado',
                    message: 'La venta a crédito se guardó correctamente.'
                });
            }
        }).fail(function(r) {
            alert(r.responseJSON ? r.responseJSON.error : 'Error al procesar el crédito');
            isProcessingSale = false;
            $btn.html(originalHtml).prop('disabled', false);
        });
    });

    $('#btn_process_sale').on('click', function(e) {
        e.preventDefault();
        let payMethod = $('#selected_payment_method').val() || 'efectivo';
        let total = parseFloat($('#txt_total_sale').val()) || 0;

        if(total <= 0) return;

        recalcTotals();
        $('#pay_modal_reference').val('');
        $('#pay_modal_note').val('');
        $('#pay_modal_received').val('');
        $('#pay_modal_change').text('Gs. 0').css('color', '#059669');
        updatePaymentButtons(payMethod);
        syncPaymentSections(payMethod);
        $('#modalPayment').modal('show');
    });

    $('#modalPayment').on('shown.bs.modal', function() {
        focusCheckoutField($('#selected_payment_method').val() || 'efectivo');
    });

    $('#modalPayment').on('hidden.bs.modal', function() {
        $('#filter_search').trigger('focus');
    });

    // Procesar Cobro FINAL desde el MODAL
    $('#btn_confirm_final_payment').on('click', function(e) {
        e.preventDefault();

        if (isProcessingSale || !$('#modalPayment').hasClass('show')) return;
        let payMethod = $('#selected_payment_method').val();
        let paymentWith = window.getCleanNumber($('#pay_modal_received').val());
        let total = parseFloat($('#txt_total_sale').val()) || 0;
        let reference = $('#pay_modal_reference').val();
        let note = $('#pay_modal_note').val();

        if(payMethod === 'efectivo' && paymentWith < total) {
            alert('El monto recibido es menor al total de la venta.');
            return;
        }

        if(payMethod !== 'efectivo' && reference.trim() === '') {
            if(!confirm('¿Desea procesar sin número de referencia?')) {
                return; // User cancelled
            }
        } // If user confirmed or reference is provided, it proceeds naturally.

        let $btn = $(this);
        isProcessingSale = true;
        $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...').prop('disabled', true);

        $.post("{{ route('sales.ajax.process_sale') }}", {
            _token: "{{ csrf_token() }}",
            customer_id: getCustomerIdForSale(),
            payment_type: payMethod,
            payment_with: paymentWith,
            reference_number: reference,
            payment_note: note,
            discount: parseFloat($('#txt_discount').val()) || 0,
            voucher_type: $('#tipo_comprobante').val()
        }, function(res) {
            if(res.success) {
                $('#modalPayment').modal('hide');
                let printUrl = "{{ route('sales.print_ticket', ':id') }}".replace(':id', res.sale_id);
                window.open(printUrl, '_blank');
                showSaleSuccess({
                    change: payMethod === 'efectivo' ? (res.change || 0) : 0
                });
            }
        }).fail(function(r) {
            alert(r.responseJSON ? r.responseJSON.error : 'Error al procesar la venta');
            isProcessingSale = false;
            $btn.html('<i class="fas fa-check-circle mr-2"></i> Registrar venta <kbd class="checkout-confirm-kbd">Enter</kbd>').prop('disabled', false);
        });
    });

    // Limpiar carrito (Vaciar y devolver stock)
    $('#btn_clear_cart').on('click', function(e) {
        e.preventDefault();
        if(confirm('¿Está seguro de vaciar el carrito? El stock de los productos será devuelto al inventario.')) {
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $.post("{{ route('sales.ajax.clear_cart') }}", {
                _token: "{{ csrf_token() }}"
            }, function(res) {
                if(res.success) {
                    loadCart();
                    clearSelectedCustomer();
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                    $('#filter_search').trigger('focus');
                }
            });
        }
    });

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            $('.modal').modal('hide');
            isProcessingSale = false;
            clearSelectedCustomer();
            loadCart();
        }
    });

    // ══ EVENTOS DE CLIENTES ══

    // Abrir Modal de Lista de Clientes
    $('#btn_list_customers').click(function() {
        $('#modalSelectCustomer').modal('show');
        loadCustomerListTable();
    });

    // Guardar nuevo cliente desde modal rápido
    $('#form_new_customer').submit(function(e) {
        e.preventDefault();
        let $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');
        
        $.post("{{ route('sales.ajax.store_customer') }}", $(this).serialize() + "&_token={{ csrf_token() }}", function(res) {
            setSelectedCustomer(res);
            
            $('#modalNewCustomer').modal('hide');
            $('#form_new_customer')[0].reset();
            $btn.prop('disabled', false).text('Guardar y Seleccionar');
        }).fail(function(err) {
            alert(err.responseJSON.message || 'Error al guardar cliente. Verifique si el NIT ya existe.');
            $btn.prop('disabled', false).text('Guardar y Seleccionar');
        });
    });

    // Selector Cliente en Lista
    $(document).on('click', '.btn-select-customer', function() {
        setSelectedCustomer({
            id: $(this).data('id'),
            name: $(this).data('name'),
            nit: $(this).data('nit')
        });
        $('#modalSelectCustomer').modal('hide');
    });

    // Función cargar clientes
    function loadCustomerListTable(term = '') {
        let tbody = $('#table_customers_list tbody');
        tbody.html('<tr><td colspan="3" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></td></tr>');
        
        $.get("{{ route('sales.ajax.list_customers') }}", { term: term }, function(res) {
            let html = '';
            if(res.length > 0) {
                res.forEach(c => {
                    html += `<tr>
                        <td class="align-middle">${escapeHtml(c.nit || '-')}</td>
                        <td class="align-middle font-weight-bold text-dark">${escapeHtml(c.name)}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm btn-success btn-select-customer rounded-pill px-3 shadow-sm" data-id="${c.id}" data-name="${escapeHtml(c.name)}" data-nit="${escapeHtml(c.nit)}">
                                <i class="fas fa-check mr-1"></i> Seleccionar
                            </button>
                        </td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="3" class="text-center text-muted py-4">No se encontraron clientes.</td></tr>';
            }
            tbody.html(html);
        });
    }

    $('#filter_customer_list').on('keyup', function() {
        loadCustomerListTable($(this).val());
    });


    // ══ EVENTOS DE ÍTEMS ══

    // Abrir modal de edición de ítem
    $(document).on('click', '.btn_edit_item', function() {
        $('#edit_item_id').val($(this).data('id'));
        $('#edit_item_name').text($(this).data('name'));
        $('#edit_item_qty').val($(this).data('qty'));
        $('#edit_item_discount').val($(this).data('discount'));
        $('#edit_item_interest').val($(this).data('interest'));
        
        let price = parseFloat($(this).closest('tr').find('.text-primary').text().replace(/[^\d]/g, '')) / $(this).data('qty');
        $('#edit_item_price').val(price);

        $('#edit_item_discount_percent').val(0);
        $('#edit_item_interest_percent').val(0);
        $('#modalEditItem').modal('show');
    });

    // Calc Dto Gs from %
    $('#edit_item_discount_percent').on('input', function() {
        let p = parseFloat($(this).val()) || 0;
        let price = parseFloat($('#edit_item_price').val()) || 0;
        let qty = parseFloat($('#edit_item_qty').val()) || 1;
        $('#edit_item_discount').val(Math.round((price * qty) * p / 100));
    });

    // Calc Rec Gs from %
    $('#edit_item_interest_percent').on('input', function() {
        let p = parseFloat($(this).val()) || 0;
        let price = parseFloat($('#edit_item_price').val()) || 0;
        let qty = parseFloat($('#edit_item_qty').val()) || 1;
        $('#edit_item_interest').val(Math.round((price * qty) * p / 100));
    });

    // Guardar cambios en el ítem
    $('#btn_save_item_changes').click(function() {
        let id = $('#edit_item_id').val();
        let qty = $('#edit_item_qty').val();
        let discount = window.getCleanNumber($('#edit_item_discount').val());
        let interest = window.getCleanNumber($('#edit_item_interest').val());
        
        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Actualizando...');

        $.post("{{ route('sales.ajax.update_cart_item') }}", {
            _token: "{{ csrf_token() }}",
            id: id,
            quantity: qty,
            discount: discount,
            interest: interest
        }, function(res) {
            if(res.error) {
                alert(res.error);
            } else {
                updateCartTable(res);
                $('#modalEditItem').modal('hide');
            }
            $btn.prop('disabled', false).text('Actualizar Ítem');
        }).fail(function(r) {
            alert(r.responseJSON ? r.responseJSON.error : 'Error al actualizar');
            $btn.prop('disabled', false).text('Actualizar Ítem');
        });
    });

    function toggleShortcutHelp() {
        $('#modalPosShortcuts').modal('toggle');
    }

    function focusProductSearch() {
        $('#filter_search').trigger('focus').trigger('select');
    }

    initPosShortcuts({
        allowWhenTyping: ['F2', 'F4', 'F8', 'F9', 'Shift+F4', 'Alt+Shift+H'],
        actions: {
            F2: focusProductSearch,
            '/': focusProductSearch,
            F4: function () {
                $('#customer_search').trigger('focus').trigger('select');
            },
            'Shift+F4': function () {
                $('#btn_list_customers').trigger('click');
            },
            F8: function () {
                if ($('#modalSaleSuccess').hasClass('show')) {
                    finishSaleAndReset();
                    return;
                }
                if ($('#modalPayment').hasClass('show')) {
                    $('#btn_confirm_final_payment').trigger('click');
                    return;
                }
                if ($('#creditModal').hasClass('show')) {
                    $('#btn_confirm_credit').trigger('click');
                    return;
                }
                if ($('.modal.show').length) {
                    return;
                }
                if (!$('#btn_process_sale').prop('disabled')) {
                    $('#btn_process_sale').trigger('click');
                }
            },
            F9: function () {
                if ($('.modal.show').length) {
                    return;
                }
                if (!$('#btn_credit_sale').prop('disabled')) {
                    $('#btn_credit_sale').trigger('click');
                }
            },
            '?': toggleShortcutHelp,
            'Alt+Shift+H': toggleShortcutHelp
        }
    });
});

// FUNCTIONS OUTSIDE READY
function addToCartFast(id, currentStock, name) {
    $.post("{{ route('sales.ajax.add_to_cart') }}", {
        _token: "{{ csrf_token() }}",
        product_id: id,
        quantity: 1
    }, function(res) {
        if(res.error) {
            alert('⚠️ ' + res.error);
        } else {
            updateCartTable(res);
        }
    }).fail(function(r) {
        alert('⚠️ ' + (r.responseJSON ? r.responseJSON.error : 'Error de stock o conexión'));
    });
}

function loadCart() {
    $.get("{{ route('sales.ajax.get_cart') }}", function(res) {
        updateCartTable(res);
    });
}

function recalcTotals() {
    let subtotal = parseFloat($('#txt_raw_subtotal').val()) || 0;
    let discount = parseFloat($('#txt_discount').val()) || 0;
    if(discount < 0) discount = 0;
    if(discount > 100) discount = 100;
    let discountAmount = Math.round(subtotal * discount / 100);
    let total = subtotal - discountAmount;
    let totalLabel = 'Gs. ' + Math.round(total).toLocaleString('de-DE');

    $('#txt_subtotal_display').text('Gs. ' + Math.round(subtotal).toLocaleString('de-DE'));
    $('#txt_discount_display').text('- Gs. ' + discountAmount.toLocaleString('de-DE'));
    $('#checkout_discount_row').prop('hidden', discountAmount <= 0);
    $('#txt_total_sale').val(total);
    $('#txt_total_sale_display').text(totalLabel);
    $('#pay_modal_total_display').text(totalLabel);
    $('#credit_base_total').text(totalLabel);
    $('#pay_modal_received').trigger('input');
}

function updateCartTable(res) {
    let html = '';
    let adjustedSubtotal = 0;

    res.details.forEach(function(d) {
        let baseTotal = (d.quantity * d.price);
        let discount = parseFloat(d.discount) || 0;
        let interest = parseFloat(d.interest_amount) || 0;
        let finalTotal = baseTotal - discount + interest;
        adjustedSubtotal += finalTotal;

        const productName = escapeHtml(d.product ? d.product.description : '');
        html += `<tr>
            <td class="align-middle">
                <span class="cart-product-name" title="${productName}">${productName}</span>
                <div class="cart-product-meta">
                    <small class="text-primary">Gs. ${Math.round(d.price).toLocaleString('de-DE')}</small>
                    ${discount > 0 ? `<small class="text-danger" title="Descuento">-${Math.round(discount).toLocaleString('de-DE')}</small>` : ''}
                    ${interest > 0 ? `<small class="text-success" title="Recargo">+${Math.round(interest).toLocaleString('de-DE')}</small>` : ''}
                </div>
            </td>
            <td class="align-middle text-center">
                <span class="badge badge-light border cart-qty font-weight-bold">${d.quantity}</span>
            </td>
            <td class="align-middle text-right font-weight-bold text-success cart-line-total">Gs. ${Math.round(finalTotal).toLocaleString('de-DE')}</td>
            <td class="align-middle text-center">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-info btn_edit_item"
                            data-id="${d.id}" data-name="${productName}"
                            data-qty="${d.quantity}" data-discount="${discount}" data-interest="${interest}"
                            title="Ajustar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn_remove_item" data-id="${d.id}" title="Quitar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    });
    
    if(res.details.length === 0) {
        html = '<tr><td colspan="4" class="text-center text-muted py-5"><i class="fas fa-shopping-basket fa-3x mb-2 opacity-50"></i><br>Agregue productos al carrito</td></tr>';
    }

    $('#cart_tbody').html(html);
    $('#item_count').text(res.details.length);
    $('#txt_raw_subtotal').val(Math.round(adjustedSubtotal));
    recalcTotals();
    let hasItems = res.details.length > 0;
    $('#btn_process_sale, #btn_credit_sale').prop('disabled', !hasItems);
}

function loadCustomerListTable() {
    $('#table_customers_list tbody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando...</td></tr>');
    $.get("{{ route('sales.ajax.list_customers') }}", function(data) {
        let html = '';
        data.forEach(c => {
            html += `<tr>
                <td class="align-middle font-weight-bold">${escapeHtml(c.nit)}</td>
                <td class="align-middle">${escapeHtml(c.name)}</td>
                <td class="text-right">
                    <button class="btn btn-sm btn-primary btn_select_customer_from_list" 
                        data-id="${c.id}" data-nit="${escapeHtml(c.nit)}" data-name="${escapeHtml(c.name)}">
                        <i class="fas fa-check mr-1"></i> Seleccionar
                    </button>
                </td>
            </tr>`;
        });
        $('#table_customers_list tbody').html(html);
    });
}
</script>
@endpush

