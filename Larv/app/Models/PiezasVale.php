<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiezasVale extends Model
{

    use HasFactory;
    protected $table = 'piezas_vales';
    protected $fillable = [
        'id',
        'id_vale',
        'id_pieza',
        'cantidad',
        'solicitud_eliminacion',
        'id_usuario_solicita_eliminacion',
        'fecha_solicitud_eliminacion',
        'activo',
        'created_at',
        'updated_at'
    ];

    public function piezasPresupuesto()
    {
        return $this->belongsTo(Piezas::class, 'id_pieza');
    }

    public function usuarioSolicitaEliminacion()
    {
        return $this->belongsTo(User::class, 'id_usuario_solicita_eliminacion');
    }
}
