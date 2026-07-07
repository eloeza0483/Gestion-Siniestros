<?php

namespace App\Policies;

use App\Models\Perfile;
use App\Models\PerfilUsuario;
use App\Models\Permisos;
use App\Models\PermisosUsuarios;
use App\Models\Proyectos;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function getArrayPerfilIds($user, $nombre_perfil)
    {
        $pu = PerfilUsuario::where('user_id', $user->id);
        if (!$pu->exists()) return false;
        $id_perfil = Perfile::where('nombre', $nombre_perfil)->first()->id;
        $perfil_ids = $pu->first()->perfil_id;
        $array_perfil_ids = explode(",", $perfil_ids);
        return (object) ['success' => in_array($id_perfil, $array_perfil_ids), 'id_perfil' => $id_perfil];
    }
    public function existePermiso($user, $nombre_perfil, $nombre_permiso)
    {
        $validacion = $this->getArrayPerfilIds($user, $nombre_perfil);
        if (!$validacion->success) return false;

        // 1. Verifica permisos custom guardados en permisos_usuarios
        $tieneCustom = PermisosUsuarios::where('user_id', $user->id)
            ->where('perfil_id', $validacion->id_perfil)
            ->where('activo', 1)
            ->whereHas('permisos', fn($q) => $q->where('nombre', $nombre_permiso))
            ->exists();

        if ($tieneCustom) return true;

        // 2. Verifica permisos heredados del rol asignado en GTI para este perfil
        return $this->tienePermisoDeRol($user, $validacion->id_perfil, $nombre_permiso);
    }

    /**
     * Verifica si el permiso solicitado pertenece al JSON id_permisos de algún
     * rol que el usuario tenga asignado en GTI para el perfil indicado.
     */
    private function tienePermisoDeRol($user, $id_perfil, $nombre_permiso)
    {
        // Obtener el ID del permiso por nombre
        $permiso = Permisos::where('nombre', $nombre_permiso)->first();
        if (!$permiso) return false;

        // Obtener la asignación activa del usuario en GTI
        $idProy = Proyectos::where('nombre', 'GestionSiniestros')->first();
        if (!$idProy) return false;

        $asignacion = DB::connection('mysqlGTI')
            ->table('permiso_desarrollos')
            ->where('id_usuario', $user->id)
            ->where('id_proyecto', $idProy->id)
            ->where('activo', 1)
            ->first();

        if (!$asignacion) return false;

        // Reunir todos los IDs de roles (principal + secundarios)
        $rolesIds = [];
        if ($asignacion->id_rol) {
            $rolesIds[] = $asignacion->id_rol;
        }
        if (!empty($asignacion->opciones)) {
            $secundarios = array_filter(array_map('trim', explode(',', $asignacion->opciones)));
            $rolesIds = array_merge($rolesIds, $secundarios);
        }

        if (empty($rolesIds)) return false;

        // Filtrar solo los roles que correspondan al perfil solicitado
        $roles = DB::connection('mysql')
            ->table('roles')
            ->whereIn('id', $rolesIds)
            ->where('id_perfil', $id_perfil)
            ->get();

        foreach ($roles as $rol) {
            if (empty($rol->id_permisos)) continue;
            $permisosRol = json_decode($rol->id_permisos, true);
            if (is_array($permisosRol) && in_array($permiso->id, $permisosRol)) {
                return true;
            }
        }

        return false;
    }
    // public function viewAny(User $user)
    // {
    //     return $user->udn->id === 16 && $user->departamento->id === 13; //Esto está mal pero está por definirse
    // }

    // SINIESTROS
    // public function readSiniestros(User $user)
    // {
    //     return $user->tienePermiso('siniestros.read');
    // }

    // public function writeSiniestros(User $user)
    // {
    //     return $user->tienePermiso('siniestros.write');
    // }

    // public function updateSiniestros(User $user)
    // {
    //     return $user->tienePermiso('siniestros.update');
    // }


    // // PRESUPUESTOS
    // //READ
    // public function readPresupuestosAutocarPensiones(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'presupuestos.read');
    // }
    // public function readPresupuestosAutocarPeriferico(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'presupuestos.read');
    // }
    // public function readPresupuestosRefacciones(User $user)
    // {
    //     return $this->existePermiso($user, 'REFACCIONES', 'presupuestos.read');
    // }

    // //WRITE
    // public function writePresupuestos(User $user)
    // {
    //     return $user->tienePermiso('presupuestos.write');
    // }

    // public function updatePresupuestos(User $user)
    // {
    //     return $user->tienePermiso('presupuestos.update');
    // }

    // public function cotizarDirectamente(User $user)
    // {
    //     return $user->tienePermiso('presupuestos.cotizardirectamente');
    // }

    // // VALES
    // /* public function readVales(User $user)
    // {
    //     return $user->tienePermiso('vales.read');
    // } */

    // public function readValesAutocarPensiones(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'vales.read');
    // }

    // public function readValesAutocarPeriferico(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'vales.read');
    // }

    // public function readValesRefacciones(User $user)
    // {
    //     return $this->existePermiso($user, 'REFACCIONES', 'vales.read');
    // }

    // //WRITE
    // /* public function writeVales(User $user)
    // {
    //     return $user->tienePermiso('vales.write');
    // } */

    // public function writeValesAutocarPensiones(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'vales.write');
    // }

    // public function writeValesAutocarPeriferico(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'vales.write');
    // }

    // public function writeValesRefacciones(User $user)
    // {
    //     return $this->existePermiso($user, 'REFACCIONES', 'vales.write');
    // }

    // /* public function updateVales(User $user)
    // {
    //     return $user->tienePermiso('vales.update');
    // } */

    // public function updateValesAutocarPensiones(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'vales.update');
    // }

    // public function updateValesAutocarPeriferico(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'vales.update');
    // }

    // public function updateValesRefacciones(User $user)
    // {
    //     return $this->existePermiso($user, 'REFACCIONES', 'vales.update');
    // }

    /* public function pedirModificacion(User $user)
    {
        return $user->tienePermiso('modificacion.pedir');
    } */
    /*____________________________________________________________________________________________________________________ */

    public function pedirModificacionAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'modificacion.pedir');
    }

    public function pedirModificacionAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'modificacion.pedir');
    }

    public function pedirModificacionRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'modificacion.pedir');
    }

    // ENTRADAS
    //READ
    /* public function readEntradas(User $user)
    {
        return $user->tienePermiso('entradas.read');
    } */

    // public function readEntradasAutocarPensiones(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'entradas.read');
    // }

    // public function readEntradasAutocarPeriferico(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'entradas.read');
    // }

    // public function readEntradasRefacciones(User $user)
    // {
    //     return $this->existePermiso($user, 'REFACCIONES', 'entradas.read');
    // }

    // public function writeEntradas(User $user)
    // {
    //     return $user->tienePermiso('entradas.write');
    // }

    // public function updateEntradas(User $user)
    // {
    //     return $user->tienePermiso('entradas.update');
    // }

    // ALBARANES
    //READ
    /* public function readAlbaranes(User $user)
    {
        return $user->tienePermiso('albaranes.read');
    } */

    public function readAlbaranesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'albaranes.read');
    }

    public function readAlbaranesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'albaranes.read');
    }

    public function readAlbaranesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'albaranes.read');
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

    // FACTURAS
    //READ
    /* public function readFacturas(User $user)
    {
        return $user->tienePermiso('facturas.read');
    } */

    // public function readFacturasAutocarPensiones(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'facturas.read');
    // }

    // public function readFacturasAutocarPeriferico(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'facturas.read');
    // }

    // public function readFacturasRefacciones(User $user)
    // {
    //     return $this->existePermiso($user, 'REFACCIONES', 'facturas.read');
    // }

    // public function writeFacturas(User $user)
    // {
    //     return $user->tienePermiso('facturas.write');
    // }

    // public function updateFacturas(User $user)
    // {
    //     return $user->tienePermiso('facturas.update');
    // }
    // /////////////////////////////////
    // // REPORTES
    // //READ
    // /* public function readReportes(User $user)
    // {
    //     return $user->tienePermiso('reportes.read');
    // } */

    // public function readReportesAutocarPensiones(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'reportes.read');
    // }

    // public function readReportesAutocarPeriferico(User $user)
    // {
    //     return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'reportes.read');
    // }

    // public function readReportesRefacciones(User $user)
    // {
    //     return $this->existePermiso($user, 'REFACCIONES', 'reportes.read');
    // }

    // public function writeReportes(User $user)
    // {
    //     return $user->tienePermiso('reportes.write');
    // }

    // public function updateReportes(User $user)
    // {
    //     return $user->tienePermiso('reportes.update');
    // }

    // EVIDENCIAS

    public function writeEvidencias(User $user)
    {
        return $user->tienePermiso(('evidencias.write'));
    }

    public function notificarEvidencias(User $user)
    {
        return $user->tienePermiso('evidencias.notify');
    }

    // PARTES
    //permiso para agregar complemento
    public function writePartes(User $user)
    {
        return $user->tienePermiso(('partes.write'));
    }

    //permiso para modificar partes

    // public function updatePartes(User $user)
    // {
    //     return $user->tienePermiso(('partes.update'));
    // }

    /*Passarlo al modelo que afecta!!! */
    public function updatePartesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'partes.update');
    }

    public function updatePartesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'partes.update');
    }

    public function updatePartesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'partes.update');
    }

    public function deletePartes(User $user)
    {
        return $user->tienePermiso(('partes.delete'));
    }


    public function deletePartesAutocarPensiones(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PENSIONES', 'partes.delete');
    }

    public function deletePartesAutocarPeriferico(User $user)
    {
        return $this->existePermiso($user, 'AUTOCAR PERIFERICO', 'partes.delete');
    }

    public function deletePartesRefacciones(User $user)
    {
        return $this->existePermiso($user, 'REFACCIONES', 'partes.delete');
    }


    //permiso para liberar partes
    public function liberarPartes(User $user)
    {
        return $user->tienePermiso(('partes.liberar'));
    }

    // DESCRIPCION W32  
    public function readDescripcionW32(User $user)
    {
        return $user->tienePermiso('descripcionw32.read');
    }
}
