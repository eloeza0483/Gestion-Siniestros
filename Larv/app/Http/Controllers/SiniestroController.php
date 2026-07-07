<?php

namespace App\Http\Controllers;

use App\Http\Traits\SiniestroTrait;
use App\Models\Albaran;
use App\Models\Aseguradora;
use App\Models\Cliente;
use App\Models\Entrada;
use App\Models\Marca;
use App\Models\Piezas;
use App\Models\PiezasAlbaran;
use App\Models\PiezasEntrada;
use App\Models\PiezasVale;
use App\Models\Presupuesto;
use Illuminate\Http\Request;
use App\Models\Siniestro;
use App\Models\Perfile;
use App\Models\PerfilUsuario;
use App\Models\Taller;
use App\Models\Vale;
use App\Models\Vehiculo;
use App\Models\VehiculoInfo;
use App\Models\W32;
use App\Services\SiniestroService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailNotificarCancelacionSiniestro;
use App\Mail\MailNotificarCierreSiniestroAutocares;
use App\Mail\MailCancelacionAutocar;
use App\Mail\MailCancelacionRefacciones;
use App\Models\User;

class SiniestroController extends Controller
{
    use SiniestroTrait;
    public function index(Request $request, $perfil = false)
    {

        $nombre_perfil = $this->formatPerfilToPermisos($perfil);
        $siniestro = Siniestro::class;
        $this->authorize("viewSiniestros$nombre_perfil", $siniestro);

        $clientes = Cliente::select('nombre', 'id')->get()->toArray();
        // dd($clientes);
        $servicio = new SiniestroService();

        $id_perfil = $servicio->getIdPerfil($perfil);
        $parametros = ['siniestro' => $siniestro, 'nombre_perfil' => $nombre_perfil ?? '', 'id_perfil' => $id_perfil, 'clientes' => $clientes];
        if (Auth::user()->can("writeSiniestros$nombre_perfil", $siniestro)) {
            $datos = $servicio->getMAV($this->formatPerfilToSQL($perfil));

            $parametros = array_merge($parametros, $datos);
        }
        return view('siniestros', $parametros);
    }

