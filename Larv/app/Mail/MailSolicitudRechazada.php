<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailSolicitudRechazada extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->subject('Solicitud de eliminación de pieza rechazada')
            ->view('emails.solicitudRechazada')
            ->with([
                'numVale' => $this->datos['numVale'],
                'numeroParte' => $this->datos['numeroParte'],
                'descripcionPieza' => $this->datos['descripcionPieza'],
                'linkVale' => $this->datos['linkVale'],
                'usuarioRechazo' => $this->datos['usuarioRechazo'],
            ]);
    }
}
