@php
    $items = collect($items ?? []);
    $max = $max ?? ($items->max('value') ?: 1);
    $color = $color ?? '#4f46e5';
@endphp

@if ($items->isNotEmpty())
    @if (! empty($title))
        <div class="section-title">{{ $title }}</div>
    @endif
    <table class="chart">
        @foreach ($items as $item)
            @php
                $value = (float) ($item['value'] ?? 0);
                $pct = $max > 0 ? max(2, (int) round(($value / $max) * 100)) : 0;
                $barColor = $item['color'] ?? $color;
            @endphp
            <tr>
                <td class="chart-label">{{ $item['label'] }}</td>
                <td class="chart-bar-cell">
                    <table class="chart-bar-track" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="{{ $pct }}%" class="chart-bar-fill" style="background-color: {{ $barColor }};">&nbsp;</td>
                            <td></td>
                        </tr>
                    </table>
                </td>
                <td class="chart-value">{{ $item['display'] ?? $value }}</td>
            </tr>
        @endforeach
    </table>
@endif
