<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compra confirmada - PsyShop</title>
</head>
<body style="margin:0; padding:30px 10px; background:#09090f; font-family:Arial,sans-serif; color:#ffffff;">

    <div style="max-width:600px; margin:auto; background:#151522; border-radius:14px; overflow:hidden;">

        <div style="padding:30px; text-align:center; background:#21183b;">
            <h1 style="margin:0; color:#a78bfa; font-size:32px;">PsyShop</h1>
            <p style="color:#d1d5db;">Tu tienda gaming</p>
        </div>

        <div style="padding:30px;">

            <h2 style="color:#a78bfa;">¡Gracias por tu compra, {{ $order->customer_name }}!</h2>

            <p>Hemos recibido correctamente tu pedido.</p>

            <p><strong>Número de pedido:</strong> #{{ $order->id }}</p>

            <p><strong>Estado:</strong> Pendiente</p>

            <h3 style="color:#a78bfa;">Resumen de tu compra</h3>

            @foreach($order->items as $item)
    <div style="padding:15px 0; border-bottom:1px solid #333; overflow:hidden;">

        @if($item->game && $item->game->image_url)
            <img
                src="{{ $item->game->image_url }}"
                alt="{{ $item->game->title }}"
                width="110"
                style="width:110px; height:140px; object-fit:cover; border-radius:8px; display:block; margin-bottom:12px;"
            >
        @endif

        <strong style="color:#ffffff; font-size:16px;">
            {{ $item->game->title ?? 'Videojuego' }}
        </strong>

        <p style="margin:5px 0; color:#d1d5db;">
            Cantidad: {{ $item->quantity }}
        </p>

        <p style="margin:5px 0; color:#d1d5db;">
            Precio: ${{ number_format($item->unit_price, 2) }}
        </p>

        <p style="margin:5px 0; color:#a78bfa;">
            Subtotal: ${{ number_format($item->subtotal, 2) }}
        </p>

    </div>
@endforeach

            <h2 style="color:#a78bfa;">
                Total: ${{ number_format($order->total, 2) }}
            </h2>

            <h3 style="color:#a78bfa;">Datos de entrega</h3>

            <p>{{ $order->customer_name }}</p>
            <p>{{ $order->delivery_address }}</p>
            <p>{{ $order->customer_phone }}</p>

            <p style="margin-top:30px;">
                Te notificaremos cuando tu pedido sea enviado.
            </p>

            <p>¡Gracias por confiar en PsyShop!</p>

        </div>

        <div style="padding:20px; text-align:center; background:#21183b; color:#aaa;">
            <p>© {{ date('Y') }} PsyShop - Tu tienda gaming</p>
        </div>

    </div>

</body>
</html>