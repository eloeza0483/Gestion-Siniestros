<?php

namespace App\Policies;

use App\Models\User;

class PresupuestoPolicy extends UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    // PRESUPUESTOS
    //READ
    public function viewPresupuestosAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'presupuestos.view');
    }
    public function viewPresupuestosAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'presupuestos.view');
    }
    public function viewPresupuestosRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'presupuestos.view');
    }

    //WRITE
    public function writePresupuestosAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'presupuestos.write');
    }
    public function writePresupuestosAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'presupuestos.write');
    }
    public function writePresupuestosRefacciones(User $user)
    {

        return $this->existePermiso($user, 'REFACCIONES', 'presupuestos.write');
    }

    public function updatePresupuestos(User $user)
    {
        return $user->tienePermiso('presupuestos.update');
    }

    public function cotizarDirectamenteAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'presupuestos.cotizardirectamente');
    }
    public function cotizarDirectamenteAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'presupuestos.cotizardirectamente');
    }
    public function cotizarDirectamenteRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'presupuestos.cotizardirectamente');
    }
}
