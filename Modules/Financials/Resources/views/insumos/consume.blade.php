@extends('admin.layouts.master')

@section('title', 'Consumo de insumos')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-utensils mr-2"></i>Consumo de insumos</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-md-5">
                @can('consume insumo')
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Registrar consumo</h3>
                    </div>
                    <form method="POST" action="{{ route('financials.insumos.consume.store') }}">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label>Insumo</label>
                                <select name="insumo_id" id="insumo_id" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($insumos as $insumo)
                                        <option value="{{ $insumo->id }}"
                                            data-stock="{{ $insumo->stock }}"
                                            {{ old('insumo_id') == $insumo->id ? 'selected' : '' }}>
                                            {{ $insumo->name }} (stock: {{ number_format($insumo->stock, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="text-muted mb-2">Stock actual: <strong id="stock_actual">-</strong></p>
                            <div class="form-group">
                                <label>Cantidad a usar</label>
                                <input type="number" step="any" min="0.01" name="quantity" class="form-control" value="{{ old('quantity') }}" required>
                            </div>
                            <div class="form-group">
                                <label>Observaciones / destino</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Ej: Para 10 porciones">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> Registrar consumo
                            </button>
                        </div>
                    </form>
                </div>
                @endcan
            </div>
            <div class="col-md-7">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Historial</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="form-inline mb-3">
                            <label class="mr-2">Desde</label>
                            <input type="date" name="from" class="form-control form-control-sm mr-2" value="{{ $from }}">
                            <label class="mr-2">Hasta</label>
                            <input type="date" name="to" class="form-control form-control-sm mr-2" value="{{ $to }}">
                            <button class="btn btn-sm btn-primary">Filtrar</button>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Insumo</th>
                                        <th>Cant.</th>
                                        <th>Usuario</th>
                                        <th>Obs.</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($consumptions as $item)
                                    <tr>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $item->insumo->name ?? '-' }}</td>
                                        <td>{{ number_format($item->quantity, 2) }}</td>
                                        <td>{{ $item->user->name ?? '-' }}</td>
                                        <td>{{ $item->notes }}</td>
                                        <td>
                                            @can('consume insumo')
                                            <form action="{{ route('financials.insumos.consume.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Anular este consumo y devolver el stock?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" title="Anular"><i class="fas fa-undo"></i></button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted">Sin consumos en el período</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $consumptions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
@endsection

@push('script')
<script>
    function refreshStock() {
        var opt = $('#insumo_id option:selected');
        var stock = opt.data('stock');
        $('#stock_actual').text(stock === undefined ? '-' : stock);
    }
    $('#insumo_id').on('change', refreshStock);
    refreshStock();
</script>
@endpush
