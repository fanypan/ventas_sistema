@extends('admin.layouts.master')
@section('title', 'Dashboard')

@section('content')
<style>
    .dashboard-minimal { background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); }
    .dashboard-card { border: 0; border-radius: 24px; text-decoration: none !important; transition: transform .2s ease; }
    .dashboard-card:hover { transform: translateY(-4px); }
    .dashboard-icon { width: 64px; height: 64px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .bg-sale { background: linear-gradient(135deg, #16a34a, #15803d); }
    .bg-credit { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
    .metric-value { font-size: 1.4rem; font-weight: 800; }
</style>

<div class="content-wrapper dashboard-minimal">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0 font-weight-bold">Panel principal</h1>
            <p class="text-muted mb-0">Accesos rápidos y movimiento de productos.</p>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ money($sales_today) }}</h3>
                            <p>Ventas de hoy ({{ $sales_count }})</p>
                        </div>
                        <div class="icon"><i class="fas fa-cash-register"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ money($sales_month) }}</h3>
                            <p>Ventas del mes</p>
                        </div>
                        <div class="icon"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $low_stock }}</h3>
                            <p>Stock bajo (≤ 5)</p>
                        </div>
                        <a href="{{ route('reports.low_stock.pdf', ['threshold' => 5]) }}" target="_blank" class="small-box-footer">Ver reporte</a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $zero_stock }}</h3>
                            <p>Sin existencia</p>
                        </div>
                        <a href="{{ route('products.zero') }}" class="small-box-footer">Ver listado</a>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <a href="{{ route('sales.pos') }}" class="card dashboard-card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="dashboard-icon bg-sale text-white mr-3"><i class="fas fa-cash-register"></i></div>
                            <div>
                                <div class="font-weight-bold">Nueva venta</div>
                                <small class="text-muted">Ir al punto de venta</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="{{ route('credits.receivables') }}" class="card dashboard-card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="dashboard-icon bg-credit text-white mr-3"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div>
                                <div class="font-weight-bold">Cobranzas</div>
                                <small class="text-muted">Cuentas por cobrar</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Productos más vendidos del mes</h3></div>
                        <div class="card-body">
                            <canvas id="chartTop" height="140"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Stock más bajo</h3></div>
                        <div class="card-body">
                            <canvas id="chartLow" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('script')
<script src="{{ asset('template/admin/plugins/chart.js/Chart.bundle.js') }}"></script>
<script>
    var topLabels = @json($chart_top_labels);
    var topQty = @json($chart_top_qty);
    var lowLabels = @json($chart_low_labels);
    var lowQty = @json($chart_low_qty);

    if (document.getElementById('chartTop')) {
        new Chart(document.getElementById('chartTop').getContext('2d'), {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{ label: 'Unidades', data: topQty, backgroundColor: '#16a34a' }]
            },
            options: { legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true } }] } }
        });
    }
    if (document.getElementById('chartLow')) {
        new Chart(document.getElementById('chartLow').getContext('2d'), {
            type: 'horizontalBar',
            data: {
                labels: lowLabels,
                datasets: [{ label: 'Stock', data: lowQty, backgroundColor: '#f59e0b' }]
            },
            options: { legend: { display: false }, scales: { xAxes: [{ ticks: { beginAtZero: true } }] } }
        });
    }
</script>
@endpush
