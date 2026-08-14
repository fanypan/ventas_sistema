@extends('admin.layouts.master')

@section('content')
<div class="content-wrapper">
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Productos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Productos</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Listado de Productos</h3>
                        <div class="card-tools">
                            @can('read report')
                            <a href="{{ route('reports.products.pdf') }}" class="btn btn-warning btn-sm" target="_blank">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </a>
                            <a href="{{ route('reports.products.excel') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </a>
                            @endcan
                            <a href="{{ route('products.zero') }}" class="btn btn-danger btn-sm">
                                <i class="fas fa-box-open"></i> Stock 0
                            </a>
                            @can('create product')
                            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm ml-2">
                                <i class="fas fa-plus"></i> Nuevo Producto
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Marca</th>
                                    <th>Modelo</th>
                                    <th>Categoría</th>
                                    <th>Costo</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr>
                                    <td class="text-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;">
                                        @else
                                            <img src="{{ asset('images/no-image.png') }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;">
                                        @endif
                                    </td>
                                    <td>{{ $product->code }}</td>
                                    <td>{{ $product->description }}</td>
                                    <td>{{ $product->brand ?? '-' }}</td>
                                    <td>{{ $product->model_name ?? '-' }}</td>
                                    <td>{{ $product->category ? $product->category->name : 'N/A' }}</td>
                                    <td>{{ money($product->cost, false) }}</td>
                                    <td>{{ money($product->price, false) }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        @if($product->status == 1)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            @if($product->code)
                                            <a href="{{ route('products.barcode', $product->id) }}" class="btn btn-secondary btn-sm" title="Imprimir código de barras" target="_blank">
                                                <i class="fas fa-barcode"></i>
                                            </a>
                                            @endif
                                            @can('update product')
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete product')
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $products->links('pagination::bootstrap-4') }}
                    </div>
                </div>
                
                @php
                    $inversion = \Modules\Products\Entities\Product::where('status', 1)->get()->sum(function($p) { return $p->stock * $p->cost; });
                    $proyeccion = \Modules\Products\Entities\Product::where('status', 1)->get()->sum(function($p) { return $p->stock * $p->price; });
                    $ganancia = $proyeccion - $inversion;
                    $utilidad = ($inversion > 0) ? ($ganancia / $inversion) * 100 : 0;
                @endphp
                
                <div class="card mt-4 shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-chart-line mr-2"></i> Resumen de Rentabilidad</h3>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row text-center font-weight-bold">
                            <div class="col-md-4 mb-2">
                                <span class="text-muted d-block text-uppercase small">Inversión Total</span>
                                <h4 class="text-dark mb-0">{{ money($inversion) }}</h4>
                            </div>
                            <div class="col-md-4 mb-2 border-left border-right">
                                <span class="text-muted d-block text-uppercase small">Proyección de Ventas</span>
                                <h4 class="text-primary mb-0">{{ money($proyeccion) }}</h4>
                            </div>
                            <div class="col-md-4 mb-2">
                                <span class="text-muted d-block text-uppercase small">Utilidad Estimada</span>
                                <h4 class="text-success mb-0">{{ number_format($utilidad, 2) }}%</h4>
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
