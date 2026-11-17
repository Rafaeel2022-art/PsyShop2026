
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Reporte de ventas - PsyShop</title>

    <style>
        @page {
            margin: 35px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #252238;
        }

        .header {
            background-color: #211b35;
            color: white;
            padding: 22px;
            border-radius: 8px;
        }

        .brand {
            font-size: 25px;
            font-weight: bold;
            color: #ffffff;
        }

        .brand span {
            color: #a78bfa;
        }

        .subtitle {
            color: #d8d0ed;
            margin-top: 5px;
        }

        .report-title {
            margin-top: 25px;
            font-size: 20px;
            color: #211b35;
        }

        .date {
            color: #777;
            margin-top: 5px;
        }

        .summary {
            width: 100%;
            margin-top: 22px;
            border-collapse: separate;
            border-spacing: 8px;
        }

        .summary td {
            background-color: #f3f0fa;
            padding: 15px;
            width: 33.33%;
            border-radius: 6px;
        }

        .summary-label {
            color: #777;
            font-size: 10px;
        }

        .summary-value {
            font-size: 19px;
            font-weight: bold;
            color: #6d45c5;
            margin-top: 8px;
        }

        .section-title {
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 12px;
            color: #211b35;
        }

        table.details {
            width: 100%;
            border-collapse: collapse;
        }

        table.details th {
            background-color: #211b35;
            color: white;
            padding: 10px 6px;
            text-align: left;
        }

        table.details td {
            padding: 9px 6px;
            border-bottom: 1px solid #e5e1ee;
        }

        table.details tr:nth-child(even) {
            background-color: #f8f7fb;
        }

        .empty {
            padding: 20px;
            text-align: center;
            color: #777;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            color: #888;
            font-size: 9px;
        }

        .total {
            font-weight: bold;
            color: #6d45c5;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="brand">
            Psy<span>Shop</span>
        </div>

        <div class="subtitle">
            Sistema de administración de videojuegos
        </div>
    </div>

    <h1 class="report-title">
        Reporte de ventas
    </h1>

    <div class="date">
        Período:
        {{ $startDate ?? 'Inicio' }}
        al
        {{ $endDate ?? 'Actualidad' }}
    </div>

    <div class="date">
        Generado:
        {{ $generatedAt->format('d/m/Y H:i') }}
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">
                    Ventas confirmadas
                </div>

                <div class="summary-value">
                    ${{ number_format($totalSales, 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Pedidos completados
                </div>

                <div class="summary-value">
                    {{ $totalOrders }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Unidades vendidas
                </div>

                <div class="summary-value">
                    {{ $totalUnits }}
                </div>
            </td>
        </tr>
    </table>

    <h2 class="section-title">
        Detalle de ventas
    </h2>

    <table class="details">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Unidades</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>

                    <td>
                        {{ $order->customer_name }}
                    </td>

                    <td>
                        {{ $order->created_at->format('d/m/Y') }}
                    </td>

                    <td>
                        {{ $order->items->sum('quantity') }}
                    </td>

                    <td class="total">
                        ${{ number_format($order->total, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">
                        No existen ventas completadas en este período.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        PsyShop - Reporte administrativo de ventas
        <br>
        Documento generado automáticamente.
    </div>

</body>
</html>
