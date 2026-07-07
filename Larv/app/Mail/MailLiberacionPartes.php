<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailLiberacionPartes extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->subject('Partes liberadas')
            ->view('emails.liberacionPartes')
            ->with([
                'numEntrada' => $this->datos['numEntrada'],
                'numParte' => $this->datos['numParte'],
                'cantidad' => $this->datos['cantidad'],
                'linkEntrada' => $this->datos['linkEntrada'],
            ]);
    }
}
