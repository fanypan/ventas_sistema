<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Productos</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        .table th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 30px; }
        .summary-table { width: 50%; border-collapse: collapse; margin-top: 20px; float: right; }
        .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Catálogo de Productos</h2>
        <p>Fecha de Reporte: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Marca/Modelo</th>
                <th>Categoría</th>
                <th class="text-right">Stock</th>
                <th class="text-right">Costo</th>
                <th class="text-right">Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->code }}</td>
                <td>{{ $product->description }}</td>
                <td>{{ $product->brand }} {{ $product->model_name }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td class="text-right">{{ $product->stock }}</td>
                <td class="text-right">{{ money($product->cost, false) }}</td>
                <td class="text-right">{{ money($product->price, false) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="clear:both;"></div>

    <table class="summary-table">
        <tr>
            <th>Inversión Total</th>
            <td class="text-right">${{ number_format($inversion, 2) }}</td>
        </tr>
        <tr>
            <th>Proyección de Ventas</th>
            <td class="text-right">${{ number_format($proyeccion, 2) }}</td>
        </tr>
        <tr>
            <th>Utilidad Estimada</th>
            <td class="text-right">{{ number_format($utilidad, 2) }}%</td>
        </tr>
    </table>
</body>
</html>
