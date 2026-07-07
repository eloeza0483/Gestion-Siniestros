<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailPedirModificacionParte extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->subject('Solicitud de modificacion de parte(s) en vale')
            ->view('emails.pedirModificacion')
            ->with([
                'mensaje' => $this->datos['mensaje'],
                'numVale' => $this->datos['numVale'],
                'linkVale' => $this->datos['linkVale'],
            ]);
    }
}
