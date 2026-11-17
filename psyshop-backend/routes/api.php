<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ClientController;

// Autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

// Catálogo público para los clientes
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);

// Pedidos protegidos: el cliente debe iniciar sesión
Route::middleware('auth:sanctum')->group(function () {

    // Pedidos del cliente
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);

    // Gestión del perfil del cliente
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile/password', [AuthController::class, 'updatePassword']);

});

// Operaciones administrativas protegidas
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        // Gestión de clientes
    Route::get('/admin/clients', [ClientController::class, 'index']);
    Route::get('/admin/clients/{user}', [ClientController::class, 'show']);
    // Reportes PDF
Route::get('/admin/reports/sales', [
    ReportController::class,
    'sales'
]);
Route::get('/admin/reports/inventory', [
    ReportController::class,
    'inventory'
]);
Route::get('/admin/reports/orders', [
    ReportController::class,
    'orders'
]);Route::get('/admin/reports/general', [
    ReportController::class,
    'general'
]);
    Route::get('/admin/games/count', function () {
    return response()->json([
        'total' => \App\Models\Game::count(),
    ]);
});

    // Administración de videojuegos
    Route::get('/admin/games', [GameController::class, 'adminIndex']);
    Route::post('/games', [GameController::class, 'store']);
    Route::put('/games/{game}', [GameController::class, 'update']);
    Route::patch('/games/{game}', [GameController::class, 'update']);
    Route::delete('/games/{game}', [GameController::class, 'destroy']);

    // Administración de pedidos
    Route::get('/admin/orders', [OrderController::class, 'index']);
    Route::patch('/admin/orders/{order}/status',[OrderController::class, 'updateStatus']
    );

    // Usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
