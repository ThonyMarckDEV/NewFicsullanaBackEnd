<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Cancelación</title>
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
        .label { display: table-cell; font-weight: normal; white-space: nowrap; }
        .value { display: table-cell; text-align: right; word-break: break-all; }
        .footer { margin-top: 5px; text-align: center; font-size: 7px; padding-top: 3px; border-top: 1px dashed #000; color: #444; }
        .centered { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="centered">
            <div class="title">NOMBRE DE TU EMPRESA</div>
        </div>
        {{-- Título cambiado --}}
        <div class="subtitle bold">COMPROBANTE DE CANCELACIÓN TOTAL</div>
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
                <span class="value">{{ $prestamo->id }}</span>
            </div>
            <div class="item">
                <span class="label">Cliente:</span>
                <span class="value">{{ $prestamo->cliente->datos->nombre ?? '' }} {{ $prestamo->cliente->datos->apellidoPaterno ?? '' }}</span>
            </div>
            <div class="item">
                <span class="label">DNI:</span>
                <span class="value">{{ $prestamo->cliente->datos->dni ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="section">
            {{-- Sección modificada para mostrar las cuotas canceladas --}}
            <div class="item">
                <span class="label">Concepto:</span>
                <span class="value bold">Cancelación Total</span>
            </div>
            <div class="item">
                <span class="label">Cuotas Canceladas:</span>
                <span class="value">{{ $cuotasCanceladas->pluck('numero_cuota')->implode(', ') }}</span>
            </div>
            <div class="item">
                <span class="label bold">MONTO TOTAL PAGADO:</span>
                <span class="value bold">S/ {{ number_format($pago->monto_pagado, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p class="centered bold">¡Préstamo cancelado en su totalidad!</p>
        <p class="centered">Gracias por su preferencia.</p>
    </div>
</body>
</html>