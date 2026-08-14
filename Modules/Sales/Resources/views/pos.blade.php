@extends('admin.layouts.master')

@section('title', 'Punto de Venta')

@section('content')
<style>
    .pos-product-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 10px;
        overflow: hidden;
    }
    .pos-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border-color: var(--primary) !important;
    }
    .product-img-placeholder {
        height: 100px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #fff;
    }
    .category-filter {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 10px;
    }
    .category-filter::-webkit-scrollbar {
        height: 6px;
    }
    .category-filter::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 10px;
    }
    .split-layout {
        height: calc(100vh - 130px);
        display: flex;
        gap: 20px;
    }
    .left-panel {
        flex: 6;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .right-panel {
        flex: 4;
        display: flex;
        flex-direction: column;
    }
    .grid-container {
        overflow-y: auto;
        padding-right: 5px;
        flex: 1;
    }
    .cart-container {
        overflow-y: auto;
        flex: 1;
    }
    .cart-table {
        table-layout: fixed;
    }
    .cart-product-name {
        display: block;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .cart-product-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        align-items: center;
    }
    .pay-methods {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 5px;
    }
    .pay-method-btn {
        min-height: 60px;
        line-height: 1.15;
        white-space: normal;
    }
    .payment-actions .btn {
        height: 100%;
    }
    .sale-total-row {
        gap: .5rem;
    }
    .sale-total-label {
        flex: 1 1 auto;
    }
    .sale-total-amount {
        flex: 0 1 auto;
        text-align: right;
        word-break: break-word;
    }
    .cash-input-group .input-group-text {
        white-space: nowrap;
    }
    @media (max-width: 1399.98px) {
        .split-layout {
            gap: 14px;
        }
        .left-panel {
            flex: 5.5;
        }
        .right-panel {
            flex: 4.5;
        }
    }
    @media (max-width: 1199.98px) {
        .split-layout {
            height: auto;
            flex-direction: column;
        }
        .left-panel,
        .right-panel {
            flex: 1 1 auto;
        }
        .grid-container,
        .cart-container {
            overflow: visible;
        }
    }
    @media (max-width: 767.98px) {
        .pay-methods {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .payment-actions {
            row-gap: .5rem;
        }
        .sale-total-row {
            align-items: flex-start !important;
            flex-direction: column;
        }
        .sale-total-amount {
            width: 100%;
            text-align: left;
        }
        .cash-input-group {
            flex-wrap: wrap;
        }
        .cash-input-group .input-group-prepend,
        .cash-input-group .input-group-append,
        .cash-input-group .form-control {
            width: 100%;
        }
        .cash-input-group .input-group-text {
            width: 100%;
            justify-content: center;
        }
        .cart-table thead th {
            font-size: .78rem;
            padding: .6rem .4rem;
        }
        .cart-table td {
            padding: .65rem .4rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="content-header pb-1">
        <div class="container-fluid">
            <h1 class="m-0 text-premium"><i class="fas fa-th mr-2"></i>Punto de Venta Interactivo</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(!$cashOpen)
                <div class="alert alert-warning shadow-sm border-0 p-4 text-center mt-3">
                    <i class="fas fa-exclamation-triangle fa-4x mb-3 text-warning"></i>
                    <h2 class="font-weight-bold">CAJA CERRADA</h2>
                    <p class="lead">Debe abrir una caja antes de poder realizar ventas en el sistema.</p>
                    <a href="{{ route('financials.cajas.index') }}" class="btn btn-warning btn-lg px-5 font-weight-bold">
                        <i class="fas fa-cash-register mr-2"></i> IR A CAJA
                    </a>
                </div>
                <div style="opacity: 0.3; pointer-events: none;">
            @endif
            <div class="split-layout">
                
                <!-- LEFT PANEL: GRID -->
                <div class="left-panel">
                    <!-- Buscador y Categorías -->
                    <div class="card shadow-sm border-0 mb-3 flex-shrink-0">
                        <div class="card-body p-3">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="filter_search" class="form-control border-left-0 form-control-lg" placeholder="Buscar por código, código de barras o descripción..." autofocus>
                            </div>
                            
                            <div class="category-filter" id="category_filters">
                                <button class="btn btn-primary rounded-pill px-4 mr-2 filter-btn active" data-filter="all">Todas</button>
                                @foreach($categories as $cat)
                                    <button class="btn btn-outline-secondary text-dark rounded-pill px-4 mr-2 filter-btn" data-filter="{{ $cat->id }}">{{ $cat->name }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Cuadrilla de Productos -->
                    <div class="grid-container">
                        <div class="row" id="products_grid">
                            @foreach($products as $prod)
                                <div class="col-xl-3 col-lg-4 col-md-4 col-6 mb-3 product-item" data-category="{{ $prod->category_id }}" data-search="{{ strtolower($prod->code.' '.$prod->description.' '.$prod->brand.' '.$prod->model_name) }}">
                                    <div class="card h-100 shadow-sm border pos-product-card" onclick="addToCartFast({{ $prod->id }}, {{ $prod->stock }}, '{{ addslashes($prod->description) }}')">
                                        <div class="product-img-placeholder">
                                            @if($prod->image)
                                                <img src="{{ asset('storage/' . $prod->image) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <i class="fas fa-box-open text-primary opacity-50"></i>
                                            @endif
                                        </div>
                                        <div class="card-body p-2 d-flex flex-column">
                                            <span class="badge badge-info align-self-start mb-1">{{ $prod->code }}</span>
                                            <h6 class="font-weight-bold mb-1 text-truncate" title="{{ $prod->description }}">{{ $prod->description }}</h6>
                                            <small class="text-muted text-truncate d-block mb-2">{{ $prod->brand }} {{ $prod->model_name }}</small>
                                            
                                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                                <h5 class="text-success font-weight-bold mb-0">Gs. {{ number_format($prod->price, 0, ',', '.') }}</h5>
                                                <span class="badge {{ $prod->stock > 0 ? 'badge-primary' : 'badge-danger' }}">{{ $prod->stock }} en stock</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            <div id="no_results_msg" class="col-12 text-center text-muted py-5" style="display:none;">
                                <i class="fas fa-search fa-3x mb-3 text-lighter"></i>
                                <h5>No se encontraron productos</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT PANEL: CART -->
                <div class="right-panel">
                    <div class="card shadow-lg border-0 h-100 d-flex flex-column">
                        <div class="card-header bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                            <h4 class="card-title font-weight-bold mb-0"><i class="fas fa-shopping-cart mr-2"></i>Carrito</h4>
                            <div>
                                <button class="btn btn-xs btn-danger mr-1" id="btn_clear_cart" title="Vaciar Carrito">
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
                                <input type="text" id="customer_search" class="form-control border-left-0" placeholder="NIT o Nombre del cliente...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="button" id="btn_search_customer"><i class="fas fa-search"></i></button>
                                    <button class="btn btn-outline-info" type="button" id="btn_list_customers" title="Seleccionar de la lista"><i class="fas fa-list"></i></button>
                                    @can('create customer')
                                    <button class="btn btn-success" type="button" data-toggle="modal" data-target="#modalNewCustomer"><i class="fas fa-plus"></i></button>
                                    @endcan
                                </div>
                            </div>
                            <div class="mt-2 text-sm d-flex justify-content-between" id="customer_info_panel" style="display:none;">
                                <input type="hidden" id="customer_id" value="1">
                                <strong><span id="lbl_customer_name" class="text-primary">Público General</span></strong>
                                <span id="lbl_customer_nit" class="text-muted">0000000</span>
                            </div>
                        </div>

                        <!-- Carrito Tabla -->
                        <div class="cart-container p-0">
                            <table class="table table-hover table-striped table-sm m-0 cart-table">
                                <thead class="bg-white sticky-top">
                                    <tr>
                                        <th width="45%">Producto</th>
                                        <th width="20%" class="text-center">Cant.</th>
                                        <th width="20%" class="text-right">Total</th>
                                        <th width="15%" class="text-center">X</th>
                                    </tr>
                                </thead>
                                <tbody id="cart_tbody">
                                    <!-- Items via AJAX -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Totales y Cobro -->
                        <div class="p-3 bg-light border-top mt-auto">

                            <!-- Tipo de Comprobante + Descuento -->
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="text-muted mb-1" style="font-size:.68rem; font-weight:700; text-transform:uppercase;">Comprobante</label>
                                    <select id="tipo_comprobante" class="form-control form-control-sm">
                                        <option value="ticket">🧾 Ticket</option>
                                        <option value="factura">📄 Factura</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted mb-1" style="font-size:.68rem; font-weight:700; text-transform:uppercase;">Descuento %</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="txt_discount" class="form-control text-center font-weight-bold" value="0" min="0" max="100" step="1">
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subtotal y Total -->
                            <div class="d-flex justify-content-between mb-1" style="font-size:.8rem;">
                                <span class="text-muted">Subtotal</span>
                                <span id="txt_subtotal_display" class="text-muted">Gs. 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" style="font-size:.8rem;">
                                <span class="text-danger">Descuento</span>
                                <span id="txt_discount_display" class="text-danger">- Gs. 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end mb-2 sale-total-row">
                                <h5 class="text-muted text-uppercase font-weight-bold mb-0 sale-total-label" style="font-size:.75rem;">Total a Pagar</h5>
                                <h2 class="text-success font-weight-bold mb-0 sale-total-amount" id="txt_total_sale_display" style="font-size:2rem;">Gs. 0</h2>
                                <input type="hidden" id="txt_total_sale" value="0">
                                <input type="hidden" id="txt_raw_subtotal" value="0">
                            </div>

                            <!-- Método de Pago -->
                            <div class="mb-2">
                                <p class="text-muted mb-1" style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">Método de Pago</p>
                                <div class="pay-methods">
                                    <button type="button" class="btn btn-sm flex-fill pay-method-btn active" data-method="efectivo" style="font-size:.72rem; padding:5px 2px;">
                                        <i class="fas fa-money-bill-wave d-block mb-1" style="font-size:1rem;"></i>Efectivo
                                    </button>
                                    <button type="button" class="btn btn-sm flex-fill pay-method-btn" data-method="qr" style="font-size:.72rem; padding:5px 2px;">
                                        <i class="fas fa-qrcode d-block mb-1" style="font-size:1rem;"></i>QR
                                    </button>
                                    <button type="button" class="btn btn-sm flex-fill pay-method-btn" data-method="tarjeta" style="font-size:.72rem; padding:5px 2px;">
                                        <i class="fas fa-credit-card d-block mb-1" style="font-size:1rem;"></i>Tarjeta
                                    </button>
                                    <button type="button" class="btn btn-sm flex-fill pay-method-btn" data-method="transferencia" style="font-size:.72rem; padding:5px 2px;">
                                        <i class="fas fa-university d-block mb-1" style="font-size:1rem;"></i>Transf.
                                    </button>
                                </div>
                            </div>

                            <!-- Campo Efectivo (solo para efectivo) -->
                            <div id="cash_input_section" class="mb-2">
                                <div class="input-group input-group-sm cash-input-group">
                                    <div class="input-group-prepend"><span class="input-group-text bg-white">Paga Gs.</span></div>
                                    <input type="text" id="txt_payment_with" class="form-control font-weight-bold currency-format" placeholder="0">
                                    <div class="input-group-append"><span class="input-group-text bg-white">Vuelto: <strong id="txt_change" class="text-success ml-1">Gs. 0</strong></span></div>
                                </div>
                            </div>

                            <input type="hidden" id="selected_payment_method" value="efectivo">

                            <div class="row payment-actions">
                                <div class="col-8">
                                    <button class="btn btn-success btn-lg btn-block py-2 font-weight-bold shadow-sm text-uppercase" id="btn_process_sale" disabled style="letter-spacing: 1px; font-size:.85rem;">
                                        <i class="fas fa-cash-register mr-2"></i> COBRAR
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-warning btn-lg btn-block py-2 font-weight-bold" id="btn_credit_sale" disabled title="Venta a Crédito" style="font-size:.80rem;">
                                        <i class="fas fa-clock"></i> Crédito
                                    </button>
                                </div>
                            </div>
                            <button class="btn btn-outline-danger btn-block mt-1 font-weight-bold" id="btn_cancel_sale" style="font-size:.8rem;">
                                <i class="fas fa-ban mr-1"></i> Cancelar Venta
                            </button>
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

<!-- MODAL: CONFIRMAR PAGO -->
<div class="modal fade" id="modalPayment" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold" id="payment_modal_title">
                    <i class="fas fa-cash-register mr-2"></i> Confirmar Pago
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <p class="text-muted text-uppercase small font-weight-bold mb-1">Total a Pagar</p>
                    <h1 class="display-4 font-weight-bold text-success mb-0" id="pay_modal_total_display">Gs. 0</h1>
                </div>

                <!-- Sección Efectivo -->
                <div id="section_pay_cash" style="display:none;">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase">Monto Recibido (Gs.)</label>
                        <input type="text" id="pay_modal_received" class="form-control form-control-lg font-weight-bold text-primary text-center currency-format" style="font-size: 2rem;" placeholder="0">
                    </div>
                    <div class="bg-light p-3 rounded border text-center">
                        <p class="text-muted small text-uppercase font-weight-bold mb-1">Vuelto</p>
                        <h2 class="font-weight-bold text-dark mb-0" id="pay_modal_change">Gs. 0</h2>
                    </div>
                </div>

                <!-- Sección Electrónica (QR/Tarjeta/Transf) -->
                <div id="section_pay_electronic" style="display:none;">
                    <div class="form-group">
                        <label class="font-weight-bold text-muted small text-uppercase" id="lbl_reference_title">Nro. Arqueo / Referencia</label>
                        <input type="text" id="pay_modal_reference" class="form-control form-control-lg font-weight-bold text-center" placeholder="Ingrese el número o ID de transacción...">
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label class="font-weight-bold text-muted small text-uppercase">Nota / Observación (Opcional)</label>
                    <textarea id="pay_modal_note" class="form-control" rows="2" placeholder="Alguna observación sobre este pago..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-lg px-4" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_confirm_final_payment" class="btn btn-success btn-lg px-5 font-weight-bold shadow">
                    <i class="fas fa-check-circle mr-2"></i> REGISTRAR VENTA
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
// Validaciones y Lógica AJAX heredada de functions.js del sistema Venta original
$(document).ready(function() {
    loadCart();

    // 1. Filtrado Rápido por Botones de Categoría (Visual)
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('btn-primary active').addClass('btn-outline-secondary text-dark');
        $(this).removeClass('btn-outline-secondary text-dark').addClass('btn-primary active');
        
        let filter = $(this).attr('data-filter');
        let searchTerm = $('#filter_search').val().toLowerCase();
        
        filterProducts(filter, searchTerm);
    });

    // 2. Filtrado Rápido por Texto (Visual)
    $('#filter_search').on('keyup', function() {
        let searchTerm = $(this).val().toLowerCase();
        let activeFilter = $('.filter-btn.active').attr('data-filter');
        
        filterProducts(activeFilter, searchTerm);
    });

    function filterProducts(category, search) {
        let totalVisible = 0;
        
        $('.product-item').each(function() {
            let matchesCategory = (category === 'all' || $(this).attr('data-category') == category);
            let matchesSearch = (search === '' || $(this).attr('data-search').includes(search));
            
            if (matchesCategory && matchesSearch) {
                $(this).show();
                totalVisible++;
            } else {
                $(this).hide();
            }
        });

        if(totalVisible === 0) {
            $('#no_results_msg').show();
        } else {
            $('#no_results_msg').hide();
        }
    }

    // Customer Search (Misma lógica validada)
    $('#btn_search_customer').click(function() {
        let term = $('#customer_search').val();
        if(term.length > 0) {
            $.post("{{ route('sales.ajax.search_customer') }}", {
                _token: "{{ csrf_token() }}",
                term: term
            }, function(res) {
                $('#customer_id').val(res.id);
                $('#lbl_customer_name').text(res.name);
                $('#lbl_customer_nit').text(res.nit);
                $('#customer_info_panel').slideDown();
            }).fail(function() {
                alert('Cliente no encontrado en la base de datos.');
                $('#customer_id').val(1);
                $('#lbl_customer_name').text('Público General');
                $('#lbl_customer_nit').text('0000000');
            });
        }
    });

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
        
        if(method === 'efectivo') {
            $('#cash_input_section').slideDown(150);
        } else {
            $('#cash_input_section').slideUp(150);
            $('#txt_change').text('Gs. 0');
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

    // Calcular vuelto en tiempo real (Sincronizar ambos campos)
    $('#txt_payment_with, #pay_modal_received').on('keyup input', function() {
        let val = $(this).val();
        $('#txt_payment_with, #pay_modal_received').val(val);
        
        let paga = window.getCleanNumber(val);
        let total = parseFloat($('#txt_total_sale').val()) || 0;
        let vuelto = paga - total;

        let display = vuelto >= 0 
            ? 'Gs. ' + Math.round(vuelto).toLocaleString('de-DE')
            : '-Gs. ' + Math.round(Math.abs(vuelto)).toLocaleString('de-DE');
        
        let color = vuelto >= 0 ? '#059669' : '#dc2626';

        $('#txt_change, #pay_modal_change').text(display).css('color', color);
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
        $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...').prop('disabled', true);

        $.post("{{ route('sales.ajax.process_sale') }}", {
            _token: "{{ csrf_token() }}",
            customer_id: $('#customer_id').val(),
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
                alert('✅ Venta a CRÉDITO registrada con éxito.');
                location.reload();
            }
        }).fail(function(r) {
            alert(r.responseJSON ? r.responseJSON.error : 'Error al procesar el crédito');
            $btn.html(originalHtml).prop('disabled', false);
        });
    });

    // Abrir Modal de Cobro al hacer clic en COBRAR
    $('#btn_process_sale').click(function() {
        let payMethod = $('#selected_payment_method').val();
        let total = parseFloat($('#txt_total_sale').val()) || 0;

        if(total <= 0) return;

        // Configurar modal
        $('#pay_modal_total_display').text('Gs. ' + total.toLocaleString('de-DE'));
        $('#pay_modal_reference').val('');
        $('#pay_modal_note').val('');
        
        // Reset Cash inputs
        if(payMethod === 'efectivo') {
            $('#section_pay_cash').show();
            $('#section_pay_electronic').hide();
            $('#payment_modal_title').html('<i class="fas fa-money-bill-wave mr-2"></i> Cobro en Efectivo');
            setTimeout(() => $('#pay_modal_received').focus().select(), 500);
        } else {
            $('#section_pay_cash').hide();
            $('#section_pay_electronic').show();
            let icon = 'fas fa-credit-card';
            let title = 'Cobro con Tarjeta';

            if(payMethod === 'qr') { icon = 'fas fa-qrcode'; title = 'Cobro con QR'; }
            if(payMethod === 'transferencia') { icon = 'fas fa-university'; title = 'Cobro vía Transferencia'; }
            
            $('#lbl_reference_title').text('Nro. de Referencia / ID Operación');
            $('#payment_modal_title').html(`<i class="${icon} mr-2"></i> ${title}`);
            setTimeout(() => $('#pay_modal_reference').focus(), 500);
        }

        $('#modalPayment').modal('show');
    });

    // Procesar Cobro FINAL desde el MODAL
    $('#btn_confirm_final_payment').click(function() {
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
        $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...').prop('disabled', true);

        $.post("{{ route('sales.ajax.process_sale') }}", {
            _token: "{{ csrf_token() }}",
            customer_id: $('#customer_id').val(),
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
                
                let msg = '✅ Venta procesada con éxito.';
                if(payMethod === 'efectivo' && res.change > 0) {
                    msg += '\n💵 Vuelto: Gs. ' + Math.round(res.change).toLocaleString('de-DE');
                }
                alert(msg);
                location.reload();
            }
        }).fail(function(r) {
            alert(r.responseJSON ? r.responseJSON.error : 'Error al procesar la venta');
            $btn.html('<i class="fas fa-check-circle mr-2"></i> REGISTRAR VENTA').prop('disabled', false);
        });
    });

    // Limpiar carrito (Vaciar y devolver stock)
    $('#btn_clear_cart').click(function() {
        if(confirm('¿Está seguro de vaciar el carrito? El stock de los productos será devuelto al inventario.')) {
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $.post("{{ route('sales.ajax.clear_cart') }}", {
                _token: "{{ csrf_token() }}"
            }, function(res) {
                if(res.success) {
                    loadCart();
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                }
            });
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
            $('#customer_id').val(res.id);
            $('#lbl_customer_name').text(res.name);
            $('#lbl_customer_nit').text(res.nit);
            $('#customer_search').val(res.nit);
            $('#customer_info_panel').slideDown();
            
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
        $('#customer_id').val($(this).data('id'));
        $('#lbl_customer_name').text($(this).data('name'));
        $('#lbl_customer_nit').text($(this).data('nit'));
        $('#customer_info_panel').slideDown();
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
                        <td class="align-middle">${c.nit || '-'}</td>
                        <td class="align-middle font-weight-bold text-dark">${c.name}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm btn-success btn-select-customer rounded-pill px-3 shadow-sm" data-id="${c.id}" data-name="${c.name}" data-nit="${c.nit}">
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

    $('#txt_subtotal_display').text('Gs. ' + Math.round(subtotal).toLocaleString('de-DE'));
    $('#txt_discount_display').text('- Gs. ' + discountAmount.toLocaleString('de-DE'));
    $('#txt_total_sale').val(total);
    $('#txt_total_sale_display').text('Gs. ' + Math.round(total).toLocaleString('de-DE'));
    $('#credit_base_total').text('Gs. ' + Math.round(total).toLocaleString('de-DE'));
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

        html += `<tr>
            <td class="align-middle border-top-0">
                <span class="cart-product-name" title="${d.product.description}">${d.product.description}</span>
                <div class="cart-product-meta">
                    <small class="text-primary mr-2">Gs. ${Math.round(d.price).toLocaleString('de-DE')}</small>
                    ${discount > 0 ? `<small class="text-danger mr-1" title="Descuento">-${Math.round(discount).toLocaleString('de-DE')}</small>` : ''}
                    ${interest > 0 ? `<small class="text-success" title="Recargo">+${Math.round(interest).toLocaleString('de-DE')}</small>` : ''}
                </div>
            </td>
            <td class="align-middle text-center border-top-0">
                <span class="badge badge-light border px-2 py-1 font-weight-bold">${d.quantity}</span>
            </td>
            <td class="align-middle text-right font-weight-bold text-success border-top-0">Gs. ${Math.round(finalTotal).toLocaleString('de-DE')}</td>
            <td class="align-middle text-center border-top-0">
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-info p-1 btn_edit_item" 
                            data-id="${d.id}" data-name="${d.product.description}" 
                            data-qty="${d.quantity}" data-discount="${discount}" data-interest="${interest}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-link text-danger p-1 btn_remove_item" data-id="${d.id}">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    });
    
    if(res.details.length === 0) {
        html = '<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-shopping-basket fa-3x mb-2 opacity-50"></i><br>Agregue productos</td></tr>';
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
                <td class="align-middle font-weight-bold">${c.nit}</td>
                <td class="align-middle">${c.name}</td>
                <td class="text-right">
                    <button class="btn btn-sm btn-primary btn_select_customer_from_list" 
                        data-id="${c.id}" data-nit="${c.nit}" data-name="${c.name}">
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

