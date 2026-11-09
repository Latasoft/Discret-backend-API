<?php

namespace App\Console\Commands;

use App\Mail\ValoracionPostSalidaMail;
use App\Models\EnvioProgramado;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnviarCorreosProgramados extends Command
{
    protected $signature = 'discret:enviar-correos-programados
        {--limit=25 : Cantidad maxima de correos por ejecucion}
        {--force : Envia pendientes aunque programado_para sea futuro}';

    protected $description = 'Envia correos programados pendientes de DISCRET.';

    public function handle(): int
    {
        $limit = max((int) $this->option('limit'), 1);

        $envios = EnvioProgramado::query()
            ->with(['reserva.habitacion.tipoHabitacion', 'cliente', 'motel'])
            ->where('canal', 'correo')
            ->where('tipo', 'valoracion_post_salida')
            ->where('estado', 'pendiente')
            ->when(! $this->option('force'), fn ($query) => $query->where('programado_para', '<=', now()))
            ->oldest('programado_para')
            ->limit($limit)
            ->get();

        if ($envios->isEmpty()) {
            $this->info('No hay correos programados pendientes.');

            return self::SUCCESS;
        }

        $enviados = 0;
        $fallidos = 0;

        foreach ($envios as $envio) {
            try {
                Mail::to($envio->destinatario)->send(new ValoracionPostSalidaMail($envio));

                $envio->update([
                    'estado' => 'enviado',
                    'enviado_at' => now(),
                    'error' => null,
                ]);

                $enviados++;
                $this->info("Correo enviado a {$envio->destinatario}.");
            } catch (Throwable $exception) {
                $envio->update([
                    'estado' => 'fallido',
                    'error' => $exception->getMessage(),
                ]);

                $fallidos++;
                $this->error("No se pudo enviar a {$envio->destinatario}: {$exception->getMessage()}");
            }
        }

        $this->info("Proceso finalizado. Enviados: {$enviados}. Fallidos: {$fallidos}.");

        return $fallidos > 0 ? self::FAILURE : self::SUCCESS;
    }
}
