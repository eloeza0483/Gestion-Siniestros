<?php

namespace App\Policies;

use App\Models\User;

class ValePolicy extends UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    // VALES

    public function viewValesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'vales.view');
    }

    public function viewValesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'vales.view');
    }

    public function viewValesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'vales.view');
    }

    //WRITE


    public function writeValesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'vales.write');
    }

    public function writeValesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'vales.write');
    }

    public function writeValesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'vales.write');
    }



    public function updateValesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'vales.update');
    }

    public function updateValesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'vales.update');
    }

    public function updateValesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'vales.update');
    }

    // CANCEL

    public function cancelValesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'vales.cancel');
    }

    public function cancelValesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'vales.cancel');
    }

    public function cancelValesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'vales.cancel');
    }
}
