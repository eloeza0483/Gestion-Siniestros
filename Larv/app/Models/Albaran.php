<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDO;

class Albaran extends W32
{
    use HasFactory;
    protected $table = 'albaranes';
    protected $fillable = ['id_siniestro', 'numero_albaran', 'id_vale', 'importe', 'total', 'estado', 'id_usuario_registro', 'motivo_cancelacion', 'created_at', 'updated_at'];

    public function siniestros()
    {
        return $this->belongsTo(Siniestro::class, 'id_siniestro', 'id');
    }

    public function vales()
    {
        return $this->belongsTo(Vale::class, 'id_vale', 'id');
    }

    public function consultarAlbaranW32($numAlbaran, $marca, $taller)
    // public function consultarAlbaranW32($numAlbaran)
    {

        // dd($numAlbaran, $marca);

        // Si se proporciona una marca, agregar la condición para que DFAC.FAMCON sea igual a la marca
        $condicionMarca = "";
        if (!empty($marca)) {
            if (strtoupper($marca) === 'CHEVROLET') {
                $condicionMarca = " AND DFAC.FAMCON = 'CHEVROLET'";
            } else {
                $condicionMarca = " AND DFAC.FAMCON = 'OTROS'";
            }
        }

        $sql = "SELECT CFAC.id AS ID_REMISION,
                CFAC.NUMERO AS NUMREMISION,
                DFAC.REFERENCIA,
                CAST(DFAC.DESCRIP AS VARCHAR(250) CHARACTER SET WIN1252) AS DESCRIP,
                CAST(CFAC.FECHA AS DATE) AS FECHA_,
                CFAC.FECHA,
                DFAC.UNI,
                DFAC.PRE,
                DFAC.IMP,
                CFAC.BRUTO,
                CFAC.IMPIVA1,
                CFAC.TOTFAC
            FROM cabfactu CFAC 
            INNER JOIN detfactu DFAC ON CFAC.id = DFAC.mincabfactu
            WHERE CFAC.tipdoc = 11
            AND CFAC.MINEMPRESAS = 5815609
            -- AND CAST(CFAC.FECHA AS DATE) BETWEEN DATEADD(-2 YEAR TO CURRENT_DATE) AND CURRENT_DATE
            AND CFAC.NUMERO = '$numAlbaran'
            $condicionMarca
            ORDER BY DFAC.referencia ASC";

        $pdo = W32::coneccionMontecristo();
        $query = $pdo->prepare($sql);
        $query->execute();
        $albaran = $query->fetchAll(PDO::FETCH_ASSOC);
        return $albaran;
    }

    public function scopeReportes($query)
    {
        return $query
            ->join('siniestros', 'albaranes.id_siniestro', '=', 'siniestros.id')
            ->join('presupuestos', 'siniestros.id', '=', 'presupuestos.id_siniestro')
            ->join('vales', 'albaranes.id_vale', '=', 'vales.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id')
            ->select(
                'albaranes.id',
                'albaranes.numero_albaran',
                'siniestros.numero_orden',
                'siniestros.numero_siniestro',
                'vales.numero_vale',
                'albaranes.importe',
                'albaranes.created_at as registro',
                'albaranes.updated_at as actualizacion',
                'albaranes.estado',
            )
            ->groupBy(
                'albaranes.id',
                'albaranes.numero_albaran',
                'siniestros.numero_orden',
                'siniestros.numero_siniestro',
                'vales.numero_vale',
                'albaranes.importe',
                'albaranes.created_at',
                'albaranes.updated_at',
                'albaranes.estado',
            );
    }
}
