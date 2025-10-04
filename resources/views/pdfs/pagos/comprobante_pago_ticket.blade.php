<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago</title>
    <style>
        @page { size: 58mm auto; margin: 1mm; }
        * { font-family: 'Helvetica', sans-serif; box-sizing: border-box; }
        body { width: 56mm; max-width: 56mm; padding: 1mm; font-size: 8px; line-height: 1.3; color: #111; }
        .header { text-align: center; margin-bottom: 5px; border-bottom: 1px dashed #000; padding-bottom: 3px; }
        .title { font-size: 10px; font-weight: bold; margin: 2px 0; }
        .subtitle { font-size: 8px; margin: 0; }
        .bold { font-weight: bold; }
        .section { margin-bottom: 5px; border-bottom: 1px dashed #000; padding-bottom: 3px; }
        .item { display: table; width: 100%; margin-bottom: 2px; }
        .label { display: table-cell; font-weight: normal; white-space: nowrap; } /* Quitado el bold por defecto */
        .value { display: table-cell; text-align: right; word-break: break-all; }
        .footer { margin-top: 5px; text-align: center; font-size: 7px; padding-top: 3px; border-top: 1px dashed #000; color: #444; }
        .centered { text-align: center; }
        .total-line { border-top: 1px dashed #999; padding-top: 2px; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="centered">
            <div class="title">NOMBRE DE TU EMPRESA</div>
        </div>
        <div class="subtitle bold">COMPROBANTE DE PAGO</div>
    </div>

    <div class="content">
        <div class="section">
            <div class="item">
                <span class="label">Fecha y Hora:</span>
                <span class="value">{{ \Carbon\Carbon::parse($pago->created_at)->format('d/m/Y H:i A') }}</span>
            </div>
            <div class="item">
                <span class="label">N° Operación:</span>
                <span class="value">{{ $pago->numero_operacion }}</span>
            </div>
            <div class="item">
                <span class="label">Cajero:</span>
                <span class="value">{{ $pago->usuario->datos->nombre ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="section">
            <div class="item">
                <span class="label">Préstamo ID:</span>
                <span class="value">{{ $pago->cuota->prestamo->id }}</span>
            </div>
            <div class="item">
                <span class="label">Cliente:</span>
                <span class="value">{{ $pago->cuota->prestamo->cliente->datos->nombre ?? '' }} {{ $pago->cuota->prestamo->cliente->datos->apellidoPaterno ?? '' }}</span>
            </div>
            <div class="item">
                <span class="label">DNI:</span>
                <span class="value">{{ $pago->cuota->prestamo->cliente->datos->dni ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="section">
            <div class="item">
                <span class="label">Concepto:</span>
                <span class="value bold">Cuota {{ $pago->cuota->numero_cuota }} de {{ $pago->cuota->prestamo->cuotas }}</span>
            </div>
            <div class="item">
                <span class="label">Monto de Cuota:</span>
                <span class="value">S/ {{ number_format($pago->cuota->monto, 2) }}</span>
            </div>
            <div class="item">
                <span class="label">Mora Pagada:</span>
                <span class="value">S/ {{ number_format($pago->cuota->cargo_mora, 2) }}</span>
            </div>
            <div class="item">
                <span class="label">Crédito Anterior:</span>
                <span class="value">- S/ {{ number_format($pago->cuota->excedente_anterior, 2) }}</span>
            </div>

            {{-- --- INICIO DE LA CORRECCIÓN --- --}}
            @php
                $totalAPagar = ($pago->cuota->monto + $pago->cuota->cargo_mora) - $pago->cuota->excedente_anterior;
            @endphp
            <div class="item total-line">
                <span class="label bold">Total a Pagar:</span>
                <span class="value bold">S/ {{ number_format(max(0, $totalAPagar), 2) }}</span>
            </div>
            {{-- --- FIN DE LA CORRECCIÓN --- --}}

            <div class="item">
                <span class="label bold">MONTO PAGADO:</span>
                <span class="value bold">S/ {{ number_format($pago->monto_pagado, 2) }}</span>
            </div>
            
            @if ($pago->excedente > 0)
            <div class="item">
                <span class="label bold">Saldo a Favor (Nuevo):</span>
                <span class="value bold">S/ {{ number_format($pago->excedente, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <p class="centered bold">¡Gracias por su pago!</p>
        <p class="centered">Conserve este comprobante.</p>
    </div>
</body>
</html>