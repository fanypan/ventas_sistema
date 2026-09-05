<table class="table table-sm table-hover mb-0 help-shortcut-table">
    <thead class="thead-light">
        <tr>
            <th style="width:42%">Atajo</th>
            <th>Qué hace</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>
                    <span class="pos-kbd-row">
                        @foreach ($row['keys'] as $key)
                            <kbd class="pos-kbd">{{ $key }}</kbd>
                        @endforeach
                    </span>
                </td>
                <td>{{ $row['label'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
