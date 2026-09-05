@if (! empty($metrics))
<table class="metrics">
    <tr>
        @foreach ($metrics as $metric)
            <td class="metric-card" width="{{ (int) (100 / count($metrics)) }}%">
                <div class="metric-label">{{ $metric['label'] }}</div>
                <div class="metric-value">{{ $metric['value'] }}</div>
            </td>
        @endforeach
    </tr>
</table>
@endif
