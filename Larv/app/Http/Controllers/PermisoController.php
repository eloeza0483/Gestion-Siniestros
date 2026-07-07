<?php

namespace App\Http\Controllers;

use App\Models\PermisosD;
use App\Models\Proyectos;
use App\Models\Rol;
use App\Models\PermisoLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function index($perfilActivo = null)
    {
        $usuarios = DB::connection('mysqlGTI')->table('usersAD')->where('activo', 1)->select('id', 'fullname')->get();
        $roles = DB::connection('mysql')->table('roles')->where('activo', 1)->select('id', 'rol', 'id_perfil')->get();
        $permisosRaw = DB::connection('mysql')->table('permisos')->select('id', 'nombre')->get();

        $labels = $this->getPermisoLabels();

        $permisos = $permisosRaw->map(function ($p) use ($labels) {
            return (object)[
                'id'     => $p->id,
                'nombre' => $labels[$p->nombre] ?? $p->nombre,
                'slug'   => $p->nombre
            ];
        });

        $perfiles = DB::connection('mysql')->table('perfiles')->where('activo', 1)->select('id', 'nombre')->get();

        return view('administracion.permisos', compact('perfilActivo', 'usuarios', 'roles', 'permisos', 'perfiles'));
    }

    private function getPermisoLabels()
    {
        return [
            'siniestros.view'           => 'Ver Siniestros',
            'siniestros.write'          => 'Registrar Siniestros',
            'siniestros.update'         => 'Eliminar Siniestros (Modificar)',
            'siniestros.read'           => 'Consultar Siniestros',
            'vales.view'                => 'Ver Vales',
            'vales.write'               => 'Registrar Vales',
            'vales.update'              => 'Editar Vales',
            'entradas.view'             => 'Ver Entradas',
            'entradas.write'            => 'Registrar Entradas',
            'entradas.update'           => 'Editar Entradas',
            'entradas.delete'           => 'Eliminar Entradas',
            'entradas.read'             => 'Consultar Entradas',
            'albaranes.view'            => 'Ver Albaranes',
            'albaranes.write'           => 'Registrar Albaranes',
            'albaranes.update'          => 'Editar Albaranes',
            'presupuestos.view'         => 'Ver Presupuestos',
            'presupuestos.write'        => 'Registrar Presupuestos',
            'presupuestos.update'       => 'Editar Presupuestos',
            'presupuestos.cotizar'      => 'Cotizar Presupuestos',
            'presupuestos.cotizardirectamente' => 'Cotizar Directamente',
            'procesosVehiculos.iniciar' => 'Iniciar Procesos',
            'procesosVehiculos.pausar'  => 'Pausar Procesos',
            'procesosVehiculos.reanudar' => 'Reanudar Procesos',
            'procesosVehiculos.finalizar' => 'Finalizar Procesos',
            'procesoVehiculo.view'      => 'Ver Procesos de Vehículos',
            'reportes.view'             => 'Ver Reportes',
            'reportes.write'            => 'Generar Reportes',
            'reportes.update'           => 'Actualizar Reportes',
            'seguimientoTrabajo.view'   => 'Ver Seguimiento de Trabajo',
            'ver.talleres.chevrolet'    => 'Ver Talleres Chevrolet',
            'ver.talleres.externos'     => 'Ver Talleres Externos',
            'ver.vales.chevrolet'       => 'Ver Vales Chevrolet',
            'partes.write'              => 'Registrar Partes',
            'partes.update'             => 'Editar Partes',
            'partes.delete'             => 'Eliminar Partes',
            'partes.liberar'            => 'Liberar Partes',
            'facturas.view'             => 'Ver Facturas',
            'facturas.write'            => 'Registrar Facturas',
            'facturas.update'           => 'Editar Facturas',
            'evidencias.write'          => 'Subir Evidencias',
            'evidencias.notify'         => 'Notificar Evidencias',
            'cotizar.presupuestos.chevrolet' => 'Cotizar Presupuestos Chevrolet',
            'cotizar.presupuestos.externos'  => 'Cotizar Presupuestos Externos',
            'descripcionw32.read'       => 'Consultar Descripción W32',
            'modificacion.pedir'        => 'Solicitar Modificación',
            'pensiones.vertaller'       => 'Ver Taller Pensiones',
            'periferico.vertaller'      => 'Ver Taller Periférico',
            'autocar'                   => 'Perfil Autocar',
            'refacciones'               => 'Perfil Refacciones',
            'compras.admin'             => 'Administrar Compras',
        ];
    }

    /**
     * Devuelve el rol actualmente asignado a un usuario en GTI (permiso_desarrollos),
     * enriquecido con el nombre del rol y el perfil (sucursal) al que pertenece.
     */
    public function getRolActualUsuario(Request $request)
    {
        $idUsuario = $request->input('id_usuario');

        if (!$idUsuario) {
            return response()->json(['rol' => null]);
        }

        $idProy = Proyectos::where('nombre', 'GestionSiniestros')->first();

        if (!$idProy) {
            return response()->json(['rol' => null]);
        }

        // Buscar el registro en GTI
        $asignacion = DB::connection('mysqlGTI')
            ->table('permiso_desarrollos')
            ->where('id_usuario', $idUsuario)
            ->where('id_proyecto', $idProy->id)
            ->where('activo', 1)
            ->first();


        if (!$asignacion || !$asignacion->id_rol) {
            return response()->json(['rol' => null]);
        }

        // Cruzar con la tabla roles local para obtener nombre y perfil
        $rol = DB::connection('mysql')
            ->table('roles')
            ->where('id', $asignacion->id_rol)
            ->first();

        if (!$rol) {
            return response()->json(['rol' => null]);
        }

        $rolesSecundarios = [];

        if (!empty($asignacion->opciones)) {
            $id_roles_secundarios = explode(",", $asignacion->opciones);

            foreach ($id_roles_secundarios as $id_rol_secundario) {
                $rolSecundarioObj = DB::connection('mysql')
                    ->table('roles')
                    ->where('id', $id_rol_secundario)
                    ->first();

                if ($rolSecundarioObj) {
                    $perfilSecundario = DB::connection('mysql')
                        ->table('perfiles')
                        ->where('id', $rolSecundarioObj->id_perfil)
                        ->first();

                    $rolesSecundarios[] = [
                        'id' => $rolSecundarioObj->id,
                        'nombre' => $rolSecundarioObj->rol,
                        'id_perfil' => $rolSecundarioObj->id_perfil,
                        'perfil' => $perfilSecundario ? $perfilSecundario->nombre : 'Sin sucursal'
                    ];
                }
            }
        }

        // Obtener el nombre del perfil (sucursal)
        $perfil = DB::connection('mysql')
            ->table('perfiles')
            ->where('id', $rol->id_perfil)
            ->first();


        return response()->json([
            'rol' => [
                'id'         => $rol->id,
                'nombre'     => $rol->rol,
                'id_perfil'  => $rol->id_perfil,
                'perfil'     => $perfil ? $perfil->nombre : null,
                'rolesSecundarios' => $rolesSecundarios
            ]
        ]);
    }

    /**
     * Dado un usuario, un rol y un perfil, devuelve todos los permisos del sistema
     * y cuáles ya están activos para esa combinación.
     */
    public function getPermisosRolUsuario(Request $request)
    {
        $idUsuario = $request->input('id_usuario');
        $idRol     = $request->input('id_rol');
        $idPerfil  = $request->input('id_perfil');

        if (!$idUsuario || !$idRol || !$idPerfil) {
            return response()->json(['error' => 'Faltan parámetros'], 422);
        }

        // Todos los permisos disponibles
        $todosPermisos = DB::connection('mysql')->table('permisos')->select('id', 'nombre')->get();

        // 1. Permisos que vienen de la tabla roles (mysql) - VALIDANDO QUE EL ROL PERTENEZCA AL PERFIL
        $rol = DB::connection('mysql')
            ->table('roles')
            ->where('id', $idRol)
            ->where('id_perfil', $idPerfil) // Validamos perfil
            ->first();

        $permisosDelRol = $rol && $rol->id_permisos ? json_decode($rol->id_permisos, true) : [];

        // 2. ¿El usuario ya tiene este rol asignado en permiso_desarrollos (GTI)?
        $idProy = Proyectos::where('nombre', 'GestionSiniestros')->first();
        $idProyecto = $idProy->id;

        $asignacionGTI = DB::connection('mysqlGTI')
            ->table('permiso_desarrollos')
            ->where('id_usuario', $idUsuario)
            ->where('id_proyecto', $idProyecto)
            ->where('activo', 1)
            ->first();

        $tieneRolAsignadoGTI = false;

        if ($asignacionGTI) {
            if ($asignacionGTI->id_rol == $idRol) {
                $tieneRolAsignadoGTI = true;
            }

            if (!empty($asignacionGTI->opciones)) {
                $secundarios = array_filter(array_map('trim', explode(',', $asignacionGTI->opciones)));
                if (in_array((string)$idRol, $secundarios)) {
                    $tieneRolAsignadoGTI = true; // El rol seleccionado es al menos secundario
                }
            }
        }

        // Los permisos fijos (heredados/bloqueados en la UI) son ÚNICAMENTE los del rol seleccionado.
        $permisosFijos = $permisosDelRol;

        // 3. Permisos ya guardados en permisos_usuarios (mysql local) para este usuario y perfil (solo activos)
        $permisosGuardados = DB::connection('mysql')
            ->table('permisos_usuarios')
            ->where('user_id', $idUsuario)
            ->where('perfil_id', $idPerfil)
            ->where('activo', 1)
            ->pluck('permiso_id')
            ->toArray();

        $labels = $this->getPermisoLabels();

        // Construir respuesta
        $resultado = $todosPermisos->map(function ($permiso) use ($permisosFijos, $permisosGuardados, $rol, $labels) {
            $esDelRol = in_array($permiso->id, $permisosFijos);
            $nombreAmigable = $labels[$permiso->nombre] ?? $permiso->nombre;

            return [
                'id'     => $permiso->id,
                'nombre' => $nombreAmigable,
                'slug'   => $permiso->nombre,
                'activo' => $esDelRol || in_array($permiso->id, $permisosGuardados),
                'es_del_rol' => $esDelRol,
            ];
        })->values();

        return response()->json([
            'permisos'        => $resultado,
            'tiene_asignado_gti'  => $tieneRolAsignadoGTI,
            'rol_valido_perfil' => (bool)$rol // Informamos si el rol coincide con el perfil
        ]);
    }

    /**
     * Guarda todos los permisos seleccionados en la tabla permisos_usuarios para un usuario y perfil específico.
     */
    public function guardarPermisos(Request $request)
    {

        // dd($request->all());
        $idUsuario = $request->input('id_usuario');
        $idRol     = $request->input('id_rol');
        $idPerfil  = $request->input('id_perfil');
        $modo      = $request->input('modo', 'principal'); // 'principal' | 'secundario'
        // Aseguramos que el array de permisos sea único para evitar duplicados en el insert
        $idPermisos = array_unique($request->input('permisos', []));

        if (!$idUsuario || !$idPerfil || !$idRol) {
            return response()->json(['success' => false, 'message' => 'Faltan datos obligatorios'], 422);
        }

        try {
            DB::connection('mysql')->transaction(function () use ($idUsuario, $idPerfil, $idRol, $idPermisos, $modo) {
                // 1. Sincronización Local (permisos_usuarios)

                // Obtener los permisos que vienen del rol seleccionado (el frontend los envía disabled,
                // por eso no llegan en $idPermisos — los recuperamos del JSON del rol aquí).
                $rol = DB::connection('mysql')
                    ->table('roles')
                    ->where('id', $idRol)
                    ->where('id_perfil', $idPerfil)
                    ->first();

                $permisosDelRol = ($rol && !empty($rol->id_permisos))
                    ? json_decode($rol->id_permisos, true)
                    : [];

                if (!is_array($permisosDelRol)) $permisosDelRol = [];

                // Fusionar permisos del rol + permisos custom enviados desde el frontend
                $todosLosPermisos = array_unique(array_merge($idPermisos, $permisosDelRol));

                // Desactivar todos los permisos del usuario+perfil que NO estén en la lista combinada
                if (empty($todosLosPermisos)) {
                    DB::connection('mysql')
                        ->table('permisos_usuarios')
                        ->where('user_id', $idUsuario)
                        ->where('perfil_id', (string)$idPerfil)
                        ->update(['activo' => 0, 'updated_at' => now()]);
                } else {
                    DB::connection('mysql')
                        ->table('permisos_usuarios')
                        ->where('user_id', $idUsuario)
                        ->where('perfil_id', (string)$idPerfil)
                        ->whereNotIn('permiso_id', $todosLosPermisos)
                        ->update(['activo' => 0, 'updated_at' => now()]);
                }

                // Para cada permiso (del rol + custom): si existe lo activa, si no lo inserta
                foreach ($todosLosPermisos as $permisoId) {
                    if (!$permisoId) continue;

                    $existe = DB::connection('mysql')
                        ->table('permisos_usuarios')
                        ->where('user_id', $idUsuario)
                        ->where('perfil_id', (string)$idPerfil)
                        ->where('permiso_id', $permisoId)
                        ->first();

                    if ($existe) {
                        DB::connection('mysql')
                            ->table('permisos_usuarios')
                            ->where('id', $existe->id)
                            ->update(['activo' => 1, 'updated_at' => now()]);
                    } else {
                        DB::connection('mysql')->table('permisos_usuarios')->insert([
                            'user_id'    => $idUsuario,
                            'perfil_id'  => (string)$idPerfil,
                            'permiso_id' => $permisoId,
                            'activo'     => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // 2. Sincronización de Perfiles Activos (perfil_usuarios local)
                $perfilUsuarioRepo = DB::connection('mysql')->table('perfil_usuarios')->where('user_id', $idUsuario)->first();

                if ($perfilUsuarioRepo) {
                    $perfilesActuales = array_filter(array_map('trim', explode(',', $perfilUsuarioRepo->perfil_id)));

                    if (!in_array((string)$idPerfil, $perfilesActuales)) {
                        $perfilesActuales[] = (string)$idPerfil;

                        DB::connection('mysql')
                            ->table('perfil_usuarios')
                            ->where('id', $perfilUsuarioRepo->id)
                            ->update([
                                'perfil_id'  => implode(',', array_unique($perfilesActuales)),
                                'updated_at' => now()
                            ]);
                    }
                } else {
                    DB::connection('mysql')->table('perfil_usuarios')->insert([
                        'user_id'    => $idUsuario,
                        'perfil_id'  => (string)$idPerfil,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // 3. Sincronización Remota (GTI - permiso_desarrollos)
                $idProy = Proyectos::where('nombre', 'GestionSiniestros')->first();
                $idProyecto = $idProy->id;

                $existeGTI = DB::connection('mysqlGTI')
                    ->table('permiso_desarrollos')
                    ->where('id_usuario', $idUsuario)
                    ->where('id_proyecto', $idProyecto)
                    ->first();

                $rolesActuales = [];
                $rolPrincipalActual = $existeGTI ? $existeGTI->id_rol : null;
                $opcionesActuales = $existeGTI && $existeGTI->opciones
                    ? array_filter(array_map('trim', explode(',', $existeGTI->opciones)))
                    : [];

                if ($rolPrincipalActual) {
                    $rolesActuales[] = $rolPrincipalActual;
                }
                $rolesActuales = array_unique(array_merge($rolesActuales, $opcionesActuales));

                // Obtener perfiles de los roles actuales para la regla "Un rol por perfil"
                $rolesConPerfil = [];
                if (!empty($rolesActuales)) {
                    $rolesDB = DB::connection('mysql')->table('roles')->whereIn('id', $rolesActuales)->get();
                    foreach ($rolesDB as $r) {
                        $rolesConPerfil[$r->id] = $r->id_perfil;
                    }
                }

                $nuevosSecundarios = [];
                $nuevoPrincipal = $rolPrincipalActual;

                if ($modo === 'principal') {
                    $nuevoPrincipal = $idRol;
                    // El antiguo principal pasa a ser secundario SI era de OTRO perfil
                    if ($rolPrincipalActual && $rolPrincipalActual != $idRol && isset($rolesConPerfil[$rolPrincipalActual]) && $rolesConPerfil[$rolPrincipalActual] != $idPerfil) {
                        $nuevosSecundarios[] = $rolPrincipalActual;
                    }
                }

                // Conservar secundarios que NO sean del mismo perfil que el nuevo rol
                foreach ($opcionesActuales as $sec) {
                    if (isset($rolesConPerfil[$sec]) && $rolesConPerfil[$sec] != $idPerfil && $sec != $nuevoPrincipal) {
                        $nuevosSecundarios[] = $sec;
                    }
                }

                if ($modo === 'secundario') {
                    // Si es secundario, verificar si reemplazó al principal (porque tenían el mismo perfil)
                    if ($rolPrincipalActual && isset($rolesConPerfil[$rolPrincipalActual]) && $rolesConPerfil[$rolPrincipalActual] == $idPerfil) {
                        // Reemplaza al principal, así que este nuevo rol DEBE ser el principal
                        $nuevoPrincipal = $idRol;
                    } else {
                        // Es un secundario en un perfil donde no teníamos el principal
                        $nuevosSecundarios[] = $idRol;
                    }
                }

                // Si no hay principal por alguna razón, el primero de los secundarios pasa a ser principal
                if (!$nuevoPrincipal && count($nuevosSecundarios) > 0) {
                    $nuevoPrincipal = array_shift($nuevosSecundarios);
                }

                // Generar string para el campo opciones
                $opcionesStr = count($nuevosSecundarios) > 0 ? implode(',', array_unique($nuevosSecundarios)) : null;

                if ($existeGTI) {
                    DB::connection('mysqlGTI')
                        ->table('permiso_desarrollos')
                        ->where('id', $existeGTI->id)
                        ->update([
                            'id_rol'     => $nuevoPrincipal,
                            // 'band_uno'   => $nuevoPrincipal, // requerimiento original de GTI
                            'opciones'   => $opcionesStr,
                            'activo'     => 1,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::connection('mysqlGTI')
                        ->table('permiso_desarrollos')
                        ->insert([
                            'id_usuario'  => $idUsuario,
                            'id_proyecto' => $idProyecto,
                            'id_rol'      => $nuevoPrincipal,
                            // 'band_uno'    => $nuevoPrincipal,   
                            'opciones'    => $opcionesStr,
                            'activo'      => 1,
                            'created_at'  => now(),
                            'updated_at'  => now()
                        ]);
                }

                // Registrar en bitácora
                PermisoLog::create([
                    'id_usuario'          => auth()->id(),
                    'id_usuario_afectado' => $idUsuario,
                    'accion'              => $modo === 'principal' ? 'ASIGNAR_ROL_PRINCIPAL' : 'ASIGNAR_ROL_SECUNDARIO',
                    'campo_modificado'    => "Perfil: $idPerfil",
                    'valor_anterior'      => json_encode(['rol_anterior' => $rolPrincipalActual]),
                    'valor_nuevo'         => json_encode(['id_rol' => $nuevoPrincipal, 'permisos_custom_guardados' => count($idPermisos)])
                ]);
            });

            $mensaje = 'Roles y permisos sincronizados correctamente.';

            return response()->json(['success' => true, 'message' => $mensaje]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al guardar permisos: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Quita un rol específico del usuario en GTI y desactiva sus permisos asociados.
     */
    public function quitarRolUsuario(Request $request)
    {
        $idUsuario = $request->input('id_usuario');
        $idRol     = $request->input('id_rol');
        $idPerfil  = $request->input('id_perfil');

        if (!$idUsuario || !$idRol || !$idPerfil) {
            return response()->json(['success' => false, 'message' => 'Faltan datos obligatorios'], 422);
        }

        try {
            DB::connection('mysql')->transaction(function () use ($idUsuario, $idRol, $idPerfil) {
                // 1. Quitar el rol de GTI
                $idProy = Proyectos::where('nombre', 'GestionSiniestros')->first();
                $existeGTI = DB::connection('mysqlGTI')
                    ->table('permiso_desarrollos')
                    ->where('id_usuario', $idUsuario)
                    ->where('id_proyecto', $idProy->id)
                    ->first();

                if ($existeGTI) {
                    $nuevoPrincipal = $existeGTI->id_rol;
                    $opciones = $existeGTI->opciones ? array_filter(array_map('trim', explode(',', $existeGTI->opciones))) : [];

                    $eraPrincipal = ($nuevoPrincipal == $idRol);

                    if ($eraPrincipal) {
                        // Si era el principal, el siguiente secundario pasa a ser principal
                        $nuevoPrincipal = count($opciones) > 0 ? array_shift($opciones) : null;
                    } else {
                        // Si era secundario, simplemente quitarlo de las opciones
                        $opciones = array_filter($opciones, fn($r) => $r != $idRol);
                    }

                    $opcionesStr = count($opciones) > 0 ? implode(',', array_unique($opciones)) : null;

                    if ($nuevoPrincipal) {
                        DB::connection('mysqlGTI')
                            ->table('permiso_desarrollos')
                            ->where('id', $existeGTI->id)
                            ->update([
                                'id_rol'     => $nuevoPrincipal,
                                'opciones'   => $opcionesStr,
                                'updated_at' => now()
                            ]);
                    } else {
                        // Si ya no tiene ningún rol, desactivar el registro
                        DB::connection('mysqlGTI')
                            ->table('permiso_desarrollos')
                            ->where('id', $existeGTI->id)
                            ->update([
                                'id_rol'     => null,
                                'opciones'   => null,
                                'activo'     => 0,
                                'updated_at' => now()
                            ]);
                    }
                } else {
                    $eraPrincipal  = false;
                    $nuevoPrincipal = null;
                }

                // 2. Desactivar los permisos del ROL QUITADO en permisos_usuarios
                // Obtenemos los permisos que pertenecen al rol que se está quitando
                $rolQuitado = DB::connection('mysql')
                    ->table('roles')
                    ->where('id', $idRol)
                    ->first();

                $permisosDelRolQuitado = ($rolQuitado && !empty($rolQuitado->id_permisos))
                    ? json_decode($rolQuitado->id_permisos, true)
                    : [];

                if (!empty($permisosDelRolQuitado)) {
                    // Solo desactivar los permisos que provienen del rol quitado
                    DB::connection('mysql')
                        ->table('permisos_usuarios')
                        ->where('user_id', $idUsuario)
                        ->where('perfil_id', (string)$idPerfil)
                        ->whereIn('permiso_id', $permisosDelRolQuitado)
                        ->update(['activo' => 0, 'updated_at' => now()]);
                } else {
                    // Si el rol no tenía permisos definidos, desactivar todos los del perfil
                    DB::connection('mysql')
                        ->table('permisos_usuarios')
                        ->where('user_id', $idUsuario)
                        ->where('perfil_id', (string)$idPerfil)
                        ->update(['activo' => 0, 'updated_at' => now()]);
                }

                // 3. Si el secundario fue promovido a principal, activar sus permisos locales
                if ($nuevoPrincipal) {
                    $rolNuevoPrincipal = DB::connection('mysql')
                        ->table('roles')
                        ->where('id', $nuevoPrincipal)
                        ->first();

                    $permisosNuevoPrincipal = ($rolNuevoPrincipal && !empty($rolNuevoPrincipal->id_permisos))
                        ? json_decode($rolNuevoPrincipal->id_permisos, true)
                        : [];

                    // Obtener el perfil del nuevo rol principal (puede ser distinto al $idPerfil)
                    $idPerfilNuevoPrincipal = $rolNuevoPrincipal->id_perfil ?? $idPerfil;

                    foreach ($permisosNuevoPrincipal as $permId) {
                        DB::connection('mysql')->table('permisos_usuarios')->updateOrInsert(
                            ['user_id' => $idUsuario, 'permiso_id' => $permId, 'perfil_id' => (string)$idPerfilNuevoPrincipal],
                            ['activo' => 1, 'updated_at' => now()]
                        );
                    }
                }

                // 4. Quitar el perfil de perfil_usuarios (solo si ya no tiene rol en ese perfil)
                if (!$nuevoPrincipal || ($rolQuitado && $rolQuitado->id_perfil == $idPerfil && (!$nuevoPrincipal || ($rolNuevoPrincipal ?? null)?->id_perfil != $idPerfil))) {
                    $perfilUsuarioRepo = DB::connection('mysql')->table('perfil_usuarios')->where('user_id', $idUsuario)->first();

                    if ($perfilUsuarioRepo) {
                        $perfilesActuales = array_filter(array_map('trim', explode(',', $perfilUsuarioRepo->perfil_id)));
                        $perfilesActuales = array_filter($perfilesActuales, fn($p) => $p != $idPerfil);

                        if (count($perfilesActuales) > 0) {
                            DB::connection('mysql')
                                ->table('perfil_usuarios')
                                ->where('id', $perfilUsuarioRepo->id)
                                ->update([
                                    'perfil_id'  => implode(',', array_unique($perfilesActuales)),
                                    'updated_at' => now()
                                ]);
                        } else {
                            DB::connection('mysql')
                                ->table('perfil_usuarios')
                                ->where('id', $perfilUsuarioRepo->id)
                                ->delete();
                        }
                    }
                }

                // Registrar en bitácora
                PermisoLog::create([
                    'id_usuario'          => auth()->id(),
                    'id_usuario_afectado' => $idUsuario,
                    'accion'              => 'QUITAR_ROL',
                    'campo_modificado'    => "Perfil: $idPerfil",
                    'valor_anterior'      => json_encode(['rol_quitado' => $idRol]),
                    'valor_nuevo'         => 'Rol y permisos desactivados'
                ]);

            });

            return response()->json(['success' => true, 'message' => 'Rol removido correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al quitar rol: ' . $e->getMessage()], 500);
        }
    }
}
