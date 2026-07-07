<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailNotificarCierreSiniestroAutocares extends Mailable
{
    use Queueable, SerializesModels;

    public $siniestro;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($siniestro)
    {
        $this->siniestro = $siniestro;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Cierre de Siniestro (Autocares) - Orden: ' . $this->siniestro->numero_orden)
            ->view('emails.cierre_siniestro_autocares');
    }
}
