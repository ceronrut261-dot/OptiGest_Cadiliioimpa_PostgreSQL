
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #222;
        }

        .encabezado {
            text-align: center;
            margin-bottom: 10px;
        }

        .encabezado img {
            width: 150px;
        }

        h1 {
            font-size: 15px;
            text-align: center;
            margin: 5px 0 15px;
            text-transform: uppercase;
        }

        .datos {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .datos td {
            padding: 5px;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        .datos .etiqueta {
            font-weight: bold;
            width: 20%;
        }

        .estado {
            padding: 8px;
            border: 1px solid #999;
            margin-bottom: 12px;
            line-height: 1.6;
        }

        table.detalle {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.detalle th,
        table.detalle td {
            border: 1px solid #999;
            padding: 5px 3px;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        table.detalle th {
            background: #f0f0f0;
            text-align: center;
            font-size: 9px;
        }

        table.detalle td {
            font-size: 10px;
        }

        table.detalle th:nth-child(1),
        table.detalle td:nth-child(1) {
            width: 28%;
            text-align: left;
        }

        table.detalle th:nth-child(2),
        table.detalle td:nth-child(2) {
            width: 14%;
        }

        table.detalle th:nth-child(3),
        table.detalle td:nth-child(3) {
            width: 14%;
        }

        table.detalle th:nth-child(4),
        table.detalle td:nth-child(4) {
            width: 10%;
        }

        table.detalle th:nth-child(5),
        table.detalle td:nth-child(5) {
            width: 15%;
        }

        table.detalle th:nth-child(6),
        table.detalle td:nth-child(6) {
            width: 19%;
        }

        .text-end {
            text-align: right;
        }

        .caja {
            border: 1px solid #999;
            padding: 8px;
            margin-bottom: 12px;
            min-height: 20px;
            overflow-wrap: break-word;
        }

        .firmas {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .firmas td {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #333;
            font-size: 10px;
            width: 33.33%;
        }

        .firmas-fila td {
            border-top: none;
            padding: 0 15px;
        }

        .sin-borde {
            border: none !important;
        }
    </style>
</head>

<body>

    @php
        $confirmada = $salida->estado_entrega === 'confirmada';

        $totalSolicitado = 0;
        $totalRecibido = 0;
        $totalPendiente = 0;

        foreach ($salida->detalles as $detalle) {
            $solicitada = (int) $detalle->cantidad;

            $recibida = $confirmada
                ? (int) $detalle->cantidad_entregada
                : 0;

            $pendiente = max(0, $solicitada - $recibida);

            $totalSolicitado += $solicitada;
            $totalRecibido += $recibida;
            $totalPendiente += $pendiente;
        }

        if (!$confirmada) {
            $estadoTexto = 'Pendiente de confirmación';
        } elseif ($totalRecibido === 0) {
            $estadoTexto = 'No se entregaron materiales';
        } elseif ($totalPendiente > 0) {
            $estadoTexto = 'Entrega parcial';
        } else {
            $estadoTexto = 'Entrega completa';
        }
    @endphp

    <div class="encabezado">
        <img src="{{ public_path('images/logo-cadiliompa.png') }}">
    </div>

    <h1>Salida de Materiales — {{ $salida->codigo }}</h1>

    <table class="datos">
        <tr>
            <td class="etiqueta">Personal que entrega:</td>
            <td>{{ $salida->usuario?->name ?? 'No disponible' }}</td>

            <td class="etiqueta">Fecha:</td>
            <td>{{ $salida->fecha?->format('d/m/Y H:i') ?? '—' }}</td>
        </tr>

        <tr>
            <td class="etiqueta">Nombre del proyecto:</td>
            <td>{{ $salida->proyecto ?: '—' }}</td>

            <td class="etiqueta">Persona que recibe:</td>
            <td>{{ $salida->persona_recibe ?: '—' }}</td>
        </tr>

        @if($salida->ticket)
            <tr>
                <td class="etiqueta">Ticket relacionado:</td>
                <td colspan="3">{{ $salida->ticket->codigo }}</td>
            </tr>
        @endif
    </table>

    <div class="estado">
        <strong>Estado de la entrega:</strong>
        {{ $estadoTexto }}

        @if($confirmada && $salida->confirmado_en)
            <br>
            <strong>Fecha de confirmación:</strong>
            {{ $salida->confirmado_en->format('d/m/Y H:i') }}
        @endif

        @if($confirmada && $salida->usuarioConfirmacion)
            <br>
            <strong>Confirmada por:</strong>
            {{ $salida->usuarioConfirmacion->name }}
        @endif

        @if($confirmada)
            <br>
            <strong>Total de unidades solicitadas:</strong>
            {{ $totalSolicitado }}

            <br>
            <strong>Total de unidades recibidas:</strong>
            {{ $totalRecibido }}

            <br>
            <strong>Total de unidades pendientes de entregar:</strong>
            {{ $totalPendiente }}
        @endif
    </div>

    <table class="detalle">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cantidad solicitada</th>
                <th>Cantidad recibida</th>
                <th>Pendiente</th>
                <th>Valor unitario</th>
                <th>Total solicitado</th>
            </tr>
        </thead>

        <tbody>
            @forelse($salida->detalles as $detalle)
                @php
                    $solicitada = (int) $detalle->cantidad;

                    $recibida = $confirmada
                        ? (int) $detalle->cantidad_entregada
                        : null;

                    $pendiente = $confirmada
                        ? max(0, $solicitada - $recibida)
                        : null;
                @endphp

                <tr>
                    <td>{{ $detalle->descripcion }}</td>

                    <td class="text-end">
                        {{ $solicitada }}
                    </td>

                    <td class="text-end">
                        {{ $recibida !== null ? $recibida : 'Por confirmar' }}
                    </td>

                    <td class="text-end">
                        {{ $pendiente !== null ? $pendiente : '—' }}
                    </td>

                    <td class="text-end">
                        Q{{ number_format((float) $detalle->valor_unitario, 2) }}
                    </td>

                    <td class="text-end">
                        Q{{ number_format((float) $detalle->total, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">
                        No hay materiales registrados en este vale.
                    </td>
                </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <td colspan="5" class="text-end">
                    <strong>Total solicitado</strong>
                </td>

                <td class="text-end">
                    <strong>
                        Q{{ number_format((float) $salida->total, 2) }}
                    </strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <p><strong>Observaciones del vale:</strong></p>

    <div class="caja">
        {{ $salida->observaciones ?: '—' }}
    </div>

    @if($confirmada)
        <p><strong>Observaciones de la entrega:</strong></p>

        <div class="caja">
            {{ $salida->observaciones_entrega ?: '—' }}
        </div>
    @endif

    <p><strong>¿Falta algo por comprar?</strong></p>

    <div class="caja">
        {{ $salida->falta_comprar ?: 'No especificado.' }}
    </div>

    <table class="firmas">
        <tr class="firmas-fila">
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <tr>
            <td>Firma de entrega</td>
            <td>Firma de recibido de proyecto</td>
            <td>Firma de recibido</td>
        </tr>
    </table>

</body>
</html>
