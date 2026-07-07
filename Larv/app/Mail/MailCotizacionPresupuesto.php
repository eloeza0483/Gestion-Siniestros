<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailCotizacionPresupuesto extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->subject('Cotización de presupuesto')
            ->view('emails.correoCotizacion')
            ->with([
                'numeroPresupuesto' => $this->datos['numeroPresupuesto'],
                'linkPresupuesto' => $this->datos['linkPresupuesto'],
            ]);
    }
}
