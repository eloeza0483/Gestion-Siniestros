<?php

namespace App\Policies;

use App\Models\User;

class ReportePolicy extends UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }


    public function viewReportesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'reportes.view');
    }

    public function viewReportesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'reportes.view');
    }

    public function viewReportesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'reportes.view');
    }

    public function writeReportes(User $user)
    {
        return $user->tienePermiso('reportes.write');
    }

    public function updateReportes(User $user)
    {
        return $user->tienePermiso('reportes.update');
    }
}