    public function getSiniestros(Request $request, $perfil = null)
    {
        $query = Siniestro::withPresupuestosAndVales();
        $taller = $this->formatPerfilToSQL($perfil);

        // if (isset($taller)) {
        //     // Caso especial: REFACCIONES muestra todos EXCEPTO los AUTOCAR
        if (request()->perfil === 'refacciones') {
            // Refacciones: todos los siniestros con proveedor CHEVROLET
            $query->where(function ($q) {
                $q->whereHas('presupuestos', function ($q2) {
                    $q2->where('proveedor', 'CHEVROLET');
                })->orWhere('perfil_id', $this->id_perfil);
            });
        } else {
            $query->where('perfil_id', $this->id_perfil);
        }
        //     } else {
        //         // Autocar: filtra por su taller específico
        //         $query->whereHas('vehiculoInfo', function ($q) use ($taller) {
        //             $q->where('taller', $taller);
        //         });
        //     }
        // }

        if ($request->has('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        } elseif (!$request->has('estado')) {
            $query->where('estado', 'Abierto');
        }
        // estado=todos => sin filtro de estado (para cálculos de porcentaje por VIN)
        return $query->get();
    }

    public function getSiniestroByNumOrden(Request $request, $Perfil)
    {

        // dd();
        $numeroOrden = $request->route('numeroOrden');
        $numOrden = trim($numeroOrden);
        // $tallerNormalizado = trim(strtoupper(str_replace("_", " ", $taller)));

        //LOS TALLERES EXTERNOS PUEDEN TENER MISMO NUMERO DE ORDEN???? 
        $query = Siniestro::with('vehiculoInfo', 'cliente')
            ->whereHas('vehiculoInfo', function ($q) use ($Perfil) {
                if ($Perfil === 'refacciones') {
                    $q->whereNotIn('taller', ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO']);
                } else {
                    $q->where('taller', str_replace('_', ' ', strtoupper($Perfil)));
                }
            })->where('numero_orden', $numOrden)->first();


        //  $query = Siniestro::with('vehiculoInfo')
        // ->whereHas('vehiculoInfo', function ($q) use ($tallerNormalizado) {
        //     if ($tallerNormalizado === 'REFACCIONES') {
        //         $q->whereNotIn('taller', ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO']);
        //     } else {
        //         $q->where('taller', $tallerNormalizado);
        //     }
        // })->where('numero_orden', $numeroOrdenLimpiado)->first();

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Siniestro no encontrado',
            ], 404);
        }
        return response()->json($query);
    }

    public function getSiniestroByNumOrdenOnly(Request $request)
    {
        $numeroOrden = $request->route('numeroOrden');

        $siniestro = Siniestro::with('vehiculoInfo', 'cliente')
            ->where('numero_orden', trim($numeroOrden))
            ->where('perfil_id', $this->id_perfil)
            ->first();

        if (!$siniestro) {
            return response()->json([
                'success' => false,
                'message' => 'Siniestro no encontrado',
            ], 404);
        }

        return $siniestro;
    }

    public function getInfoPzas($perfil = null, $id_siniestro, $tipoModal)
    {
        try {
            $existeSiniestro = Siniestro::where('id', $id_siniestro)->exists();

            if (!$existeSiniestro) {
                throw new \Exception('Siniestro no encontrado');
            }
            // switch ($tipo) {
            switch ($tipoModal) {
                case "presupuesto":
                    $result = Siniestro::getTPresupuestos($id_siniestro);
                    break;
                case "pzs-autorizadas":
                    $result = Siniestro::getPzasAutorizadas($id_siniestro);
                    break;
                case "pzs-surtidas":
                    $result = Siniestro::getPzasSurtidas($id_siniestro);
                    break;
                case "pzs-recibidas":
                    $result = Siniestro::getPzasRecibidas($id_siniestro);
                    break;
                case "pzs-faltantes":
                    $result = Siniestro::getPzasFaltantes($id_siniestro);
                    break;
                default:
                    $result = Siniestro::getPzasAutorizadas($id_siniestro);
                    break;
            }

            // dd($result);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function validarExistenciaSiniestro($numeroOrden, $numeroSiniestro, $taller)
    {
        $tallerNormalizado = strtoupper(str_replace("_", " ", $taller));

        $queryBase = Siniestro::whereHas('vehiculoInfo', function ($q) use ($tallerNormalizado) {
            if ($tallerNormalizado === 'REFACCIONES') {
                $q->whereNotIn('taller', ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO']);
            } else {
                $q->where('taller', $tallerNormalizado);
            }
        });

        $existeNumeroSiniestro = (clone $queryBase)->where('numero_siniestro', $numeroSiniestro)->exists();
        $existeNumeroOrden = (clone $queryBase)->where('numero_orden', $numeroOrden)->exists();

        return response()->json([
            'numeroOrdenExists' => $existeNumeroOrden,
            'numeroSiniestroExists' => $existeNumeroSiniestro,
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'vin' => 'required|size:17',
        ], [
            'vin.size' => 'El VIN debe tener exactamente 17 caracteres.',
        ]);

        $validatedData = $request->except(['_token']);
        $parametrosVehiculo = $request->only(['vin', 'marca', 'taller', 'modelo', 'vehiculo']);
        $parametrosSiniestro = $request->only(['numero_siniestro', 'numero_orden', 'perfil_id', 'id_cliente']);

        $parametrosVehiculo['vin'] = strtoupper(trim($request->vin));
        if ($request->exists('id_cliente')) {
            $parametrosSiniestro['id_cliente'] = $request->id_cliente;
        } else {
            $parametrosVehiculo['aseguradora'] = $request->aseguradora;
        }

        try {

            $parametrosSiniestro['numero_siniestro'] = strtoupper(trim($request->numero_siniestro));
            $siniestro = DB::transaction(function () use ($parametrosVehiculo, $parametrosSiniestro) {
                // Crear vehiculo antes del siniestro
                $vehiculoInfo = VehiculoInfo::create($parametrosVehiculo);

                $parametrosSiniestro = array_merge($parametrosSiniestro, ['id_vehiculo' => $vehiculoInfo->id, 'id_usuario_registro' => Auth::user()->id]);

                // dd($parametrosSiniestro);
                // Crear el nuevo siniestro
                return Siniestro::create($parametrosSiniestro);
            });


            // Determinar el valor del taller para la respuesta
            $tallerRespuesta = $validatedData['taller'];
            if (!in_array($validatedData['taller'], ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO'])) {
                $tallerRespuesta = 'REFACCIONES';
            }

            // Respuesta JSON para SweetAlert
            return response()->json([
                'success' => true,
                'message' => "Se registro el siniestro <b>{$siniestro->numero_siniestro}</b> correctamente.<br>¿Desea agregarle un presupuesto?",
                'icon' => 'success',
                'confirmButtonText' => 'Continuar',
                'cancelButtonText' => 'Agregar más tarde',
                'numeroOrden' => $siniestro->numero_orden,
                'taller' => $tallerRespuesta,
            ]);
        } catch (\Exception $e) {
            // Respuesta de error para SweetAlert
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'No se pudo crear el siniestro: ' . $e->getMessage(),
                'icon' => 'error', // Icono de error
            ], 500);
        }
    }

    public function cancelar(Request $request, $perfil = null, $id)
    {
        try {
            $motivo    = $request->input('motivo_cancelacion');
            $siniestro = Siniestro::with('vehiculoInfo')->find($id);

            $siniestro->update([
                'estado'             => 'Cancelado',
                'motivo_cancelacion' => $motivo,
            ]);

            // Obtener presupuestos ANTES de cancelarlos para poder leer sus datos
            $presupuestos = Presupuesto::where('id_siniestro', $id)->get();
            Presupuesto::where('id_siniestro', $id)->update(['estado' => 'Cancelado']);

            $todosLosVales = collect();
            foreach ($presupuestos as $presupuesto) {
                $valesPresupuesto = Vale::where('id_presupuesto', $presupuesto->id)->get();
                $todosLosVales = $todosLosVales->merge($valesPresupuesto);
                Vale::where('id_presupuesto', $presupuesto->id)->update(['estado' => 'Cancelado']);
            }

            $entradas = Entrada::where('id_siniestro', $id)->get();
            Entrada::where('id_siniestro', $id)->update(['estado' => 'Cancelado']);

            $albaranes = Albaran::where('id_siniestro', $id)->get();
            Albaran::where('id_siniestro', $id)->update(['estado' => 'Cancelado']);

            // ─── NOTIFICACIONES ────────────────────────────────────────────
            $destinatariosIds = collect();
            $registrosAfectados = [];

            foreach ($presupuestos as $presupuesto) {
                $destinatariosIds->push($presupuesto->id_usuario_creacion);
                $registrosAfectados[] = [
                    'tipo'       => 'Presupuesto',
                    'referencia' => $presupuesto->numero_presupuesto,
                ];
            }

            foreach ($todosLosVales as $vale) {
                $destinatariosIds->push($vale->id_usuario_registro);
                $registrosAfectados[] = [
                    'tipo'       => 'Vale',
                    'referencia' => $vale->numero_vale,
                ];
            }

            foreach ($entradas as $entrada) {
                $destinatariosIds->push($entrada->id_usuario_registro);
                $registrosAfectados[] = [
                    'tipo'       => 'Entrada',
                    'referencia' => $entrada->numero_entrada,
                ];
            }

            foreach ($albaranes as $albaran) {
                $destinatariosIds->push($albaran->id_usuario_registro);
                $registrosAfectados[] = [
                    'tipo'       => 'Albarán',
                    'referencia' => $albaran->numero_albaran,
                ];
            }

            // IDs únicos y con email válido
            $emails = User::whereIn('id', $destinatariosIds->unique()->filter()->values())
                ->whereNotNull('email')
                ->pluck('email')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $esAutocar = in_array(
                strtolower(trim($perfil ?? '')),
                ['autocar_pensiones', 'autocar_periferico', 'autocar pensiones', 'autocar periferico']
            );

            if (!empty($emails) && !empty($registrosAfectados)) {
                try {
                    $mailable = $esAutocar  ? new MailCancelacionAutocar($siniestro, $motivo, $registrosAfectados) : new MailCancelacionRefacciones($siniestro, $motivo, $registrosAfectados);

                    $mail = Mail::to(array_shift($emails));
                    if (!empty($emails)) {
                        $mail->cc($emails);
                    }
                    $mail->send($mailable);
                } catch (\Exception $mailEx) {
                    Log::error('Error al enviar correo cancelación general: ' . $mailEx->getMessage());
                }
            }

            return response()->json([
                'success'   => true,
                'icon'      => 'success',
                'title'     => '¡Éxito!',
                'message'   => 'Siniestro cancelado correctamente',
                'siniestro' => $siniestro,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'icon'    => 'error',
                'title'   => 'Error',
                'message' => 'No se pudo cancelar el siniestro: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function notificarCancelacion(Request $request, $perfil = null, $id)
    {
        try {
            $motivo = $request->input('motivo_cancelacion');
            $siniestro = Siniestro::with('vehiculoInfo')->find($id);

            if (!$siniestro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siniestro no encontrado',
                ], 404);
            }

            // Guardar el motivo en la base de datos para que sea visible en la tabla
            $siniestro->update(['motivo_cancelacion' => $motivo]);

            // Enviar correo
            Mail::to('programador4.ti@grupodc.com.mx')->send(new MailNotificarCancelacionSiniestro($siniestro, $motivo));

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'title' => 'Solicitud Enviada',
                'message' => 'La solicitud de cancelación ha sido enviada por correo.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo enviar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reabrir(Request $request, $perfil = null, $id)
    {
        try {
            // Reabrir siniestro
            $siniestro = Siniestro::find($id);
            $siniestro->update(['estado' => 'Abierto']);

            // Respuesta JSON para SweetAlert
            return response()->json([
                'success' => true,
                'title' => '¡Éxito!',
                'message' => 'Siniestro re abierto correctamente',
                'icon' => 'success', // Icono de éxito
                'siniestro' => $siniestro,
            ]);
        } catch (\Exception $e) {
            // Respuesta de error para SweetAlert
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'No se pudo re abrir el siniestro: ' . $e->getMessage(),
                'icon' => 'error', // Icono de error
            ], 500);
        }
    }

    public function cerrar(Request $request, $perfil = null, $id)
    {

        //validacion w32 albaranes
        //validacion full albaranes
        //validacion full entradas

        try {

            $siniestro = Siniestro::with('vehiculoInfo')->find($id);

            if (!$siniestro) {
                return response()->json([
                    'success' => false,
                    'icon'    => 'error',
                    'title'   => 'Error',
                    'message' => "No se encontró el siniestro con ID: $id",
                ], 404);
            }
            // Si el taller es Autocar, validar que el vehículo esté finalizado
            $esAutocar = in_array(strtoupper(trim($siniestro->vehiculoInfo->taller)), ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO']);

            if ($esAutocar) {
                $estadoVehiculo = $siniestro->vehiculoInfo->estado ?? 'Pendiente';
                if ($estadoVehiculo !== 'Finalizado') {
                    return response()->json([
                        'success' => false,
                        'icon'    => 'warning',
                        'title'   => 'Vehículo en proceso',
                        'message' => "No se puede cerrar el siniestro porque el vehículo está en estado: <b>{$estadoVehiculo}</b>.",
                    ], 422);
                }
            }

            // Obtener el id_w32 del perfil para verificar en Firebird
            $perfilModel = Perfile::where('id', $siniestro->perfil_id)->first();
            $id_w32 = $perfilModel->id_w32 ?? null;

            // Ya tenemos $esAutocar definido arriba

            $factura = null;

            if ($esAutocar) {
                // Validación habitual para AUTOCARES
                if ($id_w32 && $siniestro->numero_orden) {
                    $factura = W32::isFacturadoByNumOrdenAC($siniestro->numero_orden, $id_w32);
                    if (!$factura) {
                        return response()->json([
                            'success' => false,
                            'icon'    => 'warning',
                            'title'   => 'Orden sin facturar',
                            'message' => "La orden {$siniestro->numero_orden} no ha sido facturada.",
                        ], 422);
                    }
                }
            } else {
                // Validación para REFACCIONES (Usa los albaranes directamente a Montecristo)
                $albaranes = Albaran::where('id_siniestro', $id)->where('estado', '!=', 'Cancelado')->get();

                if ($albaranes->isNotEmpty()) {
                    foreach ($albaranes as $albaran) {
                        $facturaAlbaran = W32::isFacturadoByAlbaranRef($albaran->numero_albaran);
                        if (!$facturaAlbaran) {
                            return response()->json([
                                'success' => false,
                                'icon'    => 'warning',
                                'title'   => 'Albarán sin facturar',
                                'message' => "El albarán {$albaran->numero_albaran} no ha sido facturado.",
                            ], 422);
                        }
                        $factura = $facturaAlbaran;
                    }
                }
            }


            // Validar que todas las piezas de cada vale estén cubiertas
            $vales = Vale::with('presupuestos')
                ->whereHas('presupuestos', fn($q) => $q->where('id_siniestro', $id))
                ->where('estado', '!=', 'Cancelado')
                ->get();

            $pendientes = [];

            foreach ($vales as $vale) {
                $piezasVale  = PiezasVale::where('id_vale', $vale->id)->get();

                foreach ($piezasVale as $pv) {
                    // Sumar cantidad recibida en albaranes (activos y no cancelados)
                    $surtidaAlbaran = PiezasAlbaran::whereHas(
                        'albaran',
                        fn($q) =>
                        $q->where('id_vale', $vale->id)->where('estado', '!=', 'Cancelado')
                    )->where('id_pieza', $pv->id_pieza)->where('activo', 1)->sum('cantidad');

                    // Sumar cantidad recibida en entradas (activas y no canceladas)
                    $surtidaEntrada = PiezasEntrada::whereHas(
                        'entrada',
                        fn($q) =>
                        $q->where('id_vale', $vale->id)->where('estado', '!=', 'Cancelado')
                    )->where('id_pieza', $pv->id_pieza)->where('activo', 1)->sum('cantidad');

                    $totalSurtido = $surtidaAlbaran + $surtidaEntrada;

                    if ($totalSurtido < $pv->cantidad) {
                        $pendientes[] = [
                            'vale'      => $vale->numero_vale,
                            'faltantes' => $pv->cantidad - $totalSurtido,
                        ];
                        break;
                    }
                }
            }

            if (count($pendientes) > 0) {
                $detalle = collect($pendientes)
                    ->map(fn($p) => "Vale {$p['vale']}: {$p['faltantes']} pieza(s) pendiente(s)")
                    ->join('<br>');

                return response()->json([
                    'success' => false,
                    'icon'    => 'warning',
                    'title'   => 'Piezas pendientes',
                    'message' => "No se puede cerrar. Faltan piezas por cubrir:<br>$detalle",
                ], 422);
            }

            DB::beginTransaction();
            $siniestro->update(['estado' => 'Cerrado']);
            $presupuestos = Presupuesto::where('id_siniestro', $id)->get();
            Presupuesto::where('id_siniestro', $id)->update(['estado' => 'Cotizado']);

            foreach ($presupuestos as $presupuesto) {
                Vale::where('id_presupuesto', $presupuesto->id)->update(['estado' => 'Cerrado']);
            }

            // Entrada::where('id_siniestro', $id)->update(['estado' => 'Facturado']);
            // Albaran::where('id_siniestro', $id)->update(['estado' => 'Facturado']);
            DB::commit();

            // Notificación por correo para Autocares
            Log::info("Vehiculo taller: " . ($siniestro->vehiculoInfo->taller ?? 'null') . " - esAutocar: " . ($esAutocar ? 'true' : 'false'));
            if ($esAutocar) {
                try {
                    Log::info("Enviando correo de cierre a programador4.ti@grupodc.com.mx para orden: " . $siniestro->numero_orden);
                    Mail::to('programador4.ti@grupodc.com.mx')->send(new MailNotificarCierreSiniestroAutocares($siniestro));
                    Log::info("Correo enviado exitosamente.");
                } catch (\Exception $e) {
                    Log::error("Error al enviar correo de cierre de siniestro autocares: " . $e->getMessage());
                    throw $e; // Lanzamos la excepción para verla en pantalla si falla
                }
            }

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'title' => '¡Éxito!',
                'message' => 'Siniestro cerrado correctamente',
                'siniestro' => $siniestro,
                'factura' => $factura ?: null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo cerrar el siniestro: ' . $e->getMessage(),
            ], 500);
        }
    }
}
