<?php

namespace App\Policies;

use App\Models\User;

class FacturaPolicy extends UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewFacturasAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'facturas.view');
    }

    public function viewFacturasAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'facturas.view');
    }

    public function viewFacturasRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'facturas.view');
    }

    public function writeFacturas(User $user)
    {
        return $user->tienePermiso('facturas.write');
    }

    public function updateFacturas(User $user)
    {
        return $user->tienePermiso('facturas.update');
    }
}
