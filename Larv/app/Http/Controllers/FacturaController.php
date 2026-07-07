<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    public function getFacturasView()
    {
        return view('facturas');
    }

    public function getFacturas(Request $request, $perfil = null)
    {
        $facturas = Factura::all();

        return $facturas;
    }
}
