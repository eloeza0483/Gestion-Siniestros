<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailCancelacionRefacciones extends Mailable
{
    use Queueable, SerializesModels;

    public $siniestro;
    public $motivo;
    /** @var array Lista de strings describiendo los registros afectados */
    public $registrosAfectados;

    /**
     * @param  mixed  $siniestro
     * @param  string $motivo
     * @param  array  $registrosAfectados  Ej: ['Presupuesto: P-001', 'Vale: V-042', ...]
     */
    public function __construct($siniestro, string $motivo, array $registrosAfectados)
    {
        $this->siniestro         = $siniestro;
        $this->motivo            = $motivo;
        $this->registrosAfectados = $registrosAfectados;
    }

    public function build()
    {
        return $this->subject('Cancelación de Siniestro - Orden: ' . $this->siniestro->numero_orden)
            ->view('emails.cancelacion_refacciones');
    }
}
