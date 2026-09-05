@foreach ($permissionGroups as $items)
    <div class="mb-3">
        <p class="mb-1 font-weight-bold">{{ \App\Support\TenantPermissionLabel::groupLabel($items->first()->name) }}</p>
        <div class="row mx-0">
            @foreach ($items as $p)
                <div class="icheck-primary col-md-3">
                    <input class="form-check-input permission" type="checkbox" name="permissions[]" id="{{ $p->id }}{{ $idSuffix ?? '' }}" value="{{ $p->name }}">
                    <label class="form-check-label {{ strtok($p->name, ' ') == 'delete' ? 'text-danger' : '' }}" for="{{ $p->id }}{{ $idSuffix ?? '' }}">
                        {{ \App\Support\TenantPermissionLabel::actionLabel($p->name) }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
