<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\VehiculoInfo;
use Illuminate\Http\Request;
use App\Http\Traits\SiniestroTrait;

class SeguimientoTrabajosController extends Controller
{
    use SiniestroTrait;

    public function getSeguimientoTrabajosView($perfil = null)
    {
        $nombre_perfil = $this->formatPerfilToPermisos($perfil ?? '');
        $this->authorize("viewSeguimientoTrabajo{$nombre_perfil}", VehiculoInfo::class);

        return view('seguimientoTrabajos');
    }

    public function getSeguimientoTrabajos(Request $request, $perfil = null)
    {
        $nombre_perfil = $this->formatPerfilToPermisos($perfil ?? '');
        $this->authorize("viewSeguimientoTrabajo{$nombre_perfil}", VehiculoInfo::class);

        $query = VehiculoInfo::with('siniestros');

        if ($perfil !== 'refacciones') {
            $id_perfil_query = $this->getIdPerfil($perfil);
            if ($id_perfil_query) {
                $query->whereHas('siniestros', function ($q) use ($id_perfil_query) {
                    $q->where('perfil_id', $id_perfil_query);
                });
            }
        }

        return $query->get();
    }
}
