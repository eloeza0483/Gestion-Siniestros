<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Taller;

class TallerController extends Controller
{
    public function getTalleres(){
        return Taller::all();
    }
}
