<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Presupuesto extends Model
{
    protected $table = 'presupuestos';

    protected $fillable = ['numero_presupuesto', 'id_siniestro', 'subtotal', 'iva', 'total', 'estado', 'proveedor', 'fecha_cotizado', 'id_usuario_creacion', 'id_usuario_cotizacion'];

    public function piezas()
    {
        return $this->hasMany(Piezas::class, 'id_presupuesto');
    }

    public function vales()
    {
        return $this->hasMany(Vale::class, 'id_presupuesto');
    }

    public function siniestros()
    {
        return $this->belongsTo(Siniestro::class, 'id_siniestro');
    }

    public function usuarioCreacion()
    {
        return $this->hasMany(User::class, 'id', 'id_usuario_creacion');
    }

    public function vehiculoInfo()
    {
        return $this->hasMany(VehiculoInfo::class, 'id', 'id_usuario_creacion');
    }

    public function getNumeroProductosAttribute()
    {
        return $this->piezas()->sum('numero_pzas_presupuesto');
    }

    public function permisosUsuarios()
    {
        // $this->canUpdate = $permisos->where('nombre', 'presupuestos.update')->values()->first() ?? false;
        // $permisos = PermisosUsuarios::permisosAuth();

        $this->numero_productos = $this->numero_productos;
        // foreach ($permisos as $permiso) {
        //     $this->{$permiso->nombre} = true;
        // }

        return $this;
    }

    public function scopeReportes($query)
    {
        return $query
            ->join('piezas', 'presupuestos.id', '=', 'piezas.id_presupuesto')
            ->join('siniestros', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id')
            ->select(
                'presupuestos.numero_presupuesto',
                'presupuestos.subtotal',
                'presupuestos.total',
                'siniestros.numero_siniestro',
                'piezas.numero_parte',
                'piezas.descripcion',
                'piezas.descripcion_w32',
                'piezas.numero_pzas_presupuesto',
                'piezas.importe_unitario',
                'piezas.importe_total',
                'presupuestos.estado',
                'vehiculo_info.taller',
                'presupuestos.created_at as registro',
                'presupuestos.updated_at as actualizacion'
            )
            ->groupBy(
                'presupuestos.numero_presupuesto',
                'presupuestos.subtotal',
                'presupuestos.total',
                'siniestros.numero_siniestro',
                'piezas.numero_parte',
                'piezas.descripcion',
                'piezas.descripcion_w32',
                'piezas.numero_pzas_presupuesto',
                'piezas.importe_unitario',
                'piezas.importe_total',
                'presupuestos.estado',
                'vehiculo_info.taller',
                'presupuestos.created_at',
                'presupuestos.updated_at'
            );
    }
}
