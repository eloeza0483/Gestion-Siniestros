<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\VehiculoInfo;
use Illuminate\Support\Facades\DB;

class Siniestro extends Model
{
    use HasFactory;
    protected $table = 'siniestros';
    protected $fillable = ['numero_siniestro', 'numero_orden', 'id_vehiculo', 'id_usuario_registro', 'udn', 'estado', 'id_factura', 'motivo_cancelacion', 'siniestro_externo', 'perfil_id', 'id_cliente'];

    public function vehiculoInfo()
    {
        return $this->belongsTo(VehiculoInfo::class, 'id_vehiculo', 'id');
    }

    public function vehiculo_info()
    {
        return $this->belongsTo(VehiculoInfo::class, 'id_vehiculo', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id');
    }


    public function scopeWithPresupuestosAndVales($query)
    {
        return $query->with(['vehiculoInfo', 'cliente'])
            ->select('*')
            ->selectRaw("(SELECT count(*) FROM presupuestos P WHERE P.id_siniestro = siniestros.id ) as num_presupuestos")
            ->selectRaw("(SELECT count(*) FROM presupuestos P WHERE P.id_siniestro = siniestros.id AND P.estado = 'Cotizado') as presupuestos_cotizados")
            ->selectRaw("(SELECT proveedor FROM presupuestos P WHERE P.id_siniestro = siniestros.id ORDER BY P.id DESC LIMIT 1) as proveedor")
            ->selectRaw("(SELECT count(*) FROM presupuestos P INNER JOIN vales V ON V.id_presupuesto = P.id WHERE P.id_siniestro = siniestros.id AND NOT V.estado IN ('Cancelado')) as num_vales")
            ->selectRaw("(SELECT count(*) FROM presupuestos P INNER JOIN vales V ON V.id_presupuesto = P.id INNER JOIN entradas E ON E.id_vale = V.id WHERE P.id_siniestro = siniestros.id AND NOT V.estado IN ('Cancelado')) as num_entradas")
            ->selectRaw("(
                SELECT COALESCE(SUM(PV.cantidad), 0)
                FROM piezas_vales PV
                INNER JOIN vales V ON PV.id_vale = V.id
                INNER JOIN piezas P ON PV.id_pieza = P.id
                INNER JOIN presupuestos PR ON V.id_presupuesto = PR.id
                WHERE PR.id_siniestro = siniestros.id
                AND V.estado != 'Cancelado'
                AND PV.activo = 1
            ) as pzs_autorizadas")
            ->selectRaw("(
                SELECT COALESCE(SUM(PE.cantidad), 0)
                FROM piezas_entradas PE
                INNER JOIN entradas E ON PE.id_entrada = E.id
                INNER JOIN vales V ON E.id_vale = V.id
                WHERE V.id = E.id_vale
                AND E.id_siniestro = siniestros.id
                AND E.estado != 'Cancelado'
                AND PE.activo = 1
            ) as pzs_recibidas")
            // pzs_faltantes: para AUTOCAR solo entradas, para otros solo albaranes
            ->selectRaw("(
                COALESCE((
                    SELECT SUM(PV.cantidad)
                    FROM piezas_vales PV
                    INNER JOIN vales V ON PV.id_vale = V.id
                    INNER JOIN presupuestos PR ON V.id_presupuesto = PR.id
                    WHERE PR.id_siniestro = siniestros.id
                    AND V.estado != 'Cancelado'
                    AND PV.activo = 1
                ), 0)
                -
                CASE
                    WHEN (SELECT VI.taller FROM vehiculo_info VI WHERE VI.id = siniestros.id_vehiculo)
                        IN ('AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO') THEN
                        -- AUTOCAR: solo entradas cuentan como surtido definitivo
                        COALESCE((
                            SELECT SUM(PE.cantidad)
                            FROM piezas_entradas PE
                            INNER JOIN entradas E ON PE.id_entrada = E.id
                            WHERE E.id_siniestro = siniestros.id
                            AND E.estado != 'Cancelado'
                            AND PE.activo = 1
                        ), 0)
                    ELSE
                        -- Otros: solo albaranes cuentan
                        COALESCE((
                            SELECT SUM(PA.cantidad)
                            FROM piezas_albaranes PA
                            INNER JOIN albaranes A ON PA.id_albaran = A.id
                            WHERE A.id_siniestro = siniestros.id
                            AND A.estado != 'Cancelado'
                            AND PA.activo = 1
                        ), 0)
                END
            ) as pzs_faltantes")
            // Porcentaje: CHEVROLET usa albaranes, otros proveedores usan entradas
            // ->selectRaw("(
            //     SELECT 
            //         CASE 
            //             WHEN (
            //                 SELECT COALESCE(SUM(PV.cantidad), 0)
            //                 FROM piezas_vales PV
            //                 INNER JOIN vales V ON PV.id_vale = V.id
            //                 INNER JOIN presupuestos PR ON V.id_presupuesto = PR.id
            //                 WHERE PR.id_siniestro = siniestros.id
            //                 AND V.estado != 'Cancelado'
            //                 AND PV.activo = 1
            //             ) = 0 THEN 0
            //             WHEN EXISTS (
            //                 SELECT 1 FROM presupuestos P2 
            //                 WHERE P2.id_siniestro = siniestros.id 
            //                 AND P2.proveedor = 'CHEVROLET'
            //             ) THEN
            //                 -- CHEVROLET: porcentaje basado en albaranes
            //                 ROUND(
            //                     COALESCE((
            //                         SELECT SUM(PA.cantidad)
            //                         FROM piezas_albaranes PA
            //                         INNER JOIN albaranes A ON PA.id_albaran = A.id
            //                         WHERE A.id_siniestro = siniestros.id
            //                         AND A.estado != 'Cancelado'
            //                         AND PA.activo = 1
            //                     ), 0) * 100.0 /
            //                     (
            //                         SELECT COALESCE(SUM(PV.cantidad), 0)
            //                         FROM piezas_vales PV
            //                         INNER JOIN vales V ON PV.id_vale = V.id
            //                         INNER JOIN presupuestos PR ON V.id_presupuesto = PR.id
            //                         WHERE PR.id_siniestro = siniestros.id
            //                         AND V.estado != 'Cancelado'
            //                         AND PV.activo = 1
            //                     )
            //                 , 2)
            //             ELSE
            //                 -- Otros proveedores: porcentaje basado en entradas
            //                 ROUND(
            //                     COALESCE((
            //                         SELECT SUM(PE.cantidad)
            //                         FROM piezas_entradas PE
            //                         INNER JOIN entradas E ON PE.id_entrada = E.id
            //                         WHERE E.id_siniestro = siniestros.id
            //                         AND E.estado != 'Cancelado'
            //                         AND PE.activo = 1
            //                     ), 0) * 100.0 /
            //                     (
            //                         SELECT COALESCE(SUM(PV.cantidad), 0)
            //                         FROM piezas_vales PV
            //                         INNER JOIN vales V ON PV.id_vale = V.id
            //                         INNER JOIN presupuestos PR ON V.id_presupuesto = PR.id
            //                         WHERE PR.id_siniestro = siniestros.id
            //                         AND V.estado != 'Cancelado'
            //                         AND PV.activo = 1
            //                     )
            //                 , 2)
            //         END
            // ) as porcentaje_pzs_recibidas")
            // porcentaje_total_vehiculo: para AUTOCAR usa entradas, para otros usa albaranes
            ->selectRaw("(
                SELECT CASE
                    WHEN COALESCE((
                        SELECT SUM(PV.cantidad)
                        FROM piezas_vales PV
                        INNER JOIN vales V ON PV.id_vale = V.id
                        INNER JOIN presupuestos PR ON V.id_presupuesto = PR.id
                        WHERE PR.id_siniestro = siniestros.id
                        AND V.estado != 'Cancelado'
                        AND PV.activo = 1
                    ), 0) = 0 THEN 0
                    WHEN (SELECT VI.taller FROM vehiculo_info VI WHERE VI.id = siniestros.id_vehiculo)
                        IN ('AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO') THEN
                        -- AUTOCAR: porcentaje basado en entradas
                        ROUND(
                            COALESCE((
                                SELECT SUM(PE.cantidad)
                                FROM piezas_entradas PE
                                INNER JOIN entradas E ON PE.id_entrada = E.id
                                WHERE E.id_siniestro = siniestros.id
                                AND E.estado != 'Cancelado'
                                AND PE.activo = 1
                            ), 0) * 100.0 /
                            COALESCE((
                                SELECT SUM(PV.cantidad)
                                FROM piezas_vales PV
                                INNER JOIN vales V ON PV.id_vale = V.id
                                INNER JOIN presupuestos PR ON V.id_presupuesto = PR.id
                                WHERE PR.id_siniestro = siniestros.id
                                AND V.estado != 'Cancelado'
                                AND PV.activo = 1
                            ), 1)
                        , 2)
                    ELSE
                        -- Otros: porcentaje basado en albaranes
                        ROUND(
                            COALESCE((
                                SELECT SUM(PA.cantidad)
                                FROM piezas_albaranes PA
                                INNER JOIN albaranes A ON PA.id_albaran = A.id
                                WHERE A.id_siniestro = siniestros.id
                                AND A.estado != 'Cancelado'
                                AND PA.activo = 1
                            ), 0) * 100.0 /
                            COALESCE((
                                SELECT SUM(PV.cantidad)
                                FROM piezas_vales PV
                                INNER JOIN vales V ON PV.id_vale = V.id
                                INNER JOIN presupuestos PR ON V.id_presupuesto = PR.id
                                WHERE PR.id_siniestro = siniestros.id
                                AND V.estado != 'Cancelado'
                                AND PV.activo = 1
                            ), 1)
                        , 2)
                END
            ) as porcentaje_total_vehiculo")
            // pzs_surtidas: para AUTOCAR solo entradas, para otros solo albaranes
            ->selectRaw("(
                CASE
                    WHEN (SELECT VI.taller FROM vehiculo_info VI WHERE VI.id = siniestros.id_vehiculo)
                        IN ('AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO')
                        AND NOT EXISTS (
                            SELECT 1 FROM presupuestos P2 
                            WHERE P2.id_siniestro = siniestros.id 
                            AND P2.proveedor = 'CHEVROLET'
                        ) THEN
                        COALESCE((
                            SELECT SUM(PE.cantidad)
                            FROM piezas_entradas PE
                            INNER JOIN entradas E ON PE.id_entrada = E.id
                            WHERE E.id_siniestro = siniestros.id
                            AND E.estado != 'Cancelado'
                            AND PE.activo = 1
                        ), 0)
                    ELSE
                        COALESCE((
                            SELECT SUM(PA.cantidad)
                            FROM piezas_albaranes PA
                            INNER JOIN albaranes A ON PA.id_albaran = A.id
                            WHERE A.id_siniestro = siniestros.id
                            AND A.estado != 'Cancelado'
                            AND PA.activo = 1
                        ), 0)
                END
            ) as pzs_surtidas")
            // tiempo_surtido: CHEVROLET filtra por albaranes, otros por entradas
            // Si es Autocar, siempre usa entradas
            ->selectRaw("(
                SELECT COALESCE(JSON_ARRAYAGG(P.tiempoentrega), '[]')
                FROM piezas_vales PV
                INNER JOIN vales V ON PV.id_vale = V.id
                INNER JOIN piezas P ON PV.id_pieza = P.id
                INNER JOIN presupuestos PR ON V.id_presupuesto = PR.id
                WHERE PR.id_siniestro = siniestros.id
                AND V.estado != 'Cancelado'
                AND PV.activo = 1
                AND (
                    CASE 
                        WHEN (SELECT VI.taller FROM vehiculo_info VI WHERE VI.id = siniestros.id_vehiculo)
                            IN ('AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO') THEN
                             NOT EXISTS (
                                SELECT 1
                                FROM piezas_entradas PE
                                INNER JOIN entradas E ON PE.id_entrada = E.id
                                WHERE PE.id_pieza = P.id
                                AND E.id_siniestro = siniestros.id
                                AND E.estado != 'Cancelado'
                                AND PE.activo = 1
                            )
                        WHEN EXISTS (
                            SELECT 1 FROM presupuestos P2 
                            WHERE P2.id_siniestro = siniestros.id 
                            AND P2.proveedor = 'CHEVROLET'
                        ) THEN
                            NOT EXISTS (
                                SELECT 1
                                FROM piezas_albaranes PA
                                INNER JOIN albaranes A ON PA.id_albaran = A.id
                                WHERE PA.id_pieza = P.id
                                AND A.id_siniestro = siniestros.id
                                AND A.estado != 'Cancelado'
                                AND PA.activo = 1
                            )
                        ELSE
                            NOT EXISTS (
                                SELECT 1
                                FROM piezas_entradas PE
                                INNER JOIN entradas E ON PE.id_entrada = E.id
                                WHERE PE.id_pieza = P.id
                                AND E.id_siniestro = siniestros.id
                                AND E.estado != 'Cancelado'
                                AND PE.activo = 1
                            )
                    END
                )
            ) as tiempo_surtido");
    }

    public function presupuestos()
    {
        return $this->hasMany(Presupuesto::class, 'id_siniestro', 'id');
    }

    public function scopeReportes($query)
    {
        return $query
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id')
            ->select(
                'siniestros.numero_orden',
                'siniestros.numero_siniestro',
                'vehiculo_info.aseguradora',
                'vehiculo_info.vin',
                'vehiculo_info.marca',
                'vehiculo_info.vehiculo',
                'vehiculo_info.modelo',
                'vehiculo_info.taller',
                // Suma de los vales NO cancelados de todos los presupuestos del siniestro
                DB::raw('(
                    SELECT COALESCE(SUM(vales.total),0)
                    FROM presupuestos
                    INNER JOIN vales ON vales.id_presupuesto = presupuestos.id
                    WHERE presupuestos.id_siniestro = siniestros.id
                    AND vales.estado != \'Cancelado\'
                ) as importe_autorizado'),
                // Importe recibido: lógica diferenciada por taller y proveedor
                // - AUTOCAR (Pensiones/Periferico) con proveedor != CHEVROLET → entradas
                // - Cualquier otro caso (taller != AUTOCAR, o AUTOCAR con CHEVROLET) → albaranes
                DB::raw('(
                    CASE
                        WHEN (
                            SELECT VI.taller FROM vehiculo_info VI WHERE VI.id = siniestros.id_vehiculo
                        ) IN (\'AUTOCAR PENSIONES\', \'AUTOCAR PERIFERICO\')
                        AND NOT EXISTS (
                            SELECT 1 FROM presupuestos P2
                            WHERE P2.id_siniestro = siniestros.id
                            AND UPPER(P2.proveedor) = \'CHEVROLET\'
                        )
                        THEN (
                            -- AUTOCAR con proveedor != CHEVROLET: entradas.total ya incluye IVA
                            SELECT COALESCE(SUM(entradas.total), 0)
                            FROM entradas
                            WHERE entradas.id_siniestro = siniestros.id
                            AND entradas.estado != \'Cancelado\'
                        )
                        ELSE (
                            -- Refacciones o AUTOCAR con CHEVROLET: albaranes.total ya incluye IVA
                            SELECT COALESCE(SUM(albaranes.total), 0)
                            FROM albaranes
                            WHERE albaranes.id_siniestro = siniestros.id
                            AND albaranes.estado != \'Cancelado\'
                        )
                    END
                ) as importe_recibido'),
                'siniestros.created_at as registro',
                'siniestros.updated_at as actualizacion',
                'siniestros.estado'
            )
            ->groupBy(
                'siniestros.id',
                'siniestros.numero_orden',
                'siniestros.numero_siniestro',
                'vehiculo_info.aseguradora',
                'vehiculo_info.vin',
                'vehiculo_info.marca',
                'vehiculo_info.vehiculo',
                'vehiculo_info.modelo',
                'vehiculo_info.taller',
                'siniestros.created_at',
                'siniestros.updated_at',
                'siniestros.estado'
            );
    }

    public static function getPzasAutorizadas($id_siniestro)
    {
        return self::join('presupuestos', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->join('vales', 'vales.id_presupuesto', '=', 'presupuestos.id')
            ->join('piezas_vales', 'piezas_vales.id_vale', '=', 'vales.id')
            ->join('piezas', 'piezas.id', '=', 'piezas_vales.id_pieza')
            ->select(
                'siniestros.numero_orden',
                'presupuestos.numero_presupuesto',
                'vales.numero_vale',
                'piezas.numero_parte',
                DB::raw("COALESCE(piezas.descripcion_w32, piezas.descripcion, 'Sin descripcion') as descripcion_w32"),
                'piezas_vales.cantidad'
            )
            ->where('siniestros.id', $id_siniestro)
            ->get();
    }

    public static function getPzasSurtidas($id_siniestro)
    {
        return self::select(
            'siniestros.numero_orden',
            'presupuestos.numero_presupuesto',
            'vales.numero_vale',
            'piezas.numero_parte',
            DB::raw("COALESCE(piezas.descripcion_w32, piezas.descripcion, 'Sin descripcion') as descripcion_w32"),
            'piezas_albaranes.cantidad',
            'albaranes.numero_albaran'
        )
            ->join('presupuestos', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->join('vales', 'vales.id_presupuesto', '=', 'presupuestos.id')
            ->join('albaranes', 'albaranes.id_vale', '=', 'vales.id')
            ->join('piezas_albaranes', 'piezas_albaranes.id_albaran', '=', 'albaranes.id')
            ->join('piezas', 'piezas.id', '=', 'piezas_albaranes.id_pieza')
            ->where('siniestros.id', $id_siniestro)
            ->get();
    }

    public static function getPzasRecibidas($id_siniestro)
    {
        return self::select(
            'siniestros.numero_orden',
            'presupuestos.numero_presupuesto',
            'vales.numero_vale',
            'piezas.numero_parte',
            DB::raw("COALESCE(piezas.descripcion_w32, piezas.descripcion, 'Sin descripcion') as descripcion_w32"),
            'piezas_entradas.cantidad',
            'entradas.numero_entrada'
        )
            ->join('presupuestos', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->join('vales', 'vales.id_presupuesto', '=', 'presupuestos.id')
            ->join('entradas', 'entradas.id_vale', '=', 'vales.id')
            ->join('piezas_entradas', 'piezas_entradas.id_entrada', '=', 'entradas.id')
            ->join('piezas', 'piezas.id', '=', 'piezas_entradas.id_pieza')
            ->where('siniestros.id', $id_siniestro)
            ->get();
    }

    public static function getPzasFaltantes($id_siniestro)
    {
        $pzasAutorizadas = self::getPzasAutorizadas($id_siniestro);
        $pzasRecibidas = self::getPzasRecibidas($id_siniestro);

        $recibidasPorParte = $pzasRecibidas->groupBy('numero_parte')->map(function ($items) {
            return $items->sum('cantidad');
        });

        $pzasFaltantes = $pzasAutorizadas->map(function ($item) use ($recibidasPorParte) {
            $cantidadRecibida = $recibidasPorParte[$item->numero_parte] ?? 0;
            $cantidadFaltante = $item->cantidad - $cantidadRecibida;

            if ($cantidadFaltante > 0) {
                $item->cantidad_faltante = $cantidadFaltante;
                $item->cantidad_autorizada = $item->cantidad;
                $item->cantidad_recibida = $cantidadRecibida;
                return $item;
            }

            return null;
        })->filter()->values();

        return $pzasFaltantes;
    }

    public static function getTPresupuestos($id_siniestro)
    {
        return self::select('presupuestos.numero_presupuesto', 'presupuestos.proveedor', 'presupuestos.total')->join('presupuestos', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->where('siniestros.id', $id_siniestro)->get();
    }
}
