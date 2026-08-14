@extends('admin.layouts.master')

@section('title', 'Egresos')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Control de Egresos</h1>
            </div>
            <div class="col-sm-6 text-right">
                @can('create expense')
                <a href="{{ route('financials.expenses.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Egreso
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Descripción / Insumo</th>
                            <th class="text-center">Cant.</th>
                            <th>Usuario</th>
                            <th class="text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                            <td>
                                @if($expense->type == 'insumo')
                                    <span class="badge badge-info"><i class="fas fa-boxes mr-1"></i> Insumo</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-file-invoice-dollar mr-1"></i> Gasto</span>
                                @endif
                            </td>
                            <td>
                                <b>{{ $expense->description }}</b>
                                @if($expense->insumo)
                                    <br><small class="text-muted">ID Insumo: #{{ $expense->insumo_id }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $expense->quantity ? number_format($expense->quantity, 2) : '-' }}
                            </td>
                            <td>{{ $expense->user->name }}</td>
                            <td class="text-right font-weight-bold text-danger">
                                Gs. {{ number_format($expense->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No se encontraron egresos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $expenses->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</section>
</div>
@endsection
