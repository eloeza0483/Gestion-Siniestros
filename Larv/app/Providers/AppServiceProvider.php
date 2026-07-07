<?php

namespace App\Providers;

use App\Http\Traits\SiniestroTrait;
use App\Models\Albaran;
use App\Models\Entrada;
use App\Models\Factura;
use App\Models\Piezas;
use App\Models\Presupuesto;
use App\Models\Reporte;
use App\Models\Siniestro;
use App\Models\Vale;
use App\Models\Proyectos;
use App\Models\VehiculoInfo;
use App\Observers\PiezasObserver;
use App\Policies\VehiculoInfoPolicy;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    use SiniestroTrait;


    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }


    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Gate::policy(VehiculoInfo::class, VehiculoInfoPolicy::class);

        // Observer de auditoría para piezas de presupuesto
        Piezas::observe(PiezasObserver::class);

        // Gate para el Panel de Administración de Permisos (Requerimiento: band_dos == 1 en GTI)
        Gate::define('admin-siniestros', function ($user) {
            $idProy = Proyectos::where('nombre', 'GestionSiniestros')->first();
            return DB::connection('mysqlGTI')->table('permiso_desarrollos')
                ->where('id_usuario', $user->id)
                ->where('id_proyecto', $idProy->id)
                ->where('band_dos', 1)
                ->where('activo', 1)
                ->exists();
        });

        $directivas = [
            'accesoSiniestro'          => ['permiso' => 'viewSiniestros',         'modelo' => Siniestro::class],
            'accesoPresupuesto'        => ['permiso' => 'viewPresupuestos',        'modelo' => Presupuesto::class],
            'accesoVales'              => ['permiso' => 'viewVales',               'modelo' => Vale::class],
            'accesoEntradas'           => ['permiso' => 'viewEntradas',            'modelo' => Entrada::class],
            'accesoAlbaranes'          => ['permiso' => 'viewAlbaranes',           'modelo' => Albaran::class],
            'accesoFacturas'           => ['permiso' => 'viewFacturas',            'modelo' => Factura::class],
            'accesoReportes'           => ['permiso' => 'viewReportes',            'modelo' => Reporte::class],
            'accesoProcesosVehiculo'   => ['permiso' => 'viewProcesosVehiculo',     'modelo' => VehiculoInfo::class],
            'accesoSeguimientoTrabajo' => ['permiso' => 'viewSeguimientoTrabajo',    'modelo' => VehiculoInfo::class],
            'accesoAdminSiniestros'    => ['gate'    => 'admin-siniestros'],
        ];

        foreach ($directivas as $nombre => $config) {
            Blade::if($nombre, function () use ($config) {
                if (!auth()->check()) return false;

                // Si es un Gate directo
                if (isset($config['gate'])) {
                    return Gate::allows($config['gate']);
                }

                // Si es un permiso basado en perfil y modelo
                $permiso = $config['permiso'];
                $modelo  = $config['modelo'];

                $perfil_raw = request()->route('perfil') ?? request()->segment(1);
                $nombre_perfil = SiniestroTrait::formatPerfilPolicy($perfil_raw);

                return auth()->user()->can("{$permiso}{$nombre_perfil}", $modelo);
            });
        }
    }
}
