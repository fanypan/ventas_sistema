@extends('admin.layouts.master')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Mantenimiento de Marcas</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('create brand')
                    <a href="{{ route('brands.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Marca
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Éxito!</h5>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-body">
                    <table class="table table-hover table-sm" id="brands-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">ID</th>
                                <th>Marca</th>
                                <th>País</th>
                                <th>Descripción</th>
                                <th width="100" class="text-center">Estado</th>
                                <th width="120" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($brands as $brand)
                            <tr>
                                <td>{{ $brand->id }}</td>
                                <td class="font-weight-bold">{{ $brand->name }}</td>
                                <td>{{ $brand->country ?? 'N/A' }}</td>
                                <td><small class="text-muted">{{ Str::limit($brand->description, 50) }}</small></td>
                                <td class="text-center">
                                    @can('update brand')
                                    <form action="{{ route('brands.status', $brand->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-xs {{ $brand->status == 1 ? 'btn-success' : 'btn-danger' }}">
                                            {{ $brand->status == 1 ? 'Activo' : 'Inactivo' }}
                                        </button>
                                    </form>
                                    @else
                                    <span class="badge badge-{{ $brand->status == 1 ? 'success' : 'danger' }}">
                                        {{ $brand->status == 1 ? 'Activo' : 'Inactivo' }}
                                    </span>
                                    @endcan
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        @can('update brand')
                                        <a href="{{ route('brands.edit', $brand->id) }}" class="btn btn-info btn-xs mr-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete brand')
                                        <form action="{{ route('brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('¿Seguro de eliminar esta marca?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs">
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
            </div>
        </div>
    </section>
</div>

@push('script')
<script>
    $(document).ready(function() {
        $('#brands-table').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
            }
        });
    });
</script>
@endpush
@endsection
