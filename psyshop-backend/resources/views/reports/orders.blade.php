
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Reporte de pedidos - PsyShop</title>

    <style>
        @page {
            margin: 30px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #252238;
        }

        .header {
            background-color: #211b35;
            color: white;
            padding: 20px;
        }

        .brand {
            font-size: 24px;
            font-weight: bold;
        }

        .brand span {
            color: #a78bfa;
        }

        .subtitle {
            color: #d8d0ed;
            margin-top: 5px;
        }

        .report-title {
            margin-top: 22px;
            font-size: 19px;
            color: #211b35;
        }

        .info {
            color: #777;
            margin-top: 5px;
        }

        .summary {
            width: 100%;
            border-collapse: separate;
            border-spacing: 7px;
            margin-top: 18px;
        }

        .summary td {
            background-color: #f3f0fa;
            padding: 12px;
            width: 50%;
        }

        .summary-label {
            color: #777;
            font-size: 9px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #6d45c5;
            margin-top: 7px;
        }

        .section-title {
            font-size: 13px;
            margin-top: 22px;
            margin-bottom: 10px;
        }

        table.details {
            width: 100%;
            border-collapse: collapse;
        }

        table.details th {
            background-color: #211b35;
            color: white;
            padding: 9px 5px;
            text-align: left;
        }

        table.details td {
            padding: 8px 5px;
            border-bottom: 1px solid #e5e1ee;
        }

        table.details tr:nth-child(even) {
            background-color: #f8f7fb;
        }

        .pending {
            color: #b7791f;
            font-weight: bold;
        }

        .completed {
            color: #27804a;
            font-weight: bold;
        }

        .cancelled {
            color: #c53030;
            font-weight: bold;
        }

        .empty {
            padding: 20px;
            text-align: center;
            color: #777;
        }

        .footer {
            margin-top: 25px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            color: #888;
            font-size: 8px;
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
        Reporte de pedidos
    </h1>

    <div class="info">
        Período:
        {{ $startDate ?? 'Inicio' }}
        al
        {{ $endDate ?? 'Actualidad' }}
    </div>

    <div class="info">
        Filtro de estado:
        {{ $statusFilter ?? 'Todos los estados' }}
    </div>

    <div class="info">
        Generado:
        {{ $generatedAt->format('d/m/Y H:i') }}
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">
                    Total de pedidos
                </div>

                <div class="summary-value">
                    {{ $totalOrders }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Importe de pedidos
                </div>

                <div class="summary-value">
                    ${{ number_format($totalAmount, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <h2 class="section-title">
        Detalle de pedidos
    </h2>

    <table class="details">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Correo</th>
                <th>Fecha</th>
                <th>Productos</th>
                <th>Estado</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>

                    <td>{{ $order->customer_name }}</td>

                    <td>{{ $order->customer_email }}</td>

                    <td>
                        {{ $order->created_at->format('d/m/Y') }}
                    </td>

                    <td>
                        {{ $order->items->sum('quantity') }}
                    </td>

                    <td>
                        @if ($order->status === 'completed')
                            <span class="completed">Completado</span>
                        @elseif ($order->status === 'cancelled')
                            <span class="cancelled">Cancelado</span>
                        @else
                            <span class="pending">Pendiente</span>
                        @endif
                    </td>

                    <td>
                        ${{ number_format($order->total, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">
                        No existen pedidos para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        PsyShop - Reporte administrativo de pedidos
        <br>
        Documento generado automáticamente.
    </div>

</body>
</html>
