<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailNotificarCancelacionSiniestro extends Mailable
{
    use Queueable, SerializesModels;

    public $siniestro;
    public $motivo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($siniestro, $motivo)
    {
        $this->siniestro = $siniestro;
        $this->motivo = $motivo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Solicitud de Cancelación de Siniestro - Orden: ' . $this->siniestro->numero_orden)
            ->view('emails.notificar_cancelacion_siniestro');
    }
}
