<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculoInfo extends Model
{
    protected $table = 'vehiculo_info';
    protected $fillable = ['vin', 'aseguradora', 'taller', 'marca', 'modelo', 'vehiculo', 'estado', 'fecha_inicio', 'fecha_fin', 'adelanto', 'motivo_pausa'];

    public function siniestros()
    {
        return $this->hasMany(Siniestro::class, 'id_vehiculo');
    }
}
