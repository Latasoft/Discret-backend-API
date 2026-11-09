<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaConfirmadaMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Reserva $reserva)
    {
        $this->reserva->loadMissing(['cliente', 'habitacion.tipoHabitacion', 'motel']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comprobante de reserva DISCRET - '.$this->reserva->codigo_reserva,
        );
    }

    public function content(): Content
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173/discret'), '/');
        $tipoHabitacion = $this->tipoHabitacionLabel();
        $qrPayload = implode("\n", array_filter([
            'DISCRET - COMPROBANTE DE RESERVA',
            'ID reserva: '.$this->reserva->codigo_reserva,
            $this->reserva->qr_token ? 'Token QR: '.$this->reserva->qr_token : null,
            'Cliente: '.$this->nombreCliente(),
            'Tipo habitacion: '.$tipoHabitacion,
            'Entrada: '.$this->reserva->fecha_entrada?->format('d-m-Y H:i'),
            'Salida: '.$this->reserva->fecha_salida?->format('d-m-Y H:i'),
        ]));

        return new Content(
            view: 'emails.reserva-confirmada',
            with: [
                'codigoReserva' => $this->reserva->codigo_reserva,
                'nombreCliente' => $this->nombreCliente(),
                'habitacion' => $tipoHabitacion,
                'fechaEntrada' => $this->reserva->fecha_entrada?->format('d-m-Y H:i'),
                'fechaSalida' => $this->reserva->fecha_salida?->format('d-m-Y H:i'),
                'cantidadPersonas' => $this->reserva->cantidad_personas,
                'tipoPago' => $this->reserva->tipo_pago ?: 'Pendiente',
                'qrToken' => $this->reserva->qr_token,
                'qrUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=14&data='.urlencode($qrPayload),
                'logoUrl' => rtrim((string) config('app.url'), '/').'/images/logo-discret.png',
                'misReservasUrl' => "{$frontendUrl}/mi-cuenta",
                'motelNombre' => $this->reserva->motel?->nombre ?? 'DISCRET',
            ],
        );
    }

    private function nombreCliente(): string
    {
        return $this->reserva->nombre_cliente
            ?? trim(($this->reserva->cliente?->nombre ?? '').' '.($this->reserva->cliente?->apellido ?? ''))
            ?: 'Cliente';
    }

    private function tipoHabitacionLabel(): string
    {
        $habitacion = $this->reserva->habitacion;
        $tipo = $habitacion?->tipoHabitacion?->nombre
            ?? $habitacion?->nombre
            ?? 'Habitacion DISCRET';

        return trim($tipo);
    }
}
