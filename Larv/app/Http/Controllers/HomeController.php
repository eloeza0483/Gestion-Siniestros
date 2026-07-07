<?php

namespace App\Http\Controllers;

use App\Services\SiniestroService;
use Illuminate\Auth\Middleware\Authorize;

class HomeController extends Controller
{
    //

    public function index()
    {
        $siniestroService = new SiniestroService();
        $rr = $siniestroService->acceso_perfiles();
        $perfiles = collect($rr->perfiles ?? []);

        if ($perfiles->count() === 1) {
            $nombre_url = str_replace(' ', '_', strtolower($perfiles->first()->nombre ?? ''));
            return redirect()->route('siniestros.view', $nombre_url);
        }

        return view('inicio', compact('perfiles'));
    }
}
