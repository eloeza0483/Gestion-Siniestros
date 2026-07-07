<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisosUsuarios extends Model
{
    use HasFactory;
    protected $table = 'permisos_usuarios';
    protected $fillable = ['nombre'];


    public static function permisosAuth($perfil_id)
    {
        // 1. Obtener los permisos adicionales guardados directamente
        $permisosCustom = self::join('permisos', 'permisos_usuarios.permiso_id', 'permisos.id')
            ->join('perfiles', 'perfiles.id', 'permisos_usuarios.perfil_id')
            ->where('perfiles.id', $perfil_id)
            ->whereNotNull('perfil_id')
            ->where('permisos_usuarios.user_id', auth()->user()->id)
            ->where('permisos_usuarios.activo', 1)
            ->pluck('permisos.nombre')
            ->toArray();

        // 2. Obtener los permisos heredados del Rol del usuario (Principal y Secundarios)
        $permisosRolNames = [];
        $idProy = \App\Models\Proyectos::where('nombre', 'GestionSiniestros')->first();
        if ($idProy) {
            $asignacion = \Illuminate\Support\Facades\DB::connection('mysqlGTI')
                ->table('permiso_desarrollos')
                ->where('id_usuario', auth()->user()->id)
                ->where('id_proyecto', $idProy->id)
                ->where('activo', 1)
                ->first();

            if ($asignacion) {
                $rolesIds = [];
                if ($asignacion->id_rol) {
                    $rolesIds[] = $asignacion->id_rol;
                }
                if (!empty($asignacion->opciones)) {
                    $secundarios = array_filter(array_map('trim', explode(',', $asignacion->opciones)));
                    $rolesIds = array_merge($rolesIds, $secundarios);
                }

                if (!empty($rolesIds)) {
                    $roles = \Illuminate\Support\Facades\DB::connection('mysql')
                        ->table('roles')
                        ->whereIn('id', $rolesIds)
                        ->where('id_perfil', $perfil_id)
                        ->get();

                    $permisosIds = [];
                    foreach ($roles as $rol) {
                        if (empty($rol->id_permisos)) continue;
                        $pArr = json_decode($rol->id_permisos, true);
                        if (is_array($pArr)) {
                            $permisosIds = array_merge($permisosIds, $pArr);
                        }
                    }

                    if (!empty($permisosIds)) {
                        $permisosRolNames = \Illuminate\Support\Facades\DB::connection('mysql')
                            ->table('permisos')
                            ->whereIn('id', array_unique($permisosIds))
                            ->pluck('nombre')
                            ->toArray();
                    }
                }
            }
        }

        // Fusionar ambos conjuntos de permisos de forma única
        return array_values(array_unique(array_merge($permisosCustom, $permisosRolNames)));
    }

    public function permisos()
    {
        return $this->belongsTo(Permisos::class, 'permiso_id');
    }
}
