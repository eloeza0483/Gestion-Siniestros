<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoLog extends Model
{
    protected $table = 'permisos_logs';

    protected $fillable = [
        'id_usuario',          // Administrador que hizo el movimiento (Auth::id())
        'id_usuario_afectado', // Usuario al que se le cambiaron los permisos
        'accion',              // Ej: 'ASIGNAR_ROL', 'QUITAR_ROL', 'MODIFICAR_PERMISOS'
        'campo_modificado',    // Ej: 'Rol Principal', 'Permisos Custom', 'Perfil'
        'valor_anterior',      // JSON o Texto con lo que había antes
        'valor_nuevo'          // JSON o Texto con lo que se puso ahora
    ];
}
