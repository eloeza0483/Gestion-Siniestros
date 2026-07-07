<?php

namespace App\Http\Controllers;

use App\Models\Perfile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;

    public $id_perfil;
    public function __construct()
    {

        $this->id_perfil = $this->getIdPerfil(request()->perfil);
    }

    public function getIdPerfil($nombre_perfil)
    {

        $np = $this->formatearNombrePerfil($nombre_perfil);

        return Perfile::where('nombre', $np)->first()->id ?? false;
    }

    public function formatearNombrePerfil($nombre_perfil, $mayuscula = true)
    {
        $np =  str_replace('_', ' ', $nombre_perfil);
        return  $mayuscula ? strtoupper($np) : $np;
    }
}
