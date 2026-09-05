@php
    $context = $context ?? 'sale';
    $shortcuts = \App\Support\PosHelp::shortcutsFor($context);
    $guideUrl = route('help.index');
@endphp

<div class="modal fade" id="modalPosShortcuts" tabindex="-1" role="dialog" aria-labelledby="pos_shortcuts_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="pos_shortcuts_title">
                    <i class="fas fa-keyboard mr-2 text-primary"></i>Atajos de esta pantalla
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <ul class="list-group list-group-flush pos-shortcut-list">
                    @foreach ($shortcuts as $row)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $row['label'] }}</span>
                            <span class="pos-kbd-row ml-3">
                                @foreach ($row['keys'] as $key)
                                    <kbd class="pos-kbd">{{ $key }}</kbd>
                                @endforeach
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer bg-light justify-content-between">
                <small class="text-muted mb-0">No usan Ctrl ni F5 / F12, para no pelearse con el navegador.</small>
                <a href="{{ $guideUrl }}" class="btn btn-primary btn-sm font-weight-bold">Ver guía completa</a>
            </div>
        </div>
    </div>
</div>
