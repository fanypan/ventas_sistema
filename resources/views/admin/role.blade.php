@extends('admin.layouts.master')
@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $title ?? 'Roles' }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? 'Roles' }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            @can('create role')
                            <div class="card-header">
                                <h3 class="card-title">
                                    <button type="button" class="btn btn-sm btn-success" id="btn-tambah"><i class="fas fa-plus"></i> Nuevo</button>
                                </h3>
                            </div>
                            @endcan
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Permisos</th>
                                            <th>Actualizado</th>
                                            @canany(['update role', 'delete role'])
                                                <th>Acción</th>
                                            @endcanany
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data as $i)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $i->name }}</td>
                                                <td>
                                                    @if ($i->name == 'superadmin' || $i->permissions->count() === $permission->count())
                                                        Todos los permisos
                                                    @else
                                                        {{ $i->permissions->count() }} permisos
                                                    @endif
                                                </td>
                                                <td>{{ $i->updated_at }}</td>
                                                @canany(['update role', 'delete role'])
                                                    <td>
                                                        <div class="btn-group">
                                                            @can('update role')
                                                                @if ($i->name != 'superadmin')
                                                                    <button type="button" class="btn btn-sm btn-primary btn-edit" data-id="{{ $i->id }}"><i class="fas fa-pencil-alt"></i></button>
                                                                @endif
                                                            @endcan
                                                            @can('delete role')
                                                                @if ($i->name != 'superadmin')
                                                                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="{{ $i->id }}" data-name="{{ $i->name }}"><i class="fas fa-trash"></i></button>
                                                                @endif
                                                            @endcan
                                                        </div>
                                                    </td>
                                                @endcanany
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $(document).on("click", '#btn-tambah', function() {
                $('#modal-tambah input.permission').prop('checked', false);
                $('#checkAll').prop('checked', false);
                $('#modal-tambah').modal({backdrop: 'static', keyboard: false, show: true});
            });
            $(document).on("click", '.btn-edit', function() {
                let id = $(this).attr("data-id");
                $('#modal-loading').modal({backdrop: 'static', keyboard: false, show: true});
                $.ajax({
                    url: "{{ route('role.show') }}",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        var data = data.data;
                        var permissions = data.permissions;
                        $('#modal-edit input.permission').prop('checked', false).prop('disabled', false);
                        $("#checkAllu").prop('checked', false).prop('disabled', false);
                        if (permissions.length == {{ count($permission) }}) {
                            $("#checkAllu").prop('checked', true);
                        }
                        if (data.name == 'superadmin') {
                            $('#modal-edit input.permission').prop('checked', true).prop('disabled', true);
                            $("#checkAllu").prop('checked', true).prop('disabled', true);
                        } else {
                            for (let i = 0; i < permissions.length; i++) {
                                $(`#${permissions[i].id}u`).prop('checked', true);
                            }
                        }
                        $("#name").val(data.name);
                        $("#id").val(data.id);
                        $('#modal-loading').modal('hide');
                        $('#modal-edit').modal({backdrop: 'static', keyboard: false, show: true});
                    },
                });
            });

            $(document).on("click", '.btn-delete', function() {
                let id = $(this).attr("data-id");
                let name = $(this).attr("data-name");
                $("#did").val(id);
                $("#delete-data").html(name);
                $('#modal-delete').modal({backdrop: 'static', keyboard: false, show: true});
            });

            $("#checkAll").on('click', function() {
                $('#modal-tambah input.permission').prop('checked', this.checked);
            });

            $("#checkAllu").on('click', function() {
                $('#modal-edit input.permission').prop('checked', this.checked);
            });
        });
    </script>
@endsection

@section('modal')
    <div class="modal fade" id="modal-tambah">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo rol</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('role.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="guard_name" value="web">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="role-name-create">Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="role-name-create" placeholder="Ej: cajero" name="name" value="{{ old('name') }}" required maxlength="50">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <p class="mb-2 font-weight-bold">Permisos</p>
                        @error('permissions')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror
                        <div class="icheck-primary mb-2">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                            <label class="form-check-label" for="checkAll">Seleccionar todo</label>
                        </div>
                        @include('admin.partials.role-permissions')
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar rol</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('role.update') }}" method="POST">
                    @csrf
                    @method("PUT")
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="guard_name" value="web">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Nombre" name="name" id="name" value="{{ old('name') }}" required maxlength="50">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <p class="mb-2 font-weight-bold">Permisos</p>
                        <div class="icheck-primary mb-2">
                            <input class="form-check-input" type="checkbox" id="checkAllu">
                            <label class="form-check-label" for="checkAllu">Seleccionar todo</label>
                        </div>
                        @include('admin.partials.role-permissions', ['idSuffix' => 'u'])
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-delete">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Eliminar rol</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('role.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p class="modal-text">¿Estás seguro de que querés eliminar a <b id="delete-data"></b>?</p>
                        <input type="hidden" name="id" id="did">
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
