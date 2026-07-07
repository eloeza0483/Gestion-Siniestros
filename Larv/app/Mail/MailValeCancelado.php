<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailValeCancelado extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->subject('Vale Cancelado - ' . $this->datos['numVale'])
            ->view('emails.valeCancelado')
            ->with([
                'numeroPresupuesto' => $this->datos['numeroPresupuesto'],
                'numVale' => $this->datos['numVale'],
                'linkVale' => $this->datos['linkVale'],
                'motivo' => $this->datos['motivo']
            ]);
    }
}
