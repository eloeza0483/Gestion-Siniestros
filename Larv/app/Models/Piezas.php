<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Piezas extends Model
{
    use HasFactory;
    protected $table = 'piezas'; //Con piezas se refiere a PARTES
    protected $fillable = [
        'numero_parte',
        'descripcion',
        'descripcion_w32',
        'importe_unitario',
        'importe_total',
        'numero_pzas_presupuesto',
        'pza_adicional',
        'id_presupuesto',
        'id_entrada',
        'id_factura',
        'activo_presupuesto',
        'activo', // Agregado campo activo
        'existencia',
        'es_complemento',
        'tiempoentrega',
    ];


    public function siniestros()
    {
        return $this->belongsTo(Siniestro::class, 'id_siniestro');
    }

    public function presupuestos()
    {
        return $this->belongsTo(Presupuesto::class, 'id_presupuesto');
    }

    public function vales()
    {
        return $this->belongsToMany(Vale::class, 'piezas_vales', 'id_pieza', 'id_vale')
            ->withPivot('cantidad', 'activo', 'solicitud_eliminacion')
            ->withTimestamps();
    }
}
