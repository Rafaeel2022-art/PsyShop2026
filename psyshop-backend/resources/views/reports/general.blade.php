<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General - PsyShop</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #25213a;
            font-size: 12px;
            margin: 30px;
        }

        .header {
            background: #211936;
            color: white;
            padding: 25px;
            border-radius: 8px;
        }

        .brand {
            font-size: 27px;
            font-weight: bold;
            margin: 0;
        }

        .accent {
            color: #a78bfa;
        }

        .subtitle {
            color: #ddd6fe;
            margin-top: 8px;
        }

        .section-title {
            color: #5b21b6;
            font-size: 16px;
            margin-top: 28px;
            margin-bottom: 12px;
        }

        .cards {
            width: 100%;
            margin-top: 20px;
        }

        .card {
            width: 48%;
            display: inline-block;
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            padding: 14px;
            margin: 4px;
            box-sizing: border-box;
        }

        .card-label {
            color: #6b6480;
            font-size: 10px;
            text-transform: uppercase;
        }

        .card-value {
            color: #4c1d95;
            font-size: 21px;
            font-weight: bold;
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #211936;
            color: white;
            padding: 11px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #e5e1ed;
        }

        .footer {
            margin-top: 35px;
            padding-top: 12px;
            border-top: 1px solid #ddd6fe;
            color: #777;
            text-align: center;
            font-size: 10px;
        }

        .note {
            background: #f5f3ff;
            padding: 12px;
            margin-top: 20px;
            border-left: 4px solid #7c3aed;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1 class="brand">Psy<span class="accent">Shop</span></h1>
        <p class="subtitle">Reporte general del negocio</p>
        <p>Fecha de generación: {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>

    <h2 class="section-title">Resumen de ventas</h2>

    <div class="cards">
        <div class="card">
            <div class="card-label">Ingresos confirmados</div>
            <div class="card-value">${{ number_format($totalSales, 2) }}</div>
        </div>

        <div class="card">
            <div class="card-label">Pedidos completados</div>
            <div class="card-value">{{ $totalCompleted }}</div>
        </div>
    </div>

    <h2 class="section-title">Resumen de pedidos</h2>

    <div class="cards">
        <div class="card">
            <div class="card-label">Total de pedidos</div>
            <div class="card-value">{{ $totalOrders }}</div>
        </div>

        <div class="card">
            <div class="card-label">Pedidos pendientes</div>
            <div class="card-value">{{ $pendingOrders }}</div>
        </div>

        <div class="card">
            <div class="card-label">Pedidos cancelados</div>
            <div class="card-value">{{ $cancelledOrders }}</div>
        </div>

        <div class="card">
            <div class="card-label">Pedidos completados</div>
            <div class="card-value">{{ $totalCompleted }}</div>
        </div>
    </div>

    <h2 class="section-title">Resumen de inventario</h2>

    <div class="cards">
        <div class="card">
            <div class="card-label">Videojuegos registrados</div>
            <div class="card-value">{{ $totalGames }}</div>
        </div>

        <div class="card">
            <div class="card-label">Unidades disponibles</div>
            <div class="card-value">{{ $totalUnits }}</div>
        </div>

        <div class="card">
            <div class="card-label">Productos con stock bajo</div>
            <div class="card-value">{{ $lowStock }}</div>
        </div>

        <div class="card">
            <div class="card-label">Productos agotados</div>
            <div class="card-value">{{ $outOfStock }}</div>
        </div>
    </div>

    <div class="note">
        <strong>Nota:</strong>
        Los ingresos reflejan únicamente los pedidos completados.
        Los pedidos pendientes y cancelados no se consideran ingresos confirmados.
    </div>

    <div class="footer">
        PsyShop | Sistema de gestión de videojuegos<br>
        Reporte generado automáticamente.
    </div>

</body>
</html>
