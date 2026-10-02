<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 13px; margin: 18px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; }
        .text-end { text-align: right; }
        .muted { color: #666; }
        .comprobante { page-break-inside: avoid; margin-bottom: 18px; }
        .comprobante img { max-width: 100%; max-height: 520px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>Reporte de gastos y comprobantes — Ticket {{ $ticket->codigo }}</h1>
    <p class="muted">Constru Fontanería Cadiliompa · Generado el {{ now()->format('d/m/Y H:i') }}</p>
    <p>
        <strong>Cliente:</strong> {{ $ticket->cliente->nombre ?? '—' }}<br>
        <strong>Descripción del trabajo:</strong> {{ $ticket->descripcion }}<br>
        <strong>Equipo:</strong> {{ $ticket->tecnicos->pluck('name')->implode(', ') ?: '—' }}
    </p>

    <table>
        <thead>
            <tr><th>#</th><th>Fecha</th><th>Detalle</th><th>Documento</th><th class="text-end">Monto</th><th>Cobro</th><th>Comprobante</th></tr>
        </thead>
        <tbody>
        @forelse($gastos as $i => $gasto)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                <td>{{ $gasto->descripcion }}
                    @if($gasto->comercio)<br><span class="muted">{{ $gasto->comercio }}</span>@endif
                    <br><span class="muted">Registró: {{ $gasto->registrador->name ?? '—' }}</span>
                </td>
                <td>{{ $tiposDoc[$gasto->tipo_documento] ?? $gasto->tipo_documento }}
                    @if($gasto->numero_documento)<br>{{ $gasto->numero_documento }}@endif
                </td>
                <td class="text-end">Q{{ number_format($gasto->monto, 2) }}</td>
                <td>
                    @if(! $gasto->cobrar_al_cliente) Gasto de la empresa
                    @elseif($gasto->cotizacion) {{ $gasto->cotizacion->codigo }}
                    @else Pendiente de cotizar @endif
                </td>
                <td>
                    @if(isset($imagenes[$gasto->id])) Foto adjunta (ver abajo)
                    @elseif($gasto->archivo_path) Archivo adjunto: {{ $gasto->archivo_nombre }}
                    @else <strong>SIN COMPROBANTE</strong> @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">Este ticket no tiene gastos registrados.</td></tr>
        @endforelse
        </tbody>
        @if($gastos->isNotEmpty())
        <tfoot>
            <tr><th colspan="4" class="text-end">Total gastos</th><th class="text-end">Q{{ number_format($gastos->sum('monto'), 2) }}</th><th colspan="2"></th></tr>
            <tr><th colspan="4" class="text-end">Cobrable al cliente</th><th class="text-end">Q{{ number_format($gastos->where('cobrar_al_cliente', true)->sum('monto'), 2) }}</th><th colspan="2"></th></tr>
        </tfoot>
        @endif
    </table>

    @if(count($imagenes))
        <h2>Comprobantes adjuntos</h2>
        @foreach($gastos as $i => $gasto)
            @if(isset($imagenes[$gasto->id]))
                <div class="comprobante">
                    <p><strong>#{{ $i + 1 }} — {{ $gasto->descripcion }}</strong> · Q{{ number_format($gasto->monto, 2) }} · {{ $gasto->fecha_gasto->format('d/m/Y') }}</p>
                    <img src="{{ $imagenes[$gasto->id] }}" alt="Comprobante {{ $i + 1 }}">
                </div>
            @endif
        @endforeach
    @endif

    @if($gastos->filter(fn ($g) => $g->archivo_path && ! isset($imagenes[$g->id]))->isNotEmpty())
        <p class="muted">Los archivos PDF u otros formatos adjuntos no se incrustan en este reporte; se descargan desde el ticket en OptiGest.</p>
    @endif
</body>
</html>
