<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailAlbaranAsignado extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->subject('Albaran asignado')
            ->view('emails.albaranAsignado')
            ->with([
                'numAlbaran' => $this->datos['numAlbaran'],
                'numVale' => $this->datos['numVale'],
                'linkAlbaran' => $this->datos['linkAlbaran'],
            ]);
    }
}
