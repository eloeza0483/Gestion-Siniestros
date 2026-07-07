<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailEntradaAsignada extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->subject('Entrada asignada')
            ->view('emails.entradaAsignada')
            ->with([
                'numEntrada' => $this->datos['numEntrada'],
                'numVale' => $this->datos['numVale'],
                'linkEntrada' => $this->datos['linkEntrada'],
            ]);
    }
}
