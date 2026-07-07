<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

class Entrada extends Model
{
    use HasFactory;
    protected $table = 'entradas';
    protected $fillable = ['numero_entrada', 'id_vale', 'id_siniestro', 'importe', 'total', 'estado', 'id_usuario_registro', 'motivo_cancelacion', 'created_at', 'updated_at'];

    public function siniestros()
    {
        return $this->belongsTo(Siniestro::class, 'id_siniestro', 'id');
    }

    public function vales()
    {
        return $this->belongsTo(Vale::class, 'id_vale', 'id');
    }

    public function partes()
    {
        return $this->hasMany(PiezasEntrada::class, 'id_entrada', 'id');
    }


    public function consultarEntradaW32($numEntrada, $id_perfil)
    {
        $pdo = W32::coneccionAutocar();

        $sql = "SELECT cab.id AS ID_REMISION,
                    cab.minempresas AS ID_EMPRESA,
                    E.NOMEMP AS UDN,
                    cab.numero AS NUMREMISION,
                    det.referencia AS REFERENCIA,
                    CAST(det.descrip AS VARCHAR(250) CHARACTER SET WIN1252) AS DESCRIP,
                    CAST(cab.fecha AS DATE) AS FECHA_,
                    cab.fecha AS FECHA,
                    det.uni AS UNI,
                    det.pre AS PRE,
                    det.imp AS IMP,
                    cab.bruto AS BRUTO,
                    cab.impiva1 AS IMPIVA1,
                    cab.totfac AS TOTFAC
                FROM cabpedi cab
                    INNER JOIN detpedi det ON cab.id = det.mincabpedi AND det.rfnarticulo <> 0
                    INNER JOIN empresas AS E ON E.id = cab.minempresas
                    WHERE cab.tipdoc IN (91, 92)
                    AND cab.minempresas = $id_perfil
                    AND cab.numero = '$numEntrada'";
        try {
            $query = $pdo->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            Log::error('Error SQL consultarEntradaW32: ' . $e->getMessage(), [
                'numEntrada' => $numEntrada,
                'id_perfil' => $id_perfil,
            ]);
            throw $e;
        }
    }

    public function scopeReportes($query)
    {
        return $query
            ->join('siniestros', 'entradas.id_siniestro', '=', 'siniestros.id')
            ->join('presupuestos', 'siniestros.id', '=', 'presupuestos.id_siniestro')
            ->join('vales', 'vales.id_presupuesto', '=', 'presupuestos.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id')
            ->select(
                'entradas.id',
                'entradas.numero_entrada',
                'siniestros.numero_orden',
                'siniestros.numero_siniestro',
                'vales.numero_vale',
                'entradas.importe',
                'entradas.created_at as registro',
                'entradas.updated_at as actualizacion',
                'entradas.estado',
            )
            ->groupBy(
                'entradas.id',
                'entradas.numero_entrada',
                'siniestros.numero_orden',
                'siniestros.numero_siniestro',
                'vales.numero_vale',
                'entradas.importe',
                'entradas.created_at',
                'entradas.updated_at',
                'entradas.estado',
            );
    }
}
