<div class="platform-empty">
    <h2>{{ $title }}</h2>
    <p>{{ $body }}</p>
    @if (! empty($actionUrl) && ! empty($actionLabel))
        <a class="btn btn-primary" href="{{ $actionUrl }}">{{ $actionLabel }}</a>
    @endif
</div>
