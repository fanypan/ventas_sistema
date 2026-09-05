@extends('admin.layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Ayuda del POS</h1>
            <p class="text-muted mb-0">Cómo cobrar, cómo cargar una compra y los atajos de teclado. Pensado para el mostrador.</p>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid help-page">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header p-2">
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#help-venta" data-toggle="tab" role="tab">Punto de venta</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#help-compra" data-toggle="tab" role="tab">Compras</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#help-atajos" data-toggle="tab" role="tab">Atajos</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="help-venta" role="tabpanel">
                            <h2 class="h5 font-weight-bold mb-3">Cobrar en el mostrador</h2>
                            <ol class="help-steps pl-3 mb-4">
                                <li>La caja tiene que estar <strong>abierta</strong>. Si está cerrada, el POS te manda a Cajas antes de vender.</li>
                                <li>Buscá el producto por código, código de barras o descripción (<kbd class="pos-kbd">F2</kbd>). Con <kbd class="pos-kbd">Enter</kbd> se agrega al carrito. También podés hacer clic en la grilla.</li>
                                <li>El cliente es opcional. Si hace falta factura o crédito, buscalo por NIT/RUC o nombre (<kbd class="pos-kbd">F4</kbd>). Si no está, lo das de alta ahí mismo.</li>
                                <li>Cuando el carrito está listo, <strong>Cobrar</strong> (<kbd class="pos-kbd">F8</kbd>). Elegí el método: efectivo pide el monto y muestra el vuelto; QR, tarjeta o transferencia piden una referencia.</li>
                                <li><kbd class="pos-kbd">Enter</kbd> registra la venta. Se abre el ticket o la factura. Stock ya se descontó al armar el carrito.</li>
                            </ol>
                            <div class="alert alert-light border mb-0">
                                <strong>Crédito.</strong> <kbd class="pos-kbd">F9</kbd> abre cuotas y recargo. Hace falta un cliente (no Público General). El ticket también se imprime al confirmar.
                            </div>
                        </div>

                        <div class="tab-pane" id="help-compra" role="tabpanel">
                            <h2 class="h5 font-weight-bold mb-3">Registrar una compra</h2>
                            <ol class="help-steps pl-3 mb-4">
                                <li>Seleccioná el <strong>proveedor</strong> (<kbd class="pos-kbd">F4</kbd>). Sin proveedor no se registra.</li>
                                <li>Buscá el producto (<kbd class="pos-kbd">F2</kbd>) o hacé clic en la grilla. En cada ítem cargás cantidad, costo, y si hace falta lote y vencimiento.</li>
                                <li><strong>Registrar compra</strong> (<kbd class="pos-kbd">F8</kbd>) suma el stock. El costo del producto pasa a ser el de esta compra (último costo, no un promedio). El precio de venta no cambia.</li>
                            </ol>
                            <div class="alert alert-light border mb-0">
                                Si te equivocaste, anulá la compra desde el historial: se descuenta el stock que había ingresado.
                            </div>
                        </div>

                        <div class="tab-pane" id="help-atajos" role="tabpanel">
                            <p class="text-muted">En venta y compra podés abrir esta cheatsheet con <kbd class="pos-kbd">?</kbd> (fuera de un campo) o <kbd class="pos-kbd">Alt</kbd> <kbd class="pos-kbd">Shift</kbd> <kbd class="pos-kbd">H</kbd>.</p>
                            <p class="small text-muted mb-4">Evitamos Ctrl+T, Ctrl+W, F5, F12 y demás combos del navegador. Los atajos de caja son teclas de función que Chrome, Firefox y Edge no usan: F2, F4, F8 y F9.</p>

                            <div class="row">
                                <div class="col-lg-6 mb-4 mb-lg-0">
                                    <h2 class="h6 font-weight-bold text-uppercase text-muted mb-3">Nueva venta</h2>
                                    @include('admin.partials.help-shortcut-table', ['rows' => $saleShortcuts])
                                </div>
                                <div class="col-lg-6">
                                    <h2 class="h6 font-weight-bold text-uppercase text-muted mb-3">Nueva compra</h2>
                                    @include('admin.partials.help-shortcut-table', ['rows' => $purchaseShortcuts])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
