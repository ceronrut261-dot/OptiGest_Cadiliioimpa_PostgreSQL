<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        .encabezado { text-align: center; margin-bottom: 10px; }
        .encabezado img { width: 150px; }
        h1 { font-size: 15px; margin: 5px 0; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        .text-end { text-align: right; }
        .entrada { color: #146c2e; }
        .salida { color: #a3231f; }
    </style>
</head>
<body>
    <div class="encabezado">
        <img src="{{ public_path('images/logo-cadiliompa.png') }}">
    </div>
    <h1>Reporte de Movimientos de Inventario</h1>
    <p>Constru Fontanería Cadiliompa — Generado: {{ $fecha->format('d/m/Y H:i') }}
        @if($tipo) — Filtro: {{ ucfirst($tipo) }} @endif
    </p>

    <table>
        <thead>
            <tr><th>Fecha</th><th>Material</th><th>Tipo</th><th class="text-end">Cantidad</th><th class="text-end">Stock resultante</th><th>Motivo</th><th>Usuario</th></tr>
        </thead>
        <tbody>
            @foreach($movimientos as $mov)
                <tr>
                    <td>{{ $mov->fecha->format('d/m/Y H:i') }}</td>
                    <td>{{ $mov->material->nombre }}</td>
                    <td class="{{ $mov->tipo }}">{{ ucfirst($mov->tipo) }}</td>
                    <td class="text-end">{{ $mov->cantidad }}</td>
                    <td class="text-end">{{ $mov->stock_resultante }}</td>
                    <td>{{ $mov->motivo }}</td>
                    <td>{{ $mov->usuario->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>