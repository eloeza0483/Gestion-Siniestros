<?php

namespace App\Policies;

use App\Models\User;

class VehiculoInfoPolicy extends UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewProcesosVehiculoAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'procesoVehiculo.view');
    }

    public function viewProcesosVehiculoAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'procesoVehiculo.view');
    }

    public function viewProcesosVehiculoRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'procesoVehiculo.view');
    }

    public function viewSeguimientoTrabajoAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'seguimientoTrabajo.view');
    }

    public function viewSeguimientoTrabajoAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'seguimientoTrabajo.view');
    }

    public function viewSeguimientoTrabajoRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'seguimientoTrabajo.view');
    }
}
