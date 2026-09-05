@extends('admin.layouts.master')
@section('title', 'Configuración')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Configuración</h1>
                <p class="text-muted mb-0">Nombre, logo y datos de la empresa. Lo que se ve en la caja y en los tickets.</p>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid settings-page" data-fm-url="{{ route('fm.fm-button') }}">
                @if ($categories->isEmpty())
                    <div class="card">
                        <div class="card-body">
                            <p class="mb-0 text-muted">Todavía no hay ajustes cargados para este comercio.</p>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills" role="tablist">
                                @foreach ($categories as $category)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                           id="tab-{{ $category }}"
                                           href="#settings-{{ $category }}"
                                           data-toggle="tab"
                                           role="tab"
                                           aria-controls="settings-{{ $category }}"
                                           aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                            {{ \App\Models\Setting::categoryLabel($category) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                @foreach ($categories as $category)
                                    @php
                                        $items = $grouped->get($category, collect());
                                        $files = $items->where('type', 'file');
                                        $fields = $items->where('type', '!=', 'file');
                                        $lead = \App\Models\Setting::categoryLead($category);
                                    @endphp
                                    <div class="tab-pane {{ $loop->first ? 'active' : '' }}"
                                         id="settings-{{ $category }}"
                                         role="tabpanel"
                                         aria-labelledby="tab-{{ $category }}">
                                        @if ($lead)
                                            <p class="settings-tab-lead">{{ $lead }}</p>
                                        @endif

                                        <form action="{{ route('setting.update') }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="tab" value="{{ $category }}">

                                            @if ($files->isNotEmpty())
                                                <div class="settings-assets">
                                                    @foreach ($files as $set)
                                                        <div class="settings-asset">
                                                            <button type="button"
                                                                    class="settings-asset-preview js-setting-file"
                                                                    data-input="{{ $set->key }}"
                                                                    @if (! $canUpdate) disabled @endif
                                                                    aria-label="Cambiar {{ $set->displayLabel() }}">
                                                                <img src="{{ setting_file_url($set->value) }}"
                                                                     alt="{{ $set->displayLabel() }}"
                                                                     id="{{ $set->key }}-image">
                                                            </button>
                                                            <div class="settings-asset-body">
                                                                <p class="settings-label" id="label-{{ $set->key }}">{{ $set->displayLabel() }}</p>
                                                                @if ($set->hint())
                                                                    <p class="settings-hint">{{ $set->hint() }}</p>
                                                                @endif
                                                                <input type="hidden" name="key[]" value="{{ $set->key }}">
                                                                <input type="hidden"
                                                                       name="value[]"
                                                                       id="{{ $set->key }}"
                                                                       value="{{ $set->value }}"
                                                                       required>
                                                                @if ($canUpdate)
                                                                    <button type="button"
                                                                            class="btn btn-outline-secondary btn-sm js-setting-file"
                                                                            data-input="{{ $set->key }}"
                                                                            aria-labelledby="label-{{ $set->key }}">
                                                                        Cambiar archivo
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if ($fields->isNotEmpty())
                                                <div class="settings-fields">
                                                    @foreach ($fields as $set)
                                                        <div class="settings-field {{ $set->isWideField() ? 'settings-field--wide' : '' }}">
                                                            <label class="settings-label" for="{{ $set->key }}">{{ $set->displayLabel() }}</label>
                                                            <input type="hidden" name="key[]" value="{{ $set->key }}">
                                                            @if ($set->type === 'textarea')
                                                                <textarea name="value[]"
                                                                          rows="3"
                                                                          class="form-control"
                                                                          id="{{ $set->key }}"
                                                                          placeholder="{{ $set->displayLabel() }}"
                                                                          @if (! $canUpdate) readonly @endif
                                                                          required>{{ $set->value }}</textarea>
                                                            @else
                                                                <input type="text"
                                                                       name="value[]"
                                                                       value="{{ $set->value }}"
                                                                       class="form-control"
                                                                       id="{{ $set->key }}"
                                                                       placeholder="{{ $set->displayLabel() }}"
                                                                       @if (! $canUpdate) readonly @endif
                                                                       required>
                                                            @endif
                                                            @if ($set->hint())
                                                                <p class="settings-hint">{{ $set->hint() }}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if ($items->isEmpty())
                                                <p class="mb-0 text-muted">No hay ajustes en esta sección.</p>
                                            @elseif ($canUpdate)
                                                <div class="settings-actions">
                                                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                                    @if ($categories->count() > 1)
                                                        <span class="settings-actions-note">Guardá esta pestaña antes de pasar a otra.</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var $page = $('.settings-page');
            var fmUrl = $page.data('fm-url');
            var inputId = '';

            function fmSetLink(url) {
                var value = (url.indexOf('http://') === 0 || url.indexOf('https://') === 0)
                    ? url
                    : url.substring(1);
                $(inputId).val(value);
                $(inputId + '-image').attr('src', value.indexOf('http') === 0 ? value : '{{ url('/') }}/' + value);
            }

            window.fmSetLink = fmSetLink;

            $(document).on('click', '.js-setting-file', function (event) {
                event.preventDefault();
                if (this.disabled) {
                    return;
                }
                inputId = '#' + $(this).data('input');
                window.open(fmUrl, 'fm', 'width=800,height=600');
            });

            var hash = window.location.hash;
            if (hash && $(hash).length) {
                $('.settings-page a[href="' + hash + '"]').tab('show');
            }

            $('.settings-page a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
                if (history.replaceState) {
                    history.replaceState(null, '', event.target.getAttribute('href'));
                }
            });
        });
    </script>
@endsection
