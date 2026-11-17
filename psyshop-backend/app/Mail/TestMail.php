<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->subject('Prueba de correo - PsyShop')
            ->html('<h1>¡PsyShop está conectado!</h1><p>La configuración de correo funciona correctamente.</p>');
    }
}