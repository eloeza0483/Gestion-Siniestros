<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiezasAlbaran extends Model
{
    use HasFactory;
    protected $table = 'piezas_albaranes';
    protected $fillable = [
        'id',
        'id_albaran',
        'id_pieza',
        'cantidad',
        'activo',
        'created_at',
        'updated_at'
    ];

    public function albaran()
    {
        return $this->belongsTo(Albaran::class, 'id_albaran');
    }

    public function piezas()
    {
        return $this->belongsTo(Piezas::class, 'id_pieza');
    }
}
