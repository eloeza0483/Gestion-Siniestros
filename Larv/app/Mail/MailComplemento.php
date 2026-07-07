<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailComplemento extends Mailable
{
    use Queueable, SerializesModels;

    public $mensaje;

    // Puedes pasar los datos como un array asociativo en el constructor y luego acceder a ellos en la vista del correo.
    // Por ejemplo, si en el controlador envías:
    // Mail::to('correo@dominio.com')->send(new MailComplemento(['descripcion' => '...', 'cantidad' => '...']));
    // Entonces aquí puedes recibir ese array y pasarlo a la vista markdown.

    public $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->subject('Complemento agregado a un vale')
            ->view('emails.correoComplementoAgregado')
            ->with([
                'descripcion' => $this->datos['descripcion'] ?? '',
                'cantidad' => $this->datos['cantidad'] ?? '',
                'cantidad' => $this->datos['linkVale']
            ]);
    }
}
