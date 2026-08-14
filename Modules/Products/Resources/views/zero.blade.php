@extends('admin.layouts.master')

@section('title', 'Productos sin existencia')

@section('content')
<div class="content-wrapper">
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Productos sin existencia (stock 0)</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('products.zero.excel') }}" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped m-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th>Costo</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->description }}</td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>{{ money($product->cost) }}</td>
                            <td>{{ money($product->price) }}</td>
                            <td class="text-danger font-weight-bold">{{ $product->stock }}</td>
                            <td>
                                @can('update product')
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No hay productos agotados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
</div>
@endsection
