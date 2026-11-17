<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Mostrar los clientes registrados.
     */
    public function index()
    {
        $clients = User::query()
            ->select('id', 'name', 'email', 'created_at')
            ->where('is_admin', false)
            ->withCount('orders')
            ->latest()
            ->paginate(10);

        return response()->json($clients);
    }

    /**
     * Mostrar información e historial de compras de un cliente.
     */
    public function show(User $user)
    {
        abort_if($user->is_admin, 404);

        $user->makeHidden(['is_admin']);
        $user->loadCount('orders');

        $orders = $user->orders()
            ->with(['items.game', 'statusHistory'])
            ->latest()
            ->paginate(5);

        return response()->json([
            'client' => $user,
            'orders' => $orders,
        ]);
    }
}
