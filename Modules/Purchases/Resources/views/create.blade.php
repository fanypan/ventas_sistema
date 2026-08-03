@extends('admin.layouts.master')

@section('title', 'Nueva Compra')

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
        border-color: var(--success) !important;
    }
    .product-img-placeholder {
        height: 100px;
        background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #00796b;
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
        background-color: #a7f3d0;
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
</style>

<div class="content-wrapper">
    <div class="content-header pb-1">
        <div class="container-fluid">
            <h1 class="m-0 text-success"><i class="fas fa-truck-loading mr-2"></i>Nueva Compra (Recepción)</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
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
                                <input type="text" id="filter_search" class="form-control border-left-0 form-control-lg" placeholder="Buscar por código, descripción o marca..." autofocus>
                            </div>
                            
                            <div class="category-filter" id="category_filters">
                                <button class="btn btn-success rounded-pill px-4 mr-2 filter-btn active" data-filter="all">Todas</button>
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
                                    <div class="card h-100 shadow-sm border pos-product-card" onclick="openDetailsModal({{ $prod->id }}, '{{ addslashes($prod->description) }}', {{ $prod->cost }})">
                                        <div class="product-img-placeholder">
                                            @if($prod->image)
                                                <img src="{{ asset('storage/' . $prod->image) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <i class="fas fa-box text-success opacity-50"></i>
                                            @endif
                                        </div>
                                        <div class="card-body p-2 d-flex flex-column">
                                            <span class="badge badge-secondary align-self-start mb-1">{{ $prod->code }}</span>
                                            <h6 class="font-weight-bold mb-1 text-truncate" title="{{ $prod->description }}">{{ $prod->description }}</h6>
                                            <small class="text-muted text-truncate d-block mb-2">{{ $prod->brand }}</small>
                                            
                                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                                <h5 class="text-secondary font-weight-bold mb-0">Gs. {{ number_format($prod->cost, 0, ',', '.') }}</h5>
                                                <span class="badge badge-light border">{{ $prod->stock }} stk</span>
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
                        <div class="card-header bg-success text-white p-3 d-flex justify-content-between align-items-center">
                            <h4 class="card-title font-weight-bold mb-0"><i class="fas fa-list-ul mr-2"></i>Lista de Ingreso</h4>
                            <span class="badge badge-light text-success px-3 py-1 rounded-pill" id="item_count">0</span>
                        </div>
                        
                        <!-- Proveedor -->
                        <div class="p-3 bg-light border-bottom">
                            <div class="form-group mb-0">
                                <label class="text-sm text-muted mb-1"><i class="fas fa-truck mr-1"></i> Proveedor Remitente</label>
                                <select id="supplier_id" class="form-control select2">
                                    <option value="" disabled selected>-- Seleccione un Proveedor --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Carrito Tabla -->
                        <div class="cart-container p-0">
                            <table class="table table-hover table-striped table-sm m-0">
                                <thead class="bg-white sticky-top">
                                    <tr>
                                        <th width="40%">Lote / Prod</th>
                                        <th width="15%" class="text-center">Cant.</th>
                                        <th width="20%" class="text-right">Costo U.</th>
                                        <th width="20%" class="text-right">Total</th>
                                        <th width="5%" class="text-center">X</th>
                                    </tr>
                                </thead>
                                <tbody id="purchase_tbody">
                                    <!-- Items Javascript -->
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-box-open fa-3x mb-2 opacity-50"></i><br>Agregue el código<br>usando la cuadrilla</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totales y Cobro -->
                        <div class="p-3 bg-light border-top mt-auto">
                            <div class="d-flex justify-content-between align-items-end mb-3">
                                <h5 class="text-muted text-uppercase font-weight-bold mb-0">Total Inversión</h5>
                                <h1 class="text-dark font-weight-bold mb-0 display-4" id="purchase_total_label">Gs. 0</h1>
                            </div>

                            <button class="btn btn-success btn-lg btn-block py-3 font-weight-bold shadow shadow-sm text-uppercase" id="btn_finalize_purchase" disabled style="letter-spacing: 2px;">
                                <i class="fas fa-save mr-2"></i> REGISTRAR COMPRA
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- Modal Ingreso Específico -->
<div class="modal fade" id="itemDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Datos de Ingreso</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <h5 id="modal_product_name" class="text-primary font-weight-bold mb-4 border-bottom pb-2"></h5>
                
                <input type="hidden" id="modal_product_id">

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="text-muted">Cantidad Entrante</label>
                        <input type="number" id="modal_qty" class="form-control form-control-lg text-center font-weight-bold" value="1" min="1">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-muted">Costo Unitario (Gs.)</label>
                        <input type="text" id="modal_cost" class="form-control form-control-lg text-right currency-format" placeholder="0">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="text-muted text-sm">Nº Lote (Opcional)</label>
                        <input type="text" id="modal_lot" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-muted text-sm">Vencimiento (Opcional)</label>
                        <input type="date" id="modal_expiration" class="form-control form-control-sm">
                    </div>
                </div>
                
                <button type="button" class="btn btn-success btn-block btn-lg mt-3" id="btn_add_to_purchase_list">
                    Añadir a la Lista
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
$(document).ready(function() {
    let purchaseItems = [];

    // Grid Filtering
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('btn-success active').addClass('btn-outline-secondary text-dark');
        $(this).removeClass('btn-outline-secondary text-dark').addClass('btn-success active');
        
        let filter = $(this).attr('data-filter');
        let searchTerm = $('#filter_search').val().toLowerCase();
        
        filterProducts(filter, searchTerm);
    });

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

        if(totalVisible === 0) $('#no_results_msg').show();
        else $('#no_results_msg').hide();
    }

    // Modal Interaction for Details
    window.openDetailsModal = function(id, name, currentCost) {
        $('#modal_product_id').val(id);
        $('#modal_product_name').text(name);
        $('#modal_cost').val(currentCost);
        $('#modal_qty').val(1);
        $('#modal_lot').val('');
        $('#modal_expiration').val('');
        
        $('#itemDetailsModal').modal('show');
        
        // Auto-focus after open
        setTimeout(() => { $('#modal_qty').focus().select(); }, 500);
    };

    // Add Item to Array
    $('#btn_add_to_purchase_list').click(function() {
        let id = $('#modal_product_id').val();
        let desc = $('#modal_product_name').text();
        let qty = parseInt($('#modal_qty').val());
        let cost = window.getCleanNumber($('#modal_cost').val());
        let lot = $('#modal_lot').val();
        let exp = $('#modal_expiration').val();
        
        if(qty < 1 || isNaN(qty)) return alert("Cantidad inválida");
        if(cost < 0 || isNaN(cost)) return alert("Costo inválido");

        let item = {
            id: id,
            description: desc,
            quantity: qty,
            price: cost,
            expiration: exp,
            lot: lot,
            subtotal: qty * cost
        };

        purchaseItems.push(item);
        renderPurchaseTable();
        
        $('#itemDetailsModal').modal('hide');
        $('#filter_search').val('').focus();
    });

    window.removeItem = function(index) {
        purchaseItems.splice(index, 1);
        renderPurchaseTable();
    };

    function renderPurchaseTable() {
        let html = '';
        let total = 0;

        purchaseItems.forEach((item, index) => {
            total += item.subtotal;
            let lotBadge = item.lot ? `<br><span class="badge badge-warning text-xs">L: ${item.lot}</span>` : '';
            html += `<tr>
                <td class="align-middle border-top-0">
                    <span class="font-weight-bold d-block text-truncate" style="max-width: 150px;">${item.description}</span>
                    ${lotBadge}
                </td>
                <td class="align-middle text-center border-top-0 font-weight-bold">${item.quantity}</td>
                <td class="align-middle text-right border-top-0 text-muted">Gs. ${item.price.toLocaleString('de-DE')}</td>
                <td class="align-middle text-right border-top-0 font-weight-bold text-dark">Gs. ${item.subtotal.toLocaleString('de-DE')}</td>
                <td class="align-middle text-center border-top-0">
                    <button class="btn btn-sm btn-link text-danger p-0" onclick="removeItem(${index})">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </td>
            </tr>`;
        });

        if(purchaseItems.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-box-open fa-3x mb-2 opacity-50"></i><br>Agregue el código<br>usando la cuadrilla</td></tr>';
        }

        $('#purchase_tbody').html(html);
        $('#purchase_total_label').text('Gs. ' + total.toLocaleString('de-DE'));
        $('#item_count').text(purchaseItems.length);
        $('#btn_finalize_purchase').prop('disabled', purchaseItems.length === 0);
    }

    // Finalize
    $('#btn_finalize_purchase').click(function() {
        let supplier = $('#supplier_id').val();
        if(!supplier) return alert("Debe seleccionar un proveedor primero.");
        if(!confirm('¿Registrar compra y afectar el inventario?')) return;
        
        $(this).html('<i class="fas fa-spinner fa-spin mr-2"></i> REGISTRANDO...').prop('disabled', true);

        let data = {
            _token: "{{ csrf_token() }}",
            supplier_id: supplier,
            items: purchaseItems
        };

        $.post("{{ route('purchases.store') }}", data, function(res) {
            alert('Compra registrada y stock actualizado correctamente');
            window.location.href = "{{ route('purchases.index') }}";
        }).fail(function() {
            alert('Error al registrar la compra');
            $('#btn_finalize_purchase').html('<i class="fas fa-save mr-2"></i> REGISTRAR COMPRA').prop('disabled', false);
        });
    });
});
</script>
@endpush
