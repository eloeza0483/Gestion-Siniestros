<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilUsuario extends Model
{
    protected $table = 'perfil_usuarios';
    protected $fillable = ['user_id', 'perfil_id'];
}
