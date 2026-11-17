<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class TestEmail extends Command
{
    protected $signature = 'psyshop:test-email {email}';

    protected $description = 'Enviar un correo de prueba de PsyShop';

    public function handle()
    {
        $email = $this->argument('email');

        try {
            Mail::to($email)->send(new TestMail());

            $this->info('Correo enviado correctamente a: ' . $email);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('No se pudo enviar el correo.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}