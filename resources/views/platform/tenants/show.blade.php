@extends('platform.layout')
@section('title', $tenant->name)
@section('content')
@if ($tenant->adminNeedsPassword())
    <div class="alert alert-info" role="status">
        El admin todavía no definió su contraseña. Le mandamos un enlace a <strong>{{ $tenant->admin_email }}</strong> (vale 48 horas).
        @if (platform_can('tenants.update'))
            <form method="POST" action="{{ route('platform.tenants.invite', $tenant) }}" class="mt-2 mb-0">
                @csrf
                <button class="btn btn-sm btn-primary" type="submit">Reenviar invitación</button>
            </form>
        @endif
    </div>
@endif

<div class="platform-page-head">
    <div>
        <h1>{{ $tenant->name }}</h1>
        <p class="platform-lead mb-2">
            <a href="{{ $tenant->url() }}" target="_blank" rel="noopener noreferrer">{{ $tenant->primaryDomain() }}</a>
            · {{ $tenant->plan?->name ?: 'Sin plan' }}
        </p>
        @include('platform.partials.status-badge', ['tenant' => $tenant])
    </div>
    <div class="platform-actions">
        @if (platform_can('payments.create') && ! $tenant->subscription?->isLifetime())
            <a class="btn btn-success" href="{{ route('platform.payments.create', $tenant) }}">Registrar pago</a>
        @endif
        @if (platform_can('tenants.update'))
            @if ($tenant->status !== 'suspended')
                <form method="POST" action="{{ route('platform.tenants.suspend', $tenant) }}" onsubmit="return confirm(@json('¿Pausar el POS de '.$tenant->name.'? El comercio no va a poder cobrar.'))">
                    @csrf
                    <button class="btn btn-warning" type="submit">Suspender</button>
                </form>
            @else
                <form method="POST" action="{{ route('platform.tenants.reactivate', $tenant) }}">
                    @csrf
                    <button class="btn btn-info" type="submit">Reactivar</button>
                </form>
            @endif
        @endif
        @if (platform_can('tenants.cancel'))
            <form method="POST" action="{{ route('platform.tenants.cancel', $tenant) }}" onsubmit="return confirm(@json('¿Dar de baja a '.$tenant->name.'? No se borra la base.'))">
                @csrf
                <button class="btn btn-secondary" type="submit">Baja</button>
            </form>
        @endif
        @if (platform_can('tenants.delete'))
            <form id="delete-tenant-form" method="POST" action="{{ route('platform.tenants.destroy', $tenant) }}">
                @csrf
                @method('DELETE')
                <input type="password" name="password" id="delete-tenant-password" class="d-none" autocomplete="current-password" tabindex="-1">
                <button class="btn btn-danger" type="button" id="btn-delete-tenant">Eliminar</button>
            </form>
        @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <dl class="platform-meta">
            <div>
                <dt>RUC</dt>
                <dd>{{ $tenant->ruc ?: '—' }}</dd>
            </div>
            <div>
                <dt>Admin</dt>
                <dd>{{ $tenant->admin_name }} &lt;{{ $tenant->admin_email }}&gt;</dd>
            </div>
            <div>
                <dt>Vence</dt>
                <dd>{{ $tenant->subscription?->endsLabel() ?: '—' }}</dd>
            </div>
            <div>
                <dt>Aprovisionado</dt>
                <dd>{{ optional($tenant->provisioned_at)->format('d/m/Y H:i') ?: 'Pendiente' }}</dd>
            </div>
        </dl>
    </div>
</div>

