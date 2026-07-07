<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $connection = 'mysql';
    protected $table = 'roles';

    // protected $fillable = [
    //     'nombre',
    //     'descripcion',
    // ];


    public static function getRoles()
    {
        return self::select('id', 'rol')->get();
    }
}
