<?php

namespace App\Policies;

use App\Models\User;

class AlbaranPolicy extends UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAlbaranesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'albaranes.view');
    }

    public function viewAlbaranesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'albaranes.view');
    }

    public function viewAlbaranesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'albaranes.view');
    }


    public function writeAlbaranesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'albaranes.write');
    }

    public function writeAlbaranesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'albaranes.write');
    }

    public function writeAlbaranesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'albaranes.write');
    }


    // public function writeAlbaranes(User $user)
    // {
    //     return $user->tienePermiso('albaranes.write');
    // }

    public function updateAlbaranes(User $user)
    {
        return $user->tienePermiso('albaranes.update');
    }
}
