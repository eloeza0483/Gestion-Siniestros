<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Navbar extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {

        $perfilParam = request()->route('perfil');
        $siniestroService = new \App\Services\SiniestroService();
        $rr = $siniestroService->acceso_perfiles();
        $perfilLabel = $perfilParam ? ucwords(str_replace('_', ' ', $perfilParam)) : null;
        $perfilPermiso = $perfilParam ? str_replace(' ', '', ucwords(str_replace('_', ' ', $perfilParam))) : null;

        return view('components.navbar', [
            'authUser'      => auth()->user(),
            'perfilActivo'  => $perfilParam,
            'perfilLabel'   => $perfilLabel,
            'perfilPermiso' => $perfilPermiso,
            'perfiles'      => $rr->success ? $rr->perfiles : collect(),
            'perfil_id'     => $siniestroService->getIdPerfil($perfilParam),
        ]);
    }
}
