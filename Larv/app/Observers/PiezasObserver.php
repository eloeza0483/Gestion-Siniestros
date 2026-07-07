<?php

namespace App\Observers;

use App\Models\Piezas;
use App\Models\UpdatePresupuestoLog;
use Illuminate\Support\Facades\Auth;

class PiezasObserver
{

    public function created(Piezas $pieza): void
    {
        if (!$pieza->id_presupuesto) return;

        UpdatePresupuestoLog::create([
            'id_presupuesto' => $pieza->id_presupuesto,
            'id_pieza'       => $pieza->id,
            'id_usuario'     => Auth::id(),
            'accion'         => 'agregar_pieza',
            'valor_nuevo'    => json_encode([
                'numero_parte' => $pieza->numero_parte,
                'descripcion'  => $pieza->descripcion,
                'cantidad'     => $pieza->numero_pzas_presupuesto,
            ]),
        ]);
    }


    public function updating(Piezas $pieza): void
    {
        if (!$pieza->id_presupuesto) return;

        $camposAuditar = [
            'numero_parte',
            'descripcion',
            'descripcion_w32',
            'numero_pzas_presupuesto',
            'importe_unitario',
            'importe_total',
            'existencia',
            'tiempoentrega',
        ];

        foreach ($camposAuditar as $campo) {
            $valorOriginal = $pieza->getOriginal($campo);
            $valorNuevo    = $pieza->$campo;

            if (
                $pieza->isDirty($campo) &&
                $valorOriginal !== null &&
                $valorOriginal !== '' &&
                $valorOriginal != 0 &&
                (string) $valorNuevo !== (string) $valorOriginal
            ) {
                UpdatePresupuestoLog::create([
                    'id_presupuesto'   => $pieza->id_presupuesto,
                    'id_pieza'         => $pieza->id,
                    'id_usuario'       => Auth::id(),
                    'accion'           => 'modificar_pieza',
                    'campo_modificado' => $campo,
                    'valor_anterior'   => $valorOriginal,
                    'valor_nuevo'      => $valorNuevo,
                ]);
            }
        }
    }
}
