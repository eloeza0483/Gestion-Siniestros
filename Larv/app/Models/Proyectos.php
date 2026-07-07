<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Proyectos extends Model
{
    use HasFactory;
    protected $connection = 'mysqlGTI';
    protected $table = 'proyectos';
}
