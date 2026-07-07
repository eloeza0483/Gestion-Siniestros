<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Presupuesto;
use App\Models\User;

class Vale extends Model
{

    use HasFactory;
    protected $table = 'vales';
    protected $fillable = ['numero_vale', 'id_presupuesto', 'estado', 'subtotal', 'iva', 'total', 'fecha_vale', 'fecha_promesa', 'fecha_surtido', 'id_usuario_registro', 'id_usuario_cancelacion', 'motivo_cancelacion', 'fecha_cancelacion', 'created_at', 'updated_at'];

    public function presupuestos()
    {
        return $this->belongsTo(Presupuesto::class, 'id_presupuesto');
    }

    public function albaranes()
    {
        return $this->hasMany(Albaran::class, 'id_vale', 'id');
    }

    public function entradas()
    {
        return $this->hasMany(Entrada::class, 'id_vale', 'id');
    }


    public function usuarioRegistro()
    {
        return $this->hasMany(User::class, 'id', 'id_usuario_registro');
    }

    public function piezas()
    {
        return $this->belongsToMany(Piezas::class, 'piezas_vales', 'id_vale', 'id_pieza')
            ->withPivot('cantidad', 'solicitud_eliminacion', 'activo', 'id_usuario_solicita_eliminacion', 'fecha_solicitud_eliminacion')
            ->withTimestamps();
    }

    public function scopeReportes($query)
    {
        return $query
            ->join('presupuestos', 'presupuestos.id', '=', 'vales.id_presupuesto')
            ->join('siniestros', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id')
            ->select(
                'vales.numero_vale',
                'siniestros.numero_siniestro',
                'siniestros.numero_orden',
                'presupuestos.numero_presupuesto',
                'vales.subtotal',
                'vales.total',
                'vales.fecha_vale',
                'vales.fecha_promesa',
                'vales.estado',
                'vehiculo_info.taller',
                'vales.created_at as registro',
                'vales.updated_at as actualizacion'
            )
            ->groupBy(
                'vales.numero_vale',
                'siniestros.numero_siniestro',
                'siniestros.numero_orden',
                'presupuestos.numero_presupuesto',
                'vales.subtotal',
                'vales.total',
                'vales.fecha_vale',
                'vales.fecha_promesa',
                'vales.estado',
                'vehiculo_info.taller',
                'vales.created_at',
                'vales.updated_at'
            );
    }
}
