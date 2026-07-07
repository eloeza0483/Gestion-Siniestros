<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiezasEntrada extends Model
{
    use HasFactory;
    protected $table = 'piezas_entradas';
    protected $fillable = [
        'id',
        'id_entrada',
        'id_pieza',
        'cantidad',
        'activo',
        'created_at',
        'updated_at'
    ];

    public function entrada()
    {
        return $this->belongsTo(Entrada::class, 'id_entrada');
    }

    public function piezas()
    {
        return $this->belongsTo(Piezas::class, 'id_pieza');
    }
}
