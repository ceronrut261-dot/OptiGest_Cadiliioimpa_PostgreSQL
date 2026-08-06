<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h1 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        .text-end { text-align: right; }
        .danger { background: #fdecea; }
    </style>
</head>
<body>
    <h1>Reporte de Inventario — OptiGest</h1>
    <p>Constru Fontanería Cadiliompa — Generado: {{ $fecha->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Proveedor</th><th class="text-end">Precio</th><th class="text-end">Stock</th></tr>
        </thead>
        <tbody>
            @foreach($materiales as $material)
                <tr class="{{ $material->bajo_stock ? 'danger' : '' }}">
                    <td>{{ $material->codigo }}</td>
                    <td>{{ $material->nombre }}</td>
                    <td>{{ $material->categoria }}</td>
                    <td>{{ $material->proveedor?->nombre ?? '-' }}</td>
                    <td class="text-end">Q{{ number_format($material->precio, 2) }}</td>
                    <td class="text-end">{{ $material->stock }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
