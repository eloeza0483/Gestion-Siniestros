<?php

namespace App\Http\Traits;

trait SiniestroTrait
{
    public static function formatPerfilToPermisos($perfil)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $perfil)));
    }

    public static function formatPerfilToSQL($perfil)
    {
        return strtoupper(str_replace('_', ' ', $perfil));
    }

    public static function formatPerfilPolicy($perfil)
    {
        if (empty($perfil)) return '';
        $a = explode('_', $perfil);
        $am = array_map(fn($f) => ucwords($f), $a);
        $np = implode('', $am);
        return $np;
    }
}
