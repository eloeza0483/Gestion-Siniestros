<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Siniestro;
use App\Models\VehiculoInfo;
use Illuminate\Http\Request;
use App\Http\Traits\SiniestroTrait;

class ProcesoVehiculoController extends Controller
{
    use SiniestroTrait;
    public function getProcesosVehiculosView($perfil = null)
    {
        $nombre_perfil = $this->formatPerfilToPermisos($perfil ?? '');
        $this->authorize("viewProcesosVehiculo{$nombre_perfil}", VehiculoInfo::class);

        return view('procesosVehiculos');
    }

    public function getProcesosVehiculos(Request $request, $perfil = null)
    {
        $nombre_perfil = $this->formatPerfilToPermisos($perfil ?? '');
        $this->authorize("viewProcesosVehiculo{$nombre_perfil}", VehiculoInfo::class);

        $query = VehiculoInfo::with('siniestros');

        if ($perfil !== 'refacciones') {
            $id_perfil_query = $this->getIdPerfil($perfil);
            if ($id_perfil_query) {
                $query->whereHas('siniestros', function ($q) use ($id_perfil_query) {
                    $q->where('perfil_id', $id_perfil_query);
                });
            }
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        return $query->get();
    }

    public function cambiarEstadoProceso(Request $request, $perfil = null, $id)
    {
        $nombre_perfil = $this->formatPerfilToPermisos($perfil ?? '');
        $this->authorize("viewProcesosVehiculo{$nombre_perfil}", VehiculoInfo::class);

        $estado = $request->input('estado');
        $adelanto = $request->input('adelanto', 0);
        $motivoPausa = $request->input('motivo_pausa', null);
        $vehiculoInfo = VehiculoInfo::find($id);

        if (!$vehiculoInfo) {
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'Vehiculo no encontrado',
                'icon' => 'error'
            ], 404);
        }

        if ($estado == 'EnProceso') {
            $iniciarResponse = $this->iniciarProceso($vehiculoInfo, $adelanto);
            if ($iniciarResponse) {
                return $iniciarResponse;
            }
        } elseif ($estado == 'Finalizado') {
            $this->finalizarProceso($vehiculoInfo);
        } elseif ($estado == 'Pausado') {
            $vehiculoInfo->motivo_pausa = $motivoPausa;
        }

        $vehiculoInfo->estado = $estado;
        $vehiculoInfo->save();

        return response()->json([
            'success' => true,
            'title' => 'Exito',
            'message' => 'Estado cambiado correctamente',
            'icon' => 'success'
        ]);
    }

    private function iniciarProceso(VehiculoInfo $vehiculoInfo, $adelanto)
    {
        $siniestros = Siniestro::where('id_vehiculo', $vehiculoInfo->id)->withPresupuestosAndVales()->get();

        if ($siniestros->isEmpty()) {
            return response()->json([
                'success' => false,
                'title' => 'Atencion',
                'message' => 'No existen siniestros asociados al vehiculo, no se puede iniciar el proceso.',
                'icon' => 'warning'
            ]);
        }

        if ($adelanto != 1) {
            foreach ($siniestros as $siniestro) {
                if ($siniestro->estado !== 'Completado') {
                    return response()->json([
                        'success' => false,
                        'requireAdelanto' => true,
                        'title' => 'Atencion',
                        'message' => 'Aun no se ha recibido el 100% de las piezas, deseas adelantarlo?',
                        'icon' => 'warning'
                    ]);
                }
            }
        }

        $vehiculoInfo->fecha_inicio = now();
        if ($adelanto == 1) {
            $vehiculoInfo->adelanto = 1;
        }

        return null;
    }

    private function finalizarProceso(VehiculoInfo $vehiculoInfo)
    {
        $vehiculoInfo->fecha_fin = now();
    }
}
