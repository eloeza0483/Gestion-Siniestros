<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailCancelacionAutocar extends Mailable
{
    use Queueable, SerializesModels;

    public $siniestro;
    public $motivo;
    public $registrosAfectados;

    public function __construct($siniestro, $motivo, $registrosAfectados)
    {
        $this->siniestro          = $siniestro;
        $this->motivo             = $motivo;
        $this->registrosAfectados = $registrosAfectados;
    }

    public function build()
    {
        return $this->subject('Cancelación de Siniestro - Orden: ' . $this->siniestro->numero_orden)
            ->view('emails.cancelacion_autocar');
    }
}
