<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

//La diferencia con el otro modelo de permisos, es que este es correspondiente a la tabla permisos_desarrollo de gestionTI
class PermisosD extends Model
{
    use HasFactory;
    protected $connection = 'mysqlGTI';
    protected $table = 'permiso_desarrollos';
}
