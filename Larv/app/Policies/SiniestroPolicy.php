<?php

namespace App\Policies;

use App\Models\Perfile;
use App\Models\PerfilUsuario;
use App\Models\PermisosUsuarios;
use App\Models\Siniestro;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SiniestroPolicy extends UserPolicy
{
    // public function getArrayPerfilIds($user, $nombre_perfil)
    // {
    //     $pu = PerfilUsuario::where('user_id', $user->id);
    //     if (!$pu->exists()) return false;
    //     $id_perfil = Perfile::where('nombre', $nombre_perfil)->first()->id;
    //     $perfil_ids = $pu->first()->perfil_id;
    //     $array_perfil_ids = explode(",", $perfil_ids);
    //     return (object) ['success' => in_array($id_perfil, $array_perfil_ids), 'id_perfil' => $id_perfil];
    // }

    // private function existePermiso($user, $nombre_perfil, $nombre_permiso)
    // {
    //     $validacion = $this->getArrayPerfilIds($user, $nombre_perfil);
    //     if (!$validacion->success) return false;
    //     return PermisosUsuarios::where('user_id', $user->id)
    //         ->where('perfil_id', $validacion->id_perfil)
    //         ->whereHas('permisos', fn($q) => $q->where('nombre', $nombre_permiso))
    //         ->exists();
    // }

    // VIEWS
    public function viewSiniestrosAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'siniestros.view');
    }

    public function viewSiniestrosAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'siniestros.view');
    }

    public function viewSiniestrosRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'siniestros.view');
    }

    // WRITE

    public function writeSiniestrosAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'siniestros.write');
    }

    public function writeSiniestrosAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'siniestros.write');
    }

    public function writeSiniestrosRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'siniestros.write');
    }

    // UPDATE

    public function updateSiniestrosAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'siniestros.update');
    }

    public function updateSiniestrosAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'siniestros.update');
    }

    public function updateSiniestrosRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'siniestros.update');
    }

    public function tallerSiniestros(User $user)
    {
        return $user->tienePermiso('autocar');
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Siniestro $siniestro): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Siniestro $siniestro): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Siniestro $siniestro): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Siniestro $siniestro): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Siniestro $siniestro): bool
    {
        return false;
    }
}
