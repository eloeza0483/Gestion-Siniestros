<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            ['id' => 1, 'nombre' => 'siniestros.view'],
            ['id' => 2, 'nombre' => 'siniestros.write'],
            ['id' => 3, 'nombre' => 'siniestros.update'],
            ['id' => 4, 'nombre' => 'presupuestos.view'],
            ['id' => 5, 'nombre' => 'presupuestos.write'],
            ['id' => 6, 'nombre' => 'presupuestos.update'],
            ['id' => 7, 'nombre' => 'vales.view'],
            ['id' => 8, 'nombre' => 'vales.write'],
            ['id' => 9, 'nombre' => 'vales.update'],
            ['id' => 10, 'nombre' => 'entradas.view'],
            ['id' => 11, 'nombre' => 'entradas.write'],
            ['id' => 12, 'nombre' => 'entradas.update'],
            ['id' => 13, 'nombre' => 'albaranes.view'],
            ['id' => 14, 'nombre' => 'albaranes.write'],
            ['id' => 15, 'nombre' => 'albaranes.update'],
            ['id' => 16, 'nombre' => 'facturas.view'],
            ['id' => 17, 'nombre' => 'facturas.write'],
            ['id' => 18, 'nombre' => 'facturas.update'],
            ['id' => 19, 'nombre' => 'reportes.view'],
            ['id' => 20, 'nombre' => 'reportes.write'],
            ['id' => 21, 'nombre' => 'reportes.update'],
            ['id' => 22, 'nombre' => 'evidencias.write'],
            ['id' => 23, 'nombre' => 'evidencias.notify'],
            ['id' => 24, 'nombre' => 'presupuestos.cotizar'],
            ['id' => 25, 'nombre' => 'pensiones.vertaller'],
            ['id' => 26, 'nombre' => 'periferico.vertaller'],
            ['id' => 27, 'nombre' => 'procesosVehiculos.iniciar'],
            ['id' => 28, 'nombre' => 'procesosVehiculos.pausar'],
            ['id' => 29, 'nombre' => 'procesosVehiculos.reanudar'],
            ['id' => 30, 'nombre' => 'procesosVehiculos.finalizar'],
            ['id' => 31, 'nombre' => 'partes.update'],
            ['id' => 32, 'nombre' => 'partes.write'],
            ['id' => 33, 'nombre' => 'descripcionw32.read'],
            ['id' => 34, 'nombre' => 'modificacion.pedir'],
            ['id' => 35, 'nombre' => 'presupuestos.cotizardirectamente'],
            ['id' => 36, 'nombre' => 'partes.liberar'],
            ['id' => 37, 'nombre' => 'autocar'],
            ['id' => 38, 'nombre' => 'refacciones'],
            ['id' => 39, 'nombre' => 'cotizar.presupuestos.externos'],
            ['id' => 40, 'nombre' => 'cotizar.presupuestos.chevrolet'],
            ['id' => 41, 'nombre' => 'ver.talleres.externos'],
            ['id' => 42, 'nombre' => 'ver.talleres.chevrolet'],
            ['id' => 44, 'nombre' => 'ver.vales.chevrolet'],
            ['id' => 45, 'nombre' => 'partes.delete'],
            ['id' => 46, 'nombre' => 'siniestros.read'],
            ['id' => 47, 'nombre' => 'procesoVehiculo.view'],
            ['id' => 48, 'nombre' => 'seguimientoTrabajo.view'],
        ];

        foreach ($permisos as $permiso) {
            DB::connection('mysql')->table('permisos')->updateOrInsert(
                ['id' => $permiso['id']],
                [
                    'nombre' => $permiso['nombre'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
