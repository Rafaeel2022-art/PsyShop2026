
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Reporte de inventario - PsyShop</title>

    <style>
        @page {
            margin: 35px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #252238;
        }

        .header {
            background-color: #211b35;
            color: white;
            padding: 22px;
        }

        .brand {
            font-size: 25px;
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
            border-collapse: separate;
            border-spacing: 6px;
            margin-top: 20px;
        }

        .summary td {
            background-color: #f3f0fa;
            padding: 12px;
            width: 25%;
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
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 12px;
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

        .stock-low {
            color: #b7791f;
            font-weight: bold;
        }

        .stock-out {
            color: #c53030;
            font-weight: bold;
        }

        .stock-ok {
            color: #27804a;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            color: #888;
            font-size: 9px;
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
        Reporte de inventario
    </h1>

    <div class="date">
        Generado:
        {{ $generatedAt->format('d/m/Y H:i') }}
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">
                    Videojuegos registrados
                </div>

                <div class="summary-value">
                    {{ $totalGames }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Unidades disponibles
                </div>

                <div class="summary-value">
                    {{ $totalUnits }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Stock bajo
                </div>

                <div class="summary-value">
                    {{ $lowStock }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Agotados
                </div>

                <div class="summary-value">
                    {{ $outOfStock }}
                </div>
            </td>
        </tr>
    </table>

    <h2 class="section-title">
        Detalle del inventario
    </h2>

    <table class="details">
        <thead>
            <tr>
                <th>Videojuego</th>
                <th>Plataforma</th>
                <th>Género</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($games as $game)
                <tr>
                    <td>{{ $game->title }}</td>

                    <td>{{ $game->platform }}</td>

                    <td>{{ $game->genre ?? 'Sin género' }}</td>

                    <td>
                        ${{ number_format($game->price, 2) }}
                    </td>

                    <td>{{ $game->stock }}</td>

                    <td>
                        @if ($game->stock == 0)
                            <span class="stock-out">
                                Agotado
                            </span>
                        @elseif ($game->stock <= 5)
                            <span class="stock-low">
                                Stock bajo
                            </span>
                        @else
                            <span class="stock-ok">
                                Disponible
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        No hay videojuegos registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        PsyShop - Reporte administrativo de inventario
        <br>
        Documento generado automáticamente.
    </div>

</body>
</html>
