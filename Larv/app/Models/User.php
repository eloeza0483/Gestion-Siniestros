<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $connection = 'mysqlGTI';
    protected $table = 'usersAD';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'activo',
    ];

    public function permisos()
    {
        return $this->hasOne(PermisosD::class, 'id_usuario', 'id');
    }

    public function tienePermiso(string $permisoNombre)
    {
        $permiso = Permisos::where('nombre', $permisoNombre)->first();
        if (!$permiso) return false;

        // 1. Verificar en permisos_usuarios (permisos custom activos)
        $tieneCustom = DB::connection('mysql')
            ->table('permisos_usuarios')
            ->where('user_id', $this->id)
            ->where('permiso_id', $permiso->id)
            ->where('activo', 1)
            ->exists();

        if ($tieneCustom) return true;

        // 2. Verificar en el JSON id_permisos de los roles asignados en GTI
        $idProy = Proyectos::where('nombre', 'GestionSiniestros')->first();
        if (!$idProy) return false;

        $asignacion = DB::connection('mysqlGTI')
            ->table('permiso_desarrollos')
            ->where('id_usuario', $this->id)
            ->where('id_proyecto', $idProy->id)
            ->where('activo', 1)
            ->first();

        if (!$asignacion) return false;

        $rolesIds = [];
        if ($asignacion->id_rol) {
            $rolesIds[] = $asignacion->id_rol;
        }
        if (!empty($asignacion->opciones)) {
            $secundarios = array_filter(array_map('trim', explode(',', $asignacion->opciones)));
            $rolesIds = array_merge($rolesIds, $secundarios);
        }

        if (empty($rolesIds)) return false;

        $roles = DB::connection('mysql')
            ->table('roles')
            ->whereIn('id', $rolesIds)
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

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id');
    }

    public function udn()
    {
        return $this->belongsTo(Udn::class, 'id_udn', 'id');
    }

    public function accionesPermitidas()
    {
        return DB::connection('mysql')
            ->table('permisos')
            ->join('permisos_usuarios', 'permisos.id', '=', 'permisos_usuarios.permiso_id')
            ->where('permisos_usuarios.user_id', $this->id)
            ->where('permisos_usuarios.activo', 1)
            ->pluck('permisos.nombre'); // Devuelve una lista de nombres de permisos
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // public function permisos_usuario()
    // {
    //     return DB::connection('mysql')
    //         ->table('permisos')
    //         ->join('permisos_usuarios', 'permisos.id', '=', 'permisos_usuarios.permiso_id')
    //         ->where('permisos_usuarios.user_id', $this->id)->get();
    // }
}
