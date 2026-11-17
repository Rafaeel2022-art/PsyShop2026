<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $query = Order::with('items.game')
            ->where('status', 'completed');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $totalSales = $orders->sum('total');

        $totalUnits = $orders->sum(function ($order) {
            return $order->items->sum('quantity');
        });

        $pdf = Pdf::loadView('reports.sales', [
            'orders' => $orders,
            'totalSales' => $totalSales,
            'totalOrders' => $orders->count(),
            'totalUnits' => $totalUnits,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'generatedAt' => now(),
        ]);

        return $pdf->download('psyshop-reporte-ventas.pdf');
    }
    
    public function inventory()
    {
        $games = \App\Models\Game::orderBy('title')->get();

        $totalGames = $games->count();

        $totalUnits = $games->sum('stock');

        $lowStock = $games->filter(function ($game) {
            return $game->stock >= 1 && $game->stock <= 5;
        })->count();

        $outOfStock = $games->where('stock', 0)->count();

        $pdf = Pdf::loadView('reports.inventory', [
            'games' => $games,
            'totalGames' => $totalGames,
            'totalUnits' => $totalUnits,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'generatedAt' => now(),
        ]);

        return $pdf->download('psyshop-reporte-inventario.pdf');
    }
    
    public function orders(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:pending,completed,cancelled'],
        ]);

        $query = \App\Models\Order::with('items.game')
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        $pdf = Pdf::loadView('reports.orders', [
            'orders' => $orders,
            'totalOrders' => $orders->count(),
            'totalAmount' => $orders->sum('total'),
            'generatedAt' => now(),
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'statusFilter' => $request->status,
        ]);

        return $pdf->download('psyshop-reporte-pedidos.pdf');
    }
public function general()
{
    // Obtener todos los pedidos
    $orders = \App\Models\Order::with('items.game')->get();

    // Obtener pedidos completados
    $completedOrders = $orders->where('status', 'completed');

    // Obtener todos los videojuegos
    $games = \App\Models\Game::orderBy('title')->get();

    // Indicadores de ventas
    $totalSales = $completedOrders->sum('total');
    $totalCompleted = $completedOrders->count();

    // Indicadores de pedidos
    $totalOrders = $orders->count();
    $pendingOrders = $orders->where('status', 'pending')->count();
    $cancelledOrders = $orders->where('status', 'cancelled')->count();

    // Indicadores de inventario
    $totalGames = $games->count();
    $totalUnits = $games->sum('stock');

    $lowStock = $games->filter(function ($game) {
        return $game->stock >= 1 && $game->stock <= 5;
    })->count();

    $outOfStock = $games->where('stock', 0)->count();

    // Generar PDF
    $pdf = Pdf::loadView('reports.general', [
        'totalSales' => $totalSales,
        'totalCompleted' => $totalCompleted,
        'totalOrders' => $totalOrders,
        'pendingOrders' => $pendingOrders,
        'cancelledOrders' => $cancelledOrders,
        'totalGames' => $totalGames,
        'totalUnits' => $totalUnits,
        'lowStock' => $lowStock,
        'outOfStock' => $outOfStock,
        'generatedAt' => now(),
    ]);

    return $pdf->download('psyshop-reporte-general.pdf');
}

}
