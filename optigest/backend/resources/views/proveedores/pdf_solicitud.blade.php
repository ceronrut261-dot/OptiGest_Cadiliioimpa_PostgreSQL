<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Cotización</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        .header {
            margin-bottom: 25px;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
            margin: 0;
        }
        .date {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            vertical-align: top;
            width: 50%;
        }
        .info-box {
            line-height: 1.5;
        }
        .info-box strong {
            color: #111;
        }
        .badge-multi {
            display: inline-block;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 3px 6px;
            font-size: 10px;
            border-radius: 3px;
            color: #0d6efd;
            font-weight: bold;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        table.items-table th {
            background-color: #f1f4f9;
            color: #333;
            border: 1px solid #dee2e6;
            padding: 7px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.items-table td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            font-size: 10px;
        }
        table.items-table tr.fila-externa {
            background-color: #fffdf5; /* Leve tono para identificar insumos de otro proveedor */
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-monospace {
            font-family: "Courier New", Courier, monospace;
        }
        .fw-bold {
            font-weight: bold;
        }
        .total-box {
            width: 40%;
            float: right;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            padding: 8px 12px;
            text-align: right;
        }
        .total-title {
            font-size: 11px;
            font-weight: bold;
        }
        .total-amount {
            font-size: 14px;
            font-weight: bold;
            color: #198754;
            font-family: "Courier New", Courier, monospace;
        }
        .observaciones-box {
            clear: both;
            margin-top: 25px;
            padding: 10px;
            border: 1px dashed #bbb;
            background-color: #fafafa;
            border-radius: 4px;
        }
        .observaciones-box h5 {
            margin: 0 0 5px 0;
            font-size: 11px;
            color: #444;
        }
        .prov-tag {
            font-weight: bold;
            color: #0d6efd;
        }
        .prov-tag.externo {
            color: #d63384;
        }
    </style>
</head>
<body>

    @php
        // Comprobar si hay insumos de varios proveedores
        $proveedoresEnItems = collect($items)->pluck('proveedor')->filter()->unique();
        $esMultiProveedor = $proveedoresEnItems->count() > 1;
    @endphp

    <div class="header">
        <h1 class="title">
            {{ $esMultiProveedor ? 'Solicitud de Cotización (Multi-Proveedor)' : 'Solicitud de Cotización' }}
        </h1>
        <div class="date">Fecha de emisión: {{ now()->format('d/m/Y') }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="info-box">
                    <strong>PROVEEDOR PRINCIPAL:</strong><br>
                    <strong>Nombre:</strong> {{ $proveedor->nombre }}<br>
                    <strong>Contacto:</strong> {{ $proveedor->nombre_contacto ?? 'No especificado' }}<br>
                    <strong>Teléfono:</strong> {{ $proveedor->telefono ?? 'No especificado' }}
                    @if($esMultiProveedor)
                        <br><span class="badge-multi">Incluye insumos de otros proveedores sugeridos</span>
                    @endif
                </div>
            </td>
            <td>
                <div class="info-box text-end">
                    <strong>SOLICITANTE:</strong><br>
                    Departamento de Compras y Suministros<br>
                    Constru Fontanería Cadiliompa<br>
                    Guatemala
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 14%;">Código</th>
                <th style="width: 32%;">Descripción del Material</th>
                <th style="width: 18%;">Proveedor Sugerido</th>
                <th style="width: 12%;" class="text-center">Cantidad</th>
                <th style="width: 12%;" class="text-end">Precio Ref. (Q)</th>
                <th style="width: 12%;" class="text-end">Subtotal (Q)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php
                    $provNombre = $item['proveedor'] ?? $proveedor->nombre;
                    $esDeOtroProveedor = ($provNombre !== $proveedor->nombre);
                    $subtotalItem = ((float)$item['cantidad']) * ((float)$item['precio']);
                @endphp
                <tr class="{{ $esDeOtroProveedor ? 'fila-externa' : '' }}">
                    <td><code>{{ $item['codigo'] }}</code></td>
                    <td>{{ $item['nombre'] }}</td>
                    <td>
                        <span class="prov-tag {{ $esDeOtroProveedor ? 'externo' : '' }}">
                            {{ $provNombre }}
                        </span>
                    </td>
                    <td class="text-center">{{ $item['cantidad'] }} {{ $item['unidad'] ?? 'unidad' }}</td>
                    <td class="text-end font-monospace">Q{{ number_format((float)$item['precio'], 2) }}</td>
                    <td class="text-end font-monospace fw-bold">Q{{ number_format($subtotalItem, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <span class="total-title">Total Estimado: </span>
        <span class="total-amount">Q{{ number_format($totalGeneral, 2) }}</span>
    </div>

    @if(!empty($observaciones))
        <div class="observaciones-box">
            <h5>Instrucciones o notas adicionales:</h5>
            <p style="margin: 0; line-height: 1.4;">{{ $observaciones }}</p>
        </div>
    @endif

</body>
</html>