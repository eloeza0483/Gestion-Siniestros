<?php

namespace App\Policies;

use App\Models\User;

class EntradaPolicy extends UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewEntradasAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'entradas.view');
    }

    public function viewEntradasAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'entradas.view');
    }

    public function viewEntradasRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'entradas.view');
    }

    public function writeEntradasAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'entradas.write');
    }

    public function writeEntradasAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'entradas.write');
    }

    public function writeEntradasRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'entradas.write');
    }

    public function updateEntradasAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'entradas.update');
    }

    public function updateEntradasAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'entradas.update');
    }

    public function updateEntradasRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'entradas.update');
    }
}
