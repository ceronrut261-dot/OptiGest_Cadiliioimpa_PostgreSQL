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
    <p><strong>Cliente:</strong> {{ $cotizacion->cliente->nombre }} &nbsp; <strong>Fecha:</strong> {{ $cotizacion->fecha->format('d/m/Y') }}</p>

    <table>
        <thead><tr><th>Concepto</th><th class="text-end">Cantidad</th><th class="text-end">Precio unit.</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        @foreach($cotizacion->detalles as $detalle)
            <tr>
                <td>{{ $detalle->material->nombre }}</td>
                <td class="text-end">{{ $detalle->cantidad }}</td>
                <td class="text-end">Q{{ number_format($detalle->precio_unitario,2) }}</td>
                <td class="text-end">Q{{ number_format($detalle->subtotal,2) }}</td>
            </tr>
        @endforeach
        @foreach($cotizacion->servicios as $servicio)
            <tr>
                <td>{{ $servicio->descripcion }} (mano de obra)</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($servicio->cantidad, 2), '0'), '.') }}</td>
                <td class="text-end">Q{{ number_format($servicio->precio_unitario,2) }}</td>
                <td class="text-end">Q{{ number_format($servicio->subtotal,2) }}</td>
            </tr>
        @endforeach
        @foreach($cotizacion->gastos->where('cobrar_al_cliente', true) as $gasto)
            <tr>
                <td>{{ $gasto->descripcion }} (gasto adicional)</td>
                <td class="text-end">1</td>
                <td class="text-end">Q{{ number_format($gasto->monto,2) }}</td>
                <td class="text-end">Q{{ number_format($gasto->monto,2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p class="text-end">Subtotal: Q{{ number_format($cotizacion->subtotal,2) }}</p>
    @if($cotizacion->iva_aplicado)
        <p class="text-end">IVA: Q{{ number_format($cotizacion->iva_monto,2) }}</p>
    @endif
    <p class="text-end"><strong>Total: Q{{ number_format($cotizacion->total,2) }}</strong></p>
</body>
</html>
