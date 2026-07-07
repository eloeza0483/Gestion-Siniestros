<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdatePresupuestoLog extends Model
{
    protected $table = 'update_presupuesto_logs';

    protected $fillable = [
        'id_presupuesto',
        'id_pieza',
        'id_usuario',
        'accion',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
    ];

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'id_presupuesto');
    }

    public function pieza()
    {
        return $this->belongsTo(Piezas::class, 'id_pieza');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
