<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lista de Precios</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        h1 { text-align: center; font-size: 16pt; margin-bottom: 5px; }
        .fecha { text-align: center; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 8px; text-align: left; }
        th { background: #333; color: #fff; font-size: 9pt; }
        td { font-size: 9pt; }
        .moneda { text-align: right; }
        .iva { text-align: center; }
    </style>
</head>
<body>
    <h1>FACTUS Esperanza Veliz</h1>
    <p class="fecha">Lista de Precios - Generado el {{ $fecha }}</p>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio Unitario</th>
                <th>Precio Mayor</th>
                <th>Cant. Mín. Mayor</th>
                <th>IVA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $p)
            @php
                $tasaValorPdf = $tasas[$p->fuente_tasa] ?? 1;
                $puBsPdf = $p->precio_unitario_usd * $tasaValorPdf;
                $pmBsPdf = $p->precio_mayor_usd * $tasaValorPdf;
            @endphp
            <tr>
                <td>{{ $p->nombre }}</td>
                <td class="moneda">Bs {{ number_format($puBsPdf, 2) }}<br><small>(${{ number_format($p->precio_unitario_usd, 2) }})</small></td>
                <td class="moneda">Bs {{ number_format($pmBsPdf, 2) }}<br><small>(${{ number_format($p->precio_mayor_usd, 2) }})</small></td>
                <td class="moneda">{{ $p->cantidad_minima_mayor }}</td>
                <td class="iva">{{ $p->tiene_iva ? 'Sí' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
