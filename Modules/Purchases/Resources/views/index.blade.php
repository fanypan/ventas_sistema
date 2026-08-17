@extends('admin.layouts.master')

@section('title', 'Compras')

@section('content')
<style>
    .badge-activa   { background: #d1fae5; color: #065f46; }
    .badge-anulada  { background: #fee2e2; color: #991b1b; }
</style>

<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-truck-loading mr-2 text-primary"></i>Historial de Compras</h1>
            </div>
            <div class="col-sm-6 text-right">
                @can('create purchase')
                <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Compra
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover table-striped m-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nº</th>
                            <th>Fecha/Hora</th>
                            <th>Proveedor</th>
                            <th>Usuario</th>
                            <th class="text-center">Estado</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td class="font-weight-bold text-primary">#{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $purchase->supplier->name ?? '-' }}</td>
                            <td>{{ $purchase->creator->name ?? '-' }}</td>
                            <td class="text-center">
                                @if($purchase->status == 1)
                                    <span class="badge badge-activa px-2 py-1 rounded-pill">Activa</span>
                                @else
                                    <span class="badge badge-anulada px-2 py-1 rounded-pill">Anulada</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold">{{ money($purchase->total) }}</td>
                            <td class="text-center">
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-info btn-sm" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($purchase->status != 0)
                                @can('delete purchase')
                                <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Anular Compra" onclick="return confirm('¿Está seguro de anular esta compra? El stock será descontado.')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                @endcan
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-truck-loading fa-3x mb-2 opacity-25 d-block"></i>
                                Sin compras registradas
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
</section>
</div>
@endsection
