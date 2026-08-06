<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h1>Cotización {{ $cotizacion->codigo }} — OptiGest</h1>
    <p>Constru Fontanería Cadiliompa</p>
    <p><strong>Cliente:</strong> {{ $cotizacion->cliente }} &nbsp; <strong>Fecha:</strong> {{ $cotizacion->fecha->format('d/m/Y') }}</p>

    <table>
        <thead><tr><th>Material</th><th class="text-end">Cantidad</th><th class="text-end">Precio unit.</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        @foreach($cotizacion->detalles as $detalle)
            <tr>
                <td>{{ $detalle->material->nombre }}</td>
                <td class="text-end">{{ $detalle->cantidad }}</td>
                <td class="text-end">Q{{ number_format($detalle->precio_unitario,2) }}</td>
                <td class="text-end">Q{{ number_format($detalle->subtotal,2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p class="text-end"><strong>Total: Q{{ number_format($cotizacion->total,2) }}</strong></p>
</body>
</html>
