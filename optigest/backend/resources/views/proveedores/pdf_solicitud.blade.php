<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Cotización</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #0d6efd; margin: 0; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #dee2e6; padding: 7px; text-align: left; }
        .items-table th { background-color: #f8f9fa; font-weight: bold; font-size: 11px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .total-box { float: right; width: 40%; border: 1px solid #dee2e6; padding: 10px; background-color: #f8f9fa; text-align: right; }
        .notes { margin-top: 30px; padding: 10px; border: 1px solid #eee; background-color: #fafafa; clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">SOLICITUD DE COTIZACIÓN</h1>
        <small>Fecha de emisión: {{ date('d/m/Y') }}</small>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>PROVEEDOR:</strong><br>
                <strong>Nombre:</strong> {{ $proveedor->nombre }}<br>
                <strong>Contacto:</strong> {{ $proveedor->nombre_contacto ?? 'N/A' }}<br>
                <strong>Teléfono:</strong> {{ $proveedor->telefono ?? 'N/A' }}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>SOLICITANTE:</strong><br>
                Departamento de Compras y Suministros<br>
                Guatemala
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 15%">Código</th>
                <th style="width: 40%">Descripción del Material</th>
                <th class="text-center" style="width: 15%">Cantidad</th>
                <th class="text-end" style="width: 15%">Precio Ref. (Q)</th>
                <th class="text-end" style="width: 15%">Subtotal (Q)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php
                    $cant = (float)$item['cantidad'];
                    $prec = (float)$item['precio'];
                    $subt = $cant * $prec;
                @endphp
                <tr>
                    <td>{{ $item['codigo'] }}</td>
                    <td>{{ $item['nombre'] }}</td>
                    <td class="text-center">{{ $cant }} {{ $item['unidad'] }}</td>
                    <td class="text-end">Q{{ number_format($prec, 2) }}</td>
                    <td class="text-end">Q{{ number_format($subt, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <strong>Total Estimado: </strong>
        <span style="font-size: 14px; font-weight: bold; color: #198754;">
            Q{{ number_format($totalGeneral, 2) }}
        </span>
    </div>

    @if(!empty($observaciones))
        <div class="notes">
            <strong>Instrucciones y condiciones solicitadas:</strong>
            <p style="margin: 5px 0 0 0;">{{ $observaciones }}</p>
        </div>
    @endif
</body>
</html>