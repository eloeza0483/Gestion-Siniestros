<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\Aseguradora;
use App\Models\Cliente;
use App\Models\Marca;
use App\Models\Perfile;
use App\Models\PerfilUsuario;
use App\Models\Taller;
use App\Models\User;
use App\Models\Vehiculo;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class SiniestroService
{
    use AuthorizesRequests;
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    /**
     * 
     * @internal metodo para obtener la informacion de Marcas, Aseguradoras y Vehiculos, para dar de alta un siniestro
     * @return array
     * 
     */
    public function getMAV($perfil)
    {
        $clientes = [];
        $aseguradoras = [];
        if ($perfil == 'REFACCIONES') {
            $clientes = Cliente::where('activo', '1')->get();
        } else {
            $aseguradoras = Aseguradora::orderBy('nombre')->where('activo', '1')->get();
        }
        // $clientes = Cliente::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $vehiculos = Vehiculo::orderBy('nombre')->get();
        if ($perfil == "REFACCIONES") {
            $talleres = Taller::whereNotIn('nombre', ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO'])->get();
        } else {
            $talleres = Taller::where('nombre', $perfil)->get();
        }
        return compact('clientes', 'marcas', 'vehiculos', 'talleres', 'aseguradoras');
    }

    public function acceso_perfiles()
    {
        $pu = PerfilUsuario::where('user_id', Auth::id());

        if (!$pu->exists()) return (object) ['success' => false, 'message' => 'No cuentas con acceso a ningun perfil para acceder al apartado de siniestros'];

        $ids_perfiles = explode(",", $pu->first()->perfil_id ?? '');

        return (object) ['success' => true, 'perfiles' => Perfile::whereIn('id', $ids_perfiles)->get() ?? []];
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
