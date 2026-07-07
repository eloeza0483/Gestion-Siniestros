<?php

namespace App\Policies;

use App\Models\Perfile;
use App\Models\PerfilUsuario;
use App\Models\User;

class PerfilUsuarioPolicy
{

    public function getArrayPerfilIds($user, $nombre_perfil)
    {
        $pu = PerfilUsuario::where('user_id', $user->id);
        if (!$pu->exists()) return false;
        $id_perfil = Perfile::where('nombre', $nombre_perfil)->first()->id;
        $perfil_ids = $pu->first()->perfil_id;
        $array_perfil_ids = explode(",", $perfil_ids);
        return in_array($id_perfil, $array_perfil_ids);
    }

    public function autocar_pensiones(User $user)
    {
        return $this->getArrayPerfilIds($user, 'AUTOCAR PENSIONES');
    }

    public function autocar_periferico(User $user)
    {
        return $this->getArrayPerfilIds($user, 'AUTOCAR PERIFERICO');
    }

    public function refacciones(User $user)
    {
        return $this->getArrayPerfilIds($user, 'REFACCIONES');
    }
}
