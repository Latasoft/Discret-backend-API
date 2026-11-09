<?php

namespace App\Mail;

use App\Models\Cliente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientePasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Cliente $cliente,
        public string $resetUrl,
    ) {
        $this->cliente->loadMissing('motel');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablece tu contraseña DISCRET',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cliente-password-reset',
            with: [
                'nombreCliente' => trim($this->cliente->nombre.' '.$this->cliente->apellido),
                'resetUrl' => $this->resetUrl,
                'logoUrl' => rtrim((string) config('app.url'), '/').'/images/logo-discret.png',
                'motelNombre' => $this->cliente->motel?->nombre ?? 'DISCRET',
            ],
        );
    }
}
