<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cronograma de Pagos - Préstamo #{{ $prestamo->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .section { margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        .section h2 { margin-top: 0; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .details-grid { display: block; } /* Usamos block para simplicidad en PDF */
        .details-grid p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f7f7f7; font-weight: bold; }
        .text-right { text-align: right; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cronograma de Pagos</h1>
            <p>Préstamo ID: #{{ $prestamo->id }}</p>
        </div>

        <div class="section">
            <h2>Datos del Préstamo</h2>
            <div class="details-grid">
                <p><strong>Monto:</strong> S/ {{ number_format($prestamo->monto, 2) }}</p>
                <p><strong>Tasa de Interés:</strong> {{ $prestamo->interes * 100 }}%</p>
                <p><strong>N° de Cuotas:</strong> {{ $prestamo->cuotas }}</p>
                <p><strong>Total a Pagar:</strong> S/ {{ number_format($prestamo->total, 2) }}</p>
                <p><strong>Fecha de Inicio:</strong> {{ \Carbon\Carbon::parse($prestamo->fecha_inicio)->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="section">
            <h2>Información del Cliente y Asesor</h2>
            <div class="details-grid">
                <p><strong>Cliente:</strong> {{ $prestamo->cliente->datos->nombre ?? '' }} {{ $prestamo->cliente->datos->apellidoPaterno ?? '' }}</p>
                <p><strong>DNI Cliente:</strong> {{ $prestamo->cliente->datos->dni ?? 'N/A' }}</p>
                <p><strong>Asesor:</strong> {{ $prestamo->asesor->datos->nombre ?? '' }} {{ $prestamo->asesor->datos->apellidoPaterno ?? '' }}</p>
            </div>
        </div>
        
        <div class="section">
            <h2>Cuotas del Préstamo</h2>
            <table>
                <thead>
                    <tr>
                        <th>N° Cuota</th>
                        <th>Fecha de Vencimiento</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prestamo->cuota as $c)
                    <tr>
                        <td>{{ $c->numero_cuota }}</td>
                        <td>{{ \Carbon\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}</td>
                        <td class="text-right">S/ {{ number_format($c->monto, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Documento generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>