@extends('admin.layouts.master')

@section('title', 'Centro de Reportes')

@section('content')
<div class="content-wrapper">
    <section class="content-header pb-1">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-premium"><i class="fas fa-file-invoice mr-2"></i>Centro de Reportes</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Reportes</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline card-tabs shadow-sm">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="inventory-tab" data-toggle="pill" href="#tab-inventory" role="tab">
                                <i class="fas fa-boxes mr-1"></i> Inventario
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="sales-tab" data-toggle="pill" href="#tab-sales" role="tab">
                                <i class="fas fa-shopping-cart mr-1"></i> Ventas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="finance-tab" data-toggle="pill" href="#tab-finance" role="tab">
                                <i class="fas fa-university mr-1"></i> Finanzas
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content" id="reportTabsContent">
                        
                        <!-- TAB: INVENTARIO -->
                        <div class="tab-pane fade show active" id="tab-inventory" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="border rounded p-3 h-100 bg-light shadow-xs">
                                        <h5 class="font-weight-bold text-primary"><i class="fas fa-box mt-1 mr-2"></i>Catálogo Completo</h5>
                                        <p class="text-sm text-muted mb-4">Listado de todos los productos activos con sus costos, precios y stock actual.</p>
                                        <div class="d-flex gap-2" style="gap:10px;">
                                            <a href="{{ route('reports.products.pdf') }}" class="btn btn-warning flex-fill" target="_blank">
                                                <i class="fas fa-file-pdf mr-1"></i> PDF
                                            </a>
                                            <a href="{{ route('reports.products.excel') }}" class="btn btn-success flex-fill">
                                                <i class="fas fa-file-excel mr-1"></i> Excel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="border border-danger rounded p-3 h-100 bg-white shadow-xs">
                                        <h5 class="font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mt-1 mr-2"></i>Stock Mínimo y Agotados</h5>
                                        <p class="text-sm text-muted">Productos que requieren reposición inmediata. Stock menor o igual a:</p>
                                        <form action="{{ route('reports.low_stock.pdf') }}" method="GET" target="_blank">
                                            <div class="input-group mb-2">
                                                <input type="number" class="form-control" name="threshold" value="5" min="0">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Generar</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: VENTAS -->
                        <div class="tab-pane fade" id="tab-sales" role="tabpanel">
                            <div class="row">
                                <!-- Ventas Generales -->
                                <div class="col-md-4 mb-4">
                                    <div class="border rounded p-3 bg-light shadow-xs h-100">
                                        <h6 class="font-weight-bold text-success text-uppercase small mb-3">Historial General de Ventas</h6>
                                        <form action="{{ route('reports.sales.pdf') }}" method="GET" target="_blank">
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Rango de Fechas</label>
                                                <div class="d-flex gap-1" style="gap:5px;">
                                                    <input type="date" class="form-control form-control-sm" name="start_date" value="{{ date('Y-m-d') }}">
                                                    <input type="date" class="form-control form-control-sm" name="end_date" value="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-success btn-block"><i class="fas fa-file-pdf mr-1"></i> Generar Reporte</button>
                                        </form>
                                    </div>
                                </div>
                                <!-- Ventas por Pago -->
                                <div class="col-md-4 mb-4">
                                    <div class="border rounded p-3 bg-white shadow-xs h-100" style="border-top:3px solid #7c3aed !important;">
                                        <h6 class="font-weight-bold text-uppercase small mb-3" style="color:#7c3aed;">Ventas por Tipo de Pago</h6>
                                        <form action="{{ route('reports.sales_by_payment.pdf') }}" method="GET" target="_blank">
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Rango de Fechas</label>
                                                <div class="d-flex gap-1" style="gap:5px;">
                                                    <input type="date" class="form-control form-control-sm" name="start_date" value="{{ date('Y-m-01') }}">
                                                    <input type="date" class="form-control form-control-sm" name="end_date" value="{{ date('Y-m-t') }}">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-block text-white" style="background:#7c3aed;"><i class="fas fa-file-pdf mr-1"></i> Generar Reporte</button>
                                        </form>
                                    </div>
                                </div>
                                <!-- Ventas por Producto -->
                                <div class="col-md-4 mb-4">
                                    <div class="border rounded p-3 bg-light shadow-xs h-100">
                                        <h6 class="font-weight-bold text-primary text-uppercase small mb-3">Rendimiento por Producto</h6>
                                        <form action="{{ route('reports.sales_by_product.pdf') }}" method="GET" target="_blank">
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Rango de Fechas</label>
                                                <div class="d-flex gap-1" style="gap:5px;">
                                                    <input type="date" class="form-control form-control-sm" name="start_date" value="{{ date('Y-m-01') }}">
                                                    <input type="date" class="form-control form-control-sm" name="end_date" value="{{ date('Y-m-t') }}">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-file-pdf mr-1"></i> Generar Reporte</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: FINANZAS -->
                        <div class="tab-pane fade" id="tab-finance" role="tabpanel">
                            <div class="row">
                                <!-- Arqueo de Caja -->
                                <div class="col-md-4 mb-4">
                                    <div class="card card-outline card-info">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold text-info text-uppercase small"><i class="fas fa-cash-register mr-1"></i> Arqueo de Caja Diario</h6>
                                            <p class="text-xs text-muted">Ingresos vs Egresos del día.</p>
                                            <form action="{{ route('reports.cash.pdf') }}" method="GET" target="_blank">
                                                <input type="date" class="form-control form-control-sm mb-2" name="date" value="{{ date('Y-m-d') }}">
                                                <button type="submit" class="btn btn-info btn-sm btn-block">Generar PDF</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Compras -->
                                <div class="col-md-4 mb-4">
                                    <div class="card card-outline card-danger">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold text-danger text-uppercase small"><i class="fas fa-shopping-bag mr-1"></i> Reporte de Compras</h6>
                                            <p class="text-xs text-muted">Resumen de adquisición de mercadería.</p>
                                            <form action="{{ route('reports.purchases.pdf') }}" method="GET" target="_blank">
                                                <div class="d-flex gap-1 mb-2" style="gap:2px;">
                                                    <input type="date" class="form-control form-control-sm" name="start_date" value="{{ date('Y-m-d') }}">
                                                    <input type="date" class="form-control form-control-sm" name="end_date" value="{{ date('Y-m-d') }}">
                                                </div>
                                                <button type="submit" class="btn btn-danger btn-sm btn-block">Generar PDF</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Estado de Resultados -->
                                <div class="col-md-4 mb-4">
                                    <div class="card card-outline card-warning">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold text-warning text-uppercase small"><i class="fas fa-chart-line mr-1"></i> Estado de Resultados</h6>
                                            <p class="text-xs text-muted">Utilidad bruta y neta del periodo.</p>
                                            <form action="{{ route('reports.financial_status.pdf') }}" method="GET" target="_blank">
                                                <div class="d-flex gap-1 mb-2" style="gap:2px;">
                                                    <input type="date" class="form-control form-control-sm" name="start_date" value="{{ date('Y-m-01') }}">
                                                    <input type="date" class="form-control form-control-sm" name="end_date" value="{{ date('Y-m-t') }}">
                                                </div>
                                                <button type="submit" class="btn btn-warning btn-sm btn-block">Generar PDF</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .nav-tabs .nav-link.active {
        border-top: 3px solid #007bff !important;
        color: #007bff !important;
    }
    .text-premium {
        color: #2c3e50;
        letter-spacing: -0.5px;
    }
    .shadow-xs {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
</style>
@endsection
