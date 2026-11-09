<?php

namespace App\Mail;

use App\Models\EnvioProgramado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ValoracionPostSalidaMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public EnvioProgramado $envio)
    {
        $this->envio->loadMissing(['reserva.habitacion.tipoHabitacion', 'cliente', 'motel']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->envio->asunto ?: 'Cuentanos como fue tu experiencia en DISCRET',
        );
    }

    public function content(): Content
    {
        $reserva = $this->envio->reserva;
        $habitacion = $reserva?->habitacion;

        return new Content(
            view: 'emails.valoracion-post-salida',
            with: [
                'codigoReserva' => $reserva?->codigo_reserva ?? 'Reserva DISCRET',
                'nombreCliente' => $reserva?->nombre_cliente
                    ?? trim(($this->envio->cliente?->nombre ?? '').' '.($this->envio->cliente?->apellido ?? ''))
                    ?: 'Cliente',
                'habitacion' => $habitacion
                    ? trim(($habitacion->tipoHabitacion?->nombre ?? $habitacion->nombre).' - Hab. '.$habitacion->numero)
                    : 'Habitacion DISCRET',
                'fechaSalida' => $reserva?->fecha_salida?->format('d-m-Y H:i') ?? null,
                'linkValoracion' => $this->envio->mensaje,
                'motelNombre' => $this->envio->motel?->nombre ?? 'DISCRET',
            ],
        );
    }
}
