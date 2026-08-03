@extends('admin.layouts.master')
@section('title', 'Dashboard')

@section('content')
<style>
    .dashboard-minimal {
        min-height: calc(100vh - 120px);
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    }

    .dashboard-card {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-decoration: none !important;
    }

    .dashboard-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14) !important;
    }

    .dashboard-icon {
        width: 84px;
        height: 84px;
        border-radius: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .dashboard-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
    }

    .dashboard-text {
        color: #475569;
        margin-bottom: 0;
    }

    .bg-sale {
        background: linear-gradient(135deg, #16a34a, #15803d);
    }

    .bg-credit {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }
</style>

<div class="content-wrapper dashboard-minimal">
    <div class="content-header">
        <div class="container-fluid">
            <div class="text-center py-4">
                <h1 class="m-0 font-weight-bold">Panel Principal</h1>
                <p class="text-muted mb-0 mt-2">Accesos directos al flujo principal del sistema.</p>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-6 mb-4">
                    <a href="{{ route('sales.pos') }}" class="card dashboard-card shadow-sm h-100">
                        <div class="card-body p-5 text-center">
                            <div class="dashboard-icon bg-sale text-white mx-auto mb-4">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <div class="dashboard-title mb-2">Nueva Venta</div>
                            <p class="dashboard-text">Ir al punto de venta para registrar una venta nueva.</p>
                        </div>
                    </a>
                </div>

                <div class="col-lg-5 col-md-6 mb-4">
                    <a href="{{ route('credits.receivables') }}" class="card dashboard-card shadow-sm h-100">
                        <div class="card-body p-5 text-center">
                            <div class="dashboard-icon bg-credit text-white mx-auto mb-4">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="dashboard-title mb-2">Cobranzas</div>
                            <p class="dashboard-text">Abrir el módulo de créditos y cuentas por cobrar.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
