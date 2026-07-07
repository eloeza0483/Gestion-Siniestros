<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Udn extends Model
{
    protected $connection = 'mysqlGTI';
    protected $table = 'udns';
}
