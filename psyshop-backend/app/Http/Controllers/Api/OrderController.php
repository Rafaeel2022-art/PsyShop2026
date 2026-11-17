<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderShippedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Listar todos los pedidos para el administrador.
     * La ruta debe estar protegida por auth:sanctum y admin.
     */
   public function index()
{
    $orders = Order::with(['items.game', 'user'])
        ->latest('created_at')
        ->paginate(5);

    return response()->json($orders);
}

    /**
     * Mostrar únicamente los pedidos del cliente autenticado.
     */
   
public function myOrders(Request $request)
{
    $orders = $request->user()
        ->orders()
        ->with([
            'items.game',
            'statusHistory',
        ])
        ->latest()
        ->paginate(10);

    return response()->json($orders);
}


    /**
     * Registrar un pedido asociado al usuario autenticado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'delivery_address' => ['required', 'string', 'max:2000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.game_id' => [
                'required',
                'integer',
                'distinct',
                'exists:games,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        // El usuario proviene del token autenticado, nunca del cliente.
        $userId = $request->user()->id;

        $order = DB::transaction(function () use ($validated, $userId) {
            $subtotal = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $game = Game::whereKey($item['game_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$game || !$game->is_active) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Uno de los videojuegos ya no está disponible.',
                        ],
                    ]);
                }

                if ($game->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Stock insuficiente para {$game->title}.",
                        ],
                    ]);
                }

                $unitPrice = (float) $game->price;
                $lineSubtotal = round(
                    $unitPrice * $item['quantity'],
                    2
                );

                $subtotal += $lineSubtotal;

                $orderItems[] = [
                    'game' => $game,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $order = Order::create([
                'user_id' => $userId,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_address' => $validated['delivery_address'],
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'pending',
            ]);

            $order->statusHistory()->create([
    'status' => 'pending',
    'description' => 'El pedido fue recibido correctamente.',
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create([
                    'game_id' => $item['game']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['game']->decrement(
                    'stock',
                    $item['quantity']
                );
            }

            return $order->load('items.game');
        });

        // Enviar confirmación de compra al cliente.
try {
    Mail::to($order->customer_email)
        ->send(new OrderConfirmationMail($order));
} catch (\Throwable $e) {
    Log::error('Error enviando confirmación de compra de PsyShop', [
        'order_id' => $order->id,
        'error' => $e->getMessage(),
    ]);
}

        return response()->json([
            'message' => 'Pedido registrado correctamente.',
            'order' => $order,
        ], 201);
    }

    /**
     * Actualizar el estado de un pedido desde el panel administrativo.
     */
    
public function updateStatus(Request $request, Order $order)
{
    $validated = $request->validate([
        'status' => [
            'required',
            'string',
            'in:pending,confirmed,preparing,shipped,completed,cancelled',
        ],
    ]);

    $newStatus = $validated['status'];

    // Evitar registros duplicados si el estado no cambia.
    if ($order->status === $newStatus) {
        return response()->json([
            'message' => 'El pedido ya tiene ese estado.',
            'order' => $order->load('statusHistory'),
        ]);
    }

    DB::transaction(function () use ($order, $newStatus) {
        $order->update([
            'status' => $newStatus,
        ]);

        $descriptions = [
            'pending' => 'El pedido está pendiente de confirmación.',
            'confirmed' => 'El pedido ha sido confirmado.',
            'preparing' => 'El pedido está en preparación.',
            'shipped' => 'El pedido ha sido enviado.',
            'completed' => 'El pedido ha sido completado.',
            'cancelled' => 'El pedido ha sido cancelado.',
        ];

        $order->statusHistory()->create([
            'status' => $newStatus,
            'description' => $descriptions[$newStatus],
        ]);
    });

    // Enviar notificación cuando el pedido sea enviado.
if ($newStatus === 'shipped') {
    try {
        $orderForEmail = $order->fresh()->load('items.game');

        Mail::to($orderForEmail->customer_email)
            ->send(new OrderShippedMail($orderForEmail));

    } catch (\Throwable $e) {
        Log::error('Error enviando notificación de pedido enviado', [
            'order_id' => $order->id,
            'error' => $e->getMessage(),
        ]);
    }
}

    return response()->json([
        'message' => 'Estado del pedido actualizado correctamente.',
        'order' => $order->fresh()->load('statusHistory'),
    ]);
}

}
