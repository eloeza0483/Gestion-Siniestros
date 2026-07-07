<?php

use App\Http\Controllers\AlbaranController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Models\Proyectos;
use App\Models\PermisosD;
use App\Models\Aseguradora;
use App\Models\Marcas;
use App\Models\Vehiculo;
use App\Models\Taller;
use App\Http\Controllers\SiniestroController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VehiculoInfoController;
use App\Http\Controllers\MarcasController;
use App\Http\Controllers\TallerController;
use App\Http\Controllers\AseguradoraController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PiezaFirebirdController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\ProcesoVehiculoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SeguimientoTrabajosController;
use App\Http\Controllers\ValeController;
use App\Http\Controllers\PermisoController;
use Illuminate\Support\Facades\Request;

Route::post('/login-api', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');


Route::get('/login', function () {
    return view('login');
})->name('login');


Route::middleware(['auth'])->group(function () {

    Route::controller(HomeController::class)->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('{perfil?}', 'index')->name('home');
    });

    Route::prefix('{perfil?}/siniestros')->controller(SiniestroController::class)->group(function () {
        // Listar todos los siniestros (GET)
        Route::get('/getSiniestros/{id_perfil?}', 'getSiniestros')->name('siniestros.get');
        // Mostrar formulario de creación (GET)
        Route::post('/crear', 'create')->name('siniestros.create');

        // Route::GET('/getInfoPzas/{numOrden}/{tipoModal}', 'getInfoPzas');
        Route::GET('/getInfoPzas/{id_siniestro}/{tipo}', 'getInfoPzas');


        // Obtener siniestro solo por número de orden (sin taller)
        Route::get('/numero-orden/{numeroOrden}', 'getSiniestroByNumOrdenOnly')->name('siniestros.getByNumOrdenOnly');
        // Mostrar detalles de un siniestro (GET)
        Route::get('/{numeroOrden}', 'getSiniestroByNumOrden')->name('siniestros.getByNumOrden');
        Route::get('/{numeroOrden}/{numeroSiniestro}/{taller}', 'validarExistenciaSiniestro')->name('siniestros.validarExistencia');
        // Cancelar un siniestro
        Route::post('/{id}/cancelar', 'cancelar')->name('siniestros.cancelar');
        Route::post('/{id}/notificar-cancelacion', 'notificarCancelacion')->name('siniestros.notificarCancelacion');
        Route::post('/{id}/reabrir', 'reabrir')->name('siniestros.reabrir');
        Route::post('/{id}/cerrar', 'cerrar')->name('siniestros.cerrar');
        //Vista de siniestros — debe ir AL FINAL para no interceptar las rutas anteriores

        Route::get('/', 'index')->name('siniestros.view');
    });

    // Agrupando rutas que utilizan el PresupuestoController con un prefijo
    Route::prefix('{perfil?}/presupuestos')->group(function () {
        Route::get('/consultar-descripcion', [PiezaFirebirdController::class, 'consultarDatosW32'])->name('descripcion.consultar');
        Route::controller(PresupuestoController::class)->group(function () {
            // Obtener siniestros (GET)
            Route::get('/', 'getPresupuestosList')->name('presupuestos.list');
            Route::get('/crear', 'addPresupuestoView')->name('presupuestos.create');
            Route::post('/', 'crearPresupuesto')->name('presupuestos.store');
            Route::get('/ver', 'presupuestoView')->name('presupuesto.ver');
            Route::get('/cotizar', 'cotizarPresupuestoView')->name('presupuestos.cotizacionView');
            Route::post('/cotizar/{presupuesto}', 'cotizarPresupuesto')->name('presupuestos.cotizar');

            // Obtener siniestros (GET)
            Route::get('/get', 'getPresupuestos')->name('presupuestos.get');
            Route::get('/getPresupuestosTalleresChevrolet', 'getPresupuestosTalleresChevrolet')->name('presupuestos.getPresupuestosTalleresChevrolet');
            Route::get('/getPresupuestosTalleresExternos', 'getPresupuestosTalleresExternos')->name('presupuestos.getPresupuestosTalleresExternos');

            //Exportar presupuesto (DEBE IR ANTES DE LA RUTA GENÉRICA /{numero})
            Route::get('/exportPresupuesto/{id}', 'exportPresupuesto')->name('exportarPresupuesto');

            // Gestión de piezas en cotización
            Route::post('/{presupuesto}/agregar-pieza-cotizacion', 'agregarPiezaCotizacion')->name('presupuesto.agregarPiezaCotizacion');
            Route::patch('/{presupuesto}/modificar-pieza-cotizacion/{pieza}', 'modificarPiezaCotizacion')->name('presupuesto.modificarPiezaCotizacion');

            // Ruta genérica (DEBE IR AL FINAL)
            Route::get('/{numero}/{isVale?}', 'getPresupuestoByNumero')->name('presupuesto.getByNum');

            // Checar cuántas piezas disponibles tiene un VALE SEGÚN SU PRESUPUESTO

            //Subir archivos
            Route::post('/subir-evidencias', 'subirEvidencias')->name('evidencias.subir');

            // Enviar correo de cotización (POST)
            Route::post('/mail-cotizacion', 'enviarMailCotizacion')->name('mail.enviar');
        });
    });

    Route::prefix('vehiculos')->group(function () {
        // Listar todos los vehiculos (GET)
        Route::get('/get', [VehiculoController::class, 'getVehiculos'])->name('vehiculos.get');
        // Crear un nuevo vehículo (POST)
        Route::post('/crear', [VehiculoController::class, 'crear'])->name('vehiculos.crear');
    });

    Route::prefix('marcas')->group(function () {
        // Listar todas los marcas (GET)
        Route::get('/get', [MarcasController::class, 'getMarcas'])->name('marcas.get');
        // Crear una nueva marca (POST)
        Route::post('/crear', [MarcasController::class, 'crear'])->name('marcas.crear');
    });

    Route::prefix('talleres')->group(function () {
        // Listar todas los talleres (GET)
        Route::get('/get', [TallerController::class, 'getTalleres'])->name('talleres.get');
    });

    Route::prefix('aseguradoras')->group(function () {
        // Listar todas las aseguradoras (GET)
        Route::get('/get', [AseguradoraController::class, 'getAseguradoras'])->name('aseguradoras.get');
        // Crear una nueva aseguradora (POST)
        Route::post('/crear', [AseguradoraController::class, 'crear'])->name('aseguradoras.crear');
    });

    Route::prefix('clientes')->group(function () {
        Route::post('/crear', [ClienteController::class, 'crear'])->name('clientes.crear');
    });

    Route::get('/get', [AseguradoraController::class, 'getAseguradoras'])->name('aseguradoras.get');

    Route::prefix('{perfil?}/vales')->controller(ValeController::class)->group(function () {
        Route::get('/', 'getValesView')->name('vales.view');
        Route::get('/ver', 'valeView')->name('vale.view');
        Route::get('/asignar', 'asignarValeView')->name('vale.asignar');
        // Listar todos los vales (GET)
        Route::get('/get', 'getVales')->name('vales.get');
        Route::get('/get/{vale}', 'getVale')->name('vale.get');
        Route::get('/get-piezas', 'getPiezasVale')->name('piezasVale.get');
        Route::get('/existsEntradaByValeId/{vale}', 'existsEntradaByValeId')->name('existsEntradaByValeId.get');

        Route::post('/{presupuesto}/agregar-vale', 'agregarVale')->name('vales.agregar');
        Route::post('/asignar-entrada/{vale}', 'asignarEntrada')->name('entrada.asignar');
        Route::post('/asignar-albaran/{vale}', 'asignarAlbaran')->name('albaran.asignar');

        //Exportar vale
        Route::get('/exportVale/{id}', 'exportVale')->name('exportarVale');

        //Validacion
        Route::get('/{numeroVale}', 'validarExistenciaVale')->name('vales.validarExistencia')->where('numeroVale', '[0-9]+');

        Route::post('/agregar-complemento/{vale}', 'agregarComplemento')->name('complemento.agregar');
        Route::post('/notificar-complemento', 'notificarComplemento')->name('complemento.notificar');
        // Cancelar un vale
        Route::post('/{id}/cancelar', 'cancelar')->name('vales.cancelar');
        Route::get('/{presupuesto}/piezas-disponibles', 'getPiezasDisponibles')->name('piezasDisponibles.get');
        Route::post('/mail-vale-creado', 'notificarValeCreado')->name('vale.notificar');
        Route::post('/mail-complemento', 'enviarMailComplemento')->name('complemento.avisar');
        Route::post('/validar-match-albaran/{numVale}', 'validarMatchAlbaran')->name('validarMatchAlbaran');
        Route::post('/validar-match-entrada/{numVale}/{proveedor?}', 'validarMatchEntrada')->name('validarMatchEntrada');
        Route::post('/modificar-parte', 'modificarParte')->name('modificarParteVale');
        Route::post('/eliminar-parte', 'eliminarParte')->name('eliminarParteVale');
        Route::post('/solicitar-eliminar-parte', 'notificarEliminarParteAut')->name('solicitarEliminarParteVale');
        Route::post('/rechazar-solicitud-parte', 'rechazarSolicitudEliminacion')->name('rechazarSolicitudParteVale');
        Route::post('/mail-pedir-modificacion', 'pedirModificacionPartes')->name('parte.pedirModificacion');
    });

    Route::controller(EntradaController::class)->group(function () {
        Route::prefix('{perfil?}/entradas')->group(function () {
            Route::get('/', 'getEntradasView')->name('entradas.view');
            Route::get('/ver', 'entradaView')->name('entrada.ver');
            // Listar todas las entradas (GET)
            Route::get('/get', 'getEntradas')->name('entradas.get');
            Route::get('/w-32/{entrada}', 'consultarEntradaW32')->name('entradaw32.get');
            Route::get('/{entrada}', 'consultarEntrada')->name('entrada.get');
            Route::post('/mail-entrada-asignada', 'notificarEntradaAsignada')->name('entrada.notificar');
            Route::post('/mail-liberacion-partes', 'notificarLiberacionPartes')->name('liberacion.notificar');
            Route::post('/liberar-parte', 'liberarParte')->name('liberarParteEntrada');

            // NUEVO
            Route::get('detalle/{id}', 'detalle')->name('entrada.detalle');
            Route::post('w-32-all/{numeroVale}', 'consultarEntradaW32All')->name('entradaw32all.get');
        });
    });

    Route::prefix('{perfil?}/albaranes')->controller(AlbaranController::class)->group(function () {
        Route::get('/', 'getAlbaranesView')->name('albaranes.view');
        Route::get('/ver', 'albaranView')->name('albaran.ver');
        // Listar todas las entradas (GET)
        Route::get('/get', 'getAlbaranes')->name('albaranes.get');
        Route::get('/{albaran}', 'consultarAlbaran')->name('albaran.get');
        Route::get('/w-32/{albaran}', 'consultarAlbaranW32')->name('albaranw32.get');
        Route::POST('/w-32-all/{numeroVale}', 'consultarAlbaranW32All')->name('albaranw32all.get');

        Route::post('/mail-albaran-asignado', 'notificarAlbaranAsignado')->name('albaran.notificar');
        Route::post('/liberar-parte', 'liberarParte')->name('liberarParteAlbaran');
    });

    Route::prefix('{perfil?}/facturas')->controller(FacturaController::class)->group(function () {
        Route::get('/', 'getFacturasView')->name('facturas.view');
        Route::get('/get', 'getFacturas')->name('facturas.get');
    });

    Route::prefix('{perfil?}/reportes')->controller(ReporteController::class)->group(function () {
        Route::get('/', 'getReportesView')->name('reportes.view');
        Route::get('/get', 'getReportes')->name('reportes.get');
        Route::get('/stats', 'getStats')->name('reportes.stats');
        Route::get('/refacciones', 'getReporteRefaccionesView')->name('reportes.refaccionesView');
        Route::get('/refacciones/get', 'getReporteRefaccionesData')->name('reportes.refaccionesData');
    });

    Route::prefix('{perfil?}/procesos-vehiculos')->controller(ProcesoVehiculoController::class)->group(function () {
        Route::get('/', 'getProcesosVehiculosView')->name('procesosVehiculos.view');
        Route::get('/get', 'getProcesosVehiculos')->name('procesosVehiculos.get');
        Route::post('/cambiar-estado/{id}', 'cambiarEstadoProceso')->name('procesosVehiculos.cambiarEstado');
    });

    Route::prefix('{perfil?}/seguimiento-trabajos')->controller(SeguimientoTrabajosController::class)->group(function () {
        Route::get('/', 'getSeguimientoTrabajosView')->name('seguimientoTrabajos.view');
        Route::get('/get', 'getSeguimientoTrabajos')->name('seguimientoTrabajos.get');
    });

    // Acceso directo sin perfil: /administracion/permisos
    Route::prefix('administracion')->middleware('can:admin-siniestros')->controller(PermisoController::class)->group(function () {
        Route::get('/permisos', 'index')->name('administracion.permisos.view.direct');
        Route::get('/permisos/get-rol-actual-usuario', 'getRolActualUsuario')->name('administracion.permisos.getRolActual.direct');
        Route::get('/permisos/get-permisos-rol-usuario', 'getPermisosRolUsuario')->name('administracion.permisos.getRolUsuario.direct');
        Route::post('/permisos/guardar-permisos', 'guardarPermisos')->name('administracion.permisos.guardar.direct');
        Route::post('/permisos/quitar-rol', 'quitarRolUsuario')->name('administracion.permisos.quitarRol.direct');
    });

    // Acceso con perfil opcional (usado también por los helpers de blade route())
    Route::prefix('{perfil?}/administracion')->middleware('can:admin-siniestros')->controller(PermisoController::class)->group(function () {
        Route::get('/permisos', 'index')->name('administracion.permisos.view');
        Route::get('/permisos/get-rol-actual-usuario', 'getRolActualUsuario')->name('administracion.permisos.getRolActual');
        Route::get('/permisos/get-permisos-rol-usuario', 'getPermisosRolUsuario')->name('administracion.permisos.getRolUsuario');
        Route::post('/permisos/guardar-permisos', 'guardarPermisos')->name('administracion.permisos.guardar');
        Route::post('/permisos/quitar-rol', 'quitarRolUsuario')->name('administracion.permisos.quitarRol');
    });
});