@if (platform_can('tenants.update') && $tenant->provisioned_at)
<div class="card mb-3">
    <div class="card-header">Logo del POS</div>
    <div class="card-body">
        <div class="platform-logo-row">
            @if ($hasCustomLogo)
                <img class="platform-logo-preview" src="{{ route('platform.tenants.logo', $tenant) }}" alt="Logo de {{ $tenant->name }}">
            @else
                <div class="platform-logo-preview platform-logo-preview--empty" aria-hidden="true">
                    <span>{{ strtoupper(mb_substr($tenant->name, 0, 1)) }}</span>
                </div>
            @endif
            <div>
                <p class="platform-lead mb-2">{{ $hasCustomLogo ? 'Este logo se ve en el login y el menú del comercio. Podés reemplazarlo.' : 'Todavía usa el logo por defecto. Subí el de la marca para el login y el menú.' }}</p>
                <form method="POST" action="{{ route('platform.tenants.logo.update', $tenant) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-2">
                        <label for="logo">Archivo</label>
                        <input class="form-control-file @error('logo') is-invalid @enderror" id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" required>
                        <small class="form-text">JPG, PNG, GIF o WebP. Máx. 2 MB.</small>
                        @error('logo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="platform-form-actions">
                        <button class="btn btn-primary" type="submit">Guardar logo</button>
                    </div>
                </form>
                @if ($hasCustomLogo)
                    <form method="POST" action="{{ route('platform.tenants.logo.destroy', $tenant) }}" class="mt-2" onsubmit="return confirm(@json('¿Volver al logo por defecto de '.$tenant->name.'?'))">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-secondary" type="submit">Restablecer</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if (platform_can('tenants.catalog') && $tenant->provisioned_at)
<div class="card mb-3">
    <div class="card-header">Catálogo</div>
    <div class="card-body">
        @if ($catalogSources->isEmpty())
            <p class="platform-lead mb-0">Cuando haya otro comercio con productos, vas a poder copiar el catálogo acá (categorías, marcas y códigos de barra; stock en 0).</p>
        @else
            <p class="platform-lead">Copiá categorías, marcas y productos desde otro comercio. El stock arranca en 0. Si ya hay un producto con el mismo código, no se pisa.</p>
            <form method="POST" action="{{ route('platform.tenants.catalog', $tenant) }}" onsubmit="return confirm(@json('¿Copiar el catálogo a '.$tenant->name.'? El stock queda en 0.'))">
                @csrf
                <div class="platform-form-grid">
                    <div class="form-group">
                        <label for="source_id">Copiar desde</label>
                        <select class="form-control @error('source_id') is-invalid @enderror" id="source_id" name="source_id" required>
                            <option value="">Elegí un comercio</option>
                            @foreach ($catalogSources as $source)
                                <option value="{{ $source->id }}" @selected(old('source_id') == $source->id)>{{ $source->name }} ({{ $source->slug }})</option>
                            @endforeach
                        </select>
                        @error('source_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group d-flex align-items-end">
                        <label class="platform-check mb-2">
                            <input type="checkbox" name="copy_prices" value="1" @checked(old('copy_prices', true))>
                            <span>Incluir precios y costos (punto de partida)</span>
                        </label>
                    </div>
                    <div class="platform-form-actions">
                        <button class="btn btn-primary" type="submit">Copiar catálogo</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">Pagos</div>
    @if ($tenant->payments->isEmpty())
        @include('platform.partials.empty', [
            'title' => $tenant->subscription?->isLifetime() ? 'Sin cobro mensual' : 'Sin pagos',
            'body' => $tenant->subscription?->isLifetime()
                ? 'Instalación propia: la licencia se gestiona afuera del panel. El POS no vence.'
                : 'Registrá el cobro para renovar el período.',
            'actionUrl' => (! $tenant->subscription?->isLifetime() && platform_can('payments.create')) ? route('platform.payments.create', $tenant) : null,
            'actionLabel' => (! $tenant->subscription?->isLifetime() && platform_can('payments.create')) ? 'Registrar pago' : null,
        ])
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Medio</th>
                        <th>Ref</th>
                        <th>Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($tenant->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('d/m/Y') }}</td>
                        <td>{{ money($payment->amount) }}</td>
                        <td>{{ $payment->methodLabel() }}</td>
                        <td>{{ $payment->reference ?: '—' }}</td>
                        <td>
                            @if ($payment->attachment_path)
                                <a href="{{ $payment->attachmentUrl() }}">Ver</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if (platform_can('tenants.delete'))
@push('scripts')
<script src="{{ asset('template/admin/plugins/sweetalert2/sweetalert2.all.js') }}"></script>
<script>
(function () {
    var form = document.getElementById('delete-tenant-form');
    var button = document.getElementById('btn-delete-tenant');
    var passwordInput = document.getElementById('delete-tenant-password');
    if (!form || !button || !passwordInput || typeof Swal === 'undefined') {
        return;
    }

    var options = {
        title: '¿Eliminar este cliente?',
        html: @json('Se va a borrar '.$tenant->name.' y su base '.$tenant->database()->getName().'. El POS deja de existir. Esto no se puede deshacer.'),
        icon: 'warning',
        input: 'password',
        inputPlaceholder: 'Tu contraseña de plataforma',
        inputAttributes: {
            autocomplete: 'current-password',
            maxlength: '72'
        },
        showCancelButton: true,
        focusCancel: true,
        reverseButtons: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        preConfirm: function (value) {
            if (!value) {
                Swal.showValidationMessage('Ingresá tu contraseña para confirmar');
            }
            return value;
        }
    };

    function askPassword(errorMessage) {
        var config = Object.assign({}, options);
        if (errorMessage) {
            config.didOpen = function () {
                Swal.showValidationMessage(errorMessage);
            };
        }
        Swal.fire(config).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }
            passwordInput.value = result.value;
            form.submit();
        });
    }

    button.addEventListener('click', function () {
        askPassword(null);
    });

    @if ($errors->has('password'))
        askPassword(@json($errors->first('password')));
    @endif
})();
</script>
@endpush
@endif
@endsection
