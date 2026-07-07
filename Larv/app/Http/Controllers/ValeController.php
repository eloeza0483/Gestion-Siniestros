<?php

namespace App\Http\Controllers;

use App\Exports\ValesExport;
use App\Http\Traits\SiniestroTrait;
use App\Mail\MailComplemento;
use App\Mail\MailComplementoAgregado;
use App\Mail\MailPedirModificacionParte;
use App\Mail\MailSolicitudEliminacion;
use App\Mail\MailSolicitudRechazada;
use App\Mail\MailValeCreado;
use App\Models\User;
use App\Models\Albaran;
use App\Models\Vale;
use App\Models\Presupuesto;
use App\Models\Aseguradora;
use App\Models\Entrada;
use App\Models\Marca;
use App\Models\Piezas;
use App\Models\PiezasAlbaran;
use App\Models\PiezasEntrada;
use App\Models\PiezasVale;
use App\Models\Siniestro;
use App\Models\Vehiculo;
use App\Models\Perfile;
use App\Models\PermisosUsuarios;
use App\Services\MailLinkResolver;
use FFI;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ValeController extends Controller
{
    use SiniestroTrait;

    protected function queryValePorNumeroEnPerfil($numeroVale, $idVale = null)
    {
        $query = Vale::query()
            ->where('numero_vale', $numeroVale);

        if ($idVale) {
            $query->where('id', $idVale);
        }

        if ((int) $this->id_perfil === 3) {
            $query->whereHas('presupuestos', function ($q) {
                $q->where('proveedor', 'CHEVROLET');
            });
        } else {
            $query->whereHas('presupuestos.siniestros', function ($q) {
                $q->where('perfil_id', $this->id_perfil);
            });
        }

        return $query;
    }

    protected function buildValeLinkPorPerfil($numeroVale, $idVale = null)
    {
        $perfil = request()->perfil ? request()->perfil . '/' : '';
        $query = '?numVale=' . urlencode((string) $numeroVale);
        if ($idVale) {
            $query .= '&idVale=' . urlencode((string) $idVale);
        }

        return url($perfil . 'vales/ver') . $query;
    }

    public function getVales(Request $request, $perfil)
    {
        $id_perfil = $this->id_perfil;

        $query = Vale::with([
            'presupuestos.siniestros.vehiculoInfo',
            'presupuestos.siniestros.cliente',
            'usuarioRegistro',
            'piezas' => function ($query) {
                $query->where('piezas_vales.activo', true)
                    ->where('piezas.activo', true);
            }
        ]);

        // dd($id_perfil);
        if ($id_perfil === 3) {
            // Si es REFACCIONES (ID 3), filtra los vales con proveedor CHEVROLET
            $query->whereHas('presupuestos', function ($q) {
                $q->where('proveedor', 'CHEVROLET');
            });
        } else {
            // Autocar: filtra por su perfil_id específico en el siniestro
            $query->whereHas('presupuestos.siniestros', function ($q) use ($id_perfil) {
                $q->where('perfil_id', $id_perfil);
            });
        }

        if ($request->has('estado') && $request->estado !== '') {
            if ($request->estado === 'Todos') {
                // Si envían "Todos", tal vez no hacer filtro extra
            } else {
                $query->where('estado', $request->estado);
            }
        } else {
            // Comportamiento por defecto
            $query->where('estado', '!=', 'Cancelado');
        }

        // Si externos='true', filtrar por talleres externos (proveedor != CHEVROLET)
        // Si externos es null o no existe, filtrar por CHEVROLET
        // if ($request->chevrolet == 'true') {
        //     $query->whereHas('presupuestos', function ($q) {
        //         $q->where('proveedor', '=', 'CHEVROLET');
        //     });
        // }

        return $query->get();
    }

    public function getVale(Request $request, $perfil = null, $numeroVale)
    {
        //checkear e
        // dd($numeroVale);
        $valeQuery = $this->queryValePorNumeroEnPerfil($numeroVale, $request->query('idVale'));
        $vale = $valeQuery->firstOrFail();

        $vale->load([
            'piezas' => function ($query) use ($vale) {
                $query->where('piezas.activo', true);
                if ($vale->estado !== 'Cancelado') {
                    $query->where('piezas_vales.activo', true);
                }
            },
            'piezas.vales',
            'presupuestos.siniestros.vehiculoInfo',
            'presupuestos.siniestros.cliente',
            'entradas' => function ($query) {
                $query->where('estado', '!=', 'Cancelado');
            },
            'albaranes' => function ($query) {
                $query->where('estado', '!=', 'Cancelado');
            }
        ]);
        // Calcular surtidoAlbaranPorParte desde la BD Laravel
        $surtidoAlbaranPorParte = [];
        foreach ($vale->albaranes as $albaran) {
            $piezasAlbaran = PiezasAlbaran::where('id_albaran', $albaran->id)
                ->where('activo', 1)
                ->with('piezas')
                ->get();

            foreach ($piezasAlbaran as $piezaAlbaran) {
                $numeroParte = trim($piezaAlbaran->piezas->numero_parte ?? '');
                if ($numeroParte) {
                    if (!isset($surtidoAlbaranPorParte[$numeroParte])) {
                        $surtidoAlbaranPorParte[$numeroParte] = 0;
                    }
                    $surtidoAlbaranPorParte[$numeroParte] += $piezaAlbaran->cantidad;
                }
            }
        }

        // Calcular surtidoEntradaPorParte desde la BD Laravel
        $surtidoEntradaPorParte = [];
        foreach ($vale->entradas as $entrada) {
            $piezasEntrada = PiezasEntrada::where('id_entrada', $entrada->id)
                ->where('activo', 1)
                ->with('piezas')
                ->get();

            foreach ($piezasEntrada as $piezaEntrada) {
                $numeroParte = trim($piezaEntrada->piezas->numero_parte ?? '');
                if ($numeroParte) {
                    if (!isset($surtidoEntradaPorParte[$numeroParte])) {
                        $surtidoEntradaPorParte[$numeroParte] = 0;
                    }
                    $surtidoEntradaPorParte[$numeroParte] += $piezaEntrada->cantidad;
                }
            }
        }

        // Agregar los datos calculados al objeto vale
        $vale->surtidoAlbaranPorParte = $surtidoAlbaranPorParte;
        $vale->surtidoEntradaPorParte = $surtidoEntradaPorParte;

        // Cargar nombres de usuarios que solicitaron eliminación
        $userIds = [];
        foreach ($vale->piezas as $pieza) {
            if (!empty($pieza->pivot->id_usuario_solicita_eliminacion)) {
                $userIds[] = $pieza->pivot->id_usuario_solicita_eliminacion;
            }
        }
        $usuariosNombres = \App\Models\User::whereIn('id', array_unique($userIds))->pluck('name', 'id');

        foreach ($vale->piezas as $pieza) {
            if (!empty($pieza->pivot->id_usuario_solicita_eliminacion)) {
                $pieza->pivot->usuario_solicita_nombre = $usuariosNombres[$pieza->pivot->id_usuario_solicita_eliminacion] ?? 'Desconocido';
                if ($pieza->pivot->fecha_solicitud_eliminacion) {
                    $pieza->pivot->fecha_solicitud_eliminacion_formateada = \Carbon\Carbon::parse($pieza->pivot->fecha_solicitud_eliminacion)->format('d/m/Y H:i');
                }
            }
        }

        return [
            "vale" => $vale,
            "permisos" => PermisosUsuarios::permisosAuth($this->id_perfil)
        ];
    }


    public function getValesView()
    {
        return view('vales');
    }

    public function valeView($perfil = null)
    {

        $perfil = $this->formatPerfilToPermisos($perfil);
        $aseguradoras = Aseguradora::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $vehiculos = Vehiculo::orderBy('nombre')->get();
        $talleres = Perfile::orderBy('nombre')->get();

        return view('verVale', [
            'aseguradoras' => $aseguradoras,
            'marcas' => $marcas,
            'vehiculos' => $vehiculos,
            'talleres' => $talleres,
            'perfil' => $perfil,
        ]);

        // return response()->json([$aseguradoras, $marcas, $vehiculos, $talleres]);
    }

    public function asignarValeView()
    {
        $aseguradoras = Aseguradora::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $vehiculos = Vehiculo::orderBy('nombre')->get();
        $talleres = Perfile::orderBy('nombre')->get();

        // $perfil = session('perfil_activo');
        return view('administrarVales', [
            'aseguradoras' => $aseguradoras,
            'marcas' => $marcas,
            'vehiculos' => $vehiculos,
            'talleres' => $talleres,

            // 'perfil' => $perfil
        ]);
    }

    public function existsEntradaByValeId($id)
    {
        $entrada = $this->queryValePorNumeroEnPerfil($id, request()->query('idVale'))->with('entradas')->first();
        // dd($entrada);
        return $entrada;
    }

    public function agregarVale(Request $request, $perfil, $numeroPresupuesto)
    {
        // dd($request);
        $validatedData = $request->except(['_token']);
        try {
            $presupuesto = Presupuesto::with('siniestros')->where('numero_presupuesto', $numeroPresupuesto)->first();

            if (!$presupuesto) {
                return response()->json([
                    'success' => false,
                    'title' => 'Presupuesto no encontrado',
                    'message' => 'No se encontró el presupuesto especificado.',
                    'icon' => 'error',
                ], 404);
            }

            $idPerfilPresupuesto = $presupuesto->siniestros?->perfil_id;

            $valeDuplicado = Vale::where('numero_vale', $validatedData['numero_vale'])
                ->where('estado', '!=', 'Cancelado')
                ->whereHas('presupuestos.siniestros', function ($q) use ($idPerfilPresupuesto) {
                    $q->where('perfil_id', $idPerfilPresupuesto);
                })
                ->exists();

            if ($valeDuplicado) {
                return response()->json([
                    'success' => false,
                    'title' => 'Vale duplicado',
                    'message' => 'El número de vale ya existe en este perfil.',
                    'icon' => 'warning',
                ], 422);
            }

            // Si el presupuesto o su siniestro están en estado "Completado", reabrirlos
            if ($presupuesto) {
                // Reabrir vales del presupuesto que estén Completados
                // Vale::where('id_presupuesto', $presupuesto->id)
                //     ->where('estado', 'Completado')
                //     ->update(['estado' => 'Abierto']);

                // Reabrir el siniestro si también está Completado
                $siniestro = Siniestro::find($presupuesto->id_siniestro);
                if ($siniestro && $siniestro->estado === 'Completado') {
                    $siniestro->estado = 'Abierto';
                    $siniestro->save();
                }
            }

            $vale = Vale::create([
                'numero_vale' => $validatedData['numero_vale'],
                'id_presupuesto' => $presupuesto->id,
                'estado' => 'Abierto',
                'subtotal' => $validatedData['subtotal'],
                'iva' => $validatedData['iva'],
                'total' => $validatedData['total'],
                'fecha_vale' => $validatedData['fecha_vale'],
                'fecha_promesa' => $validatedData['fecha_promesa'],
                'id_usuario_registro' => Auth::user()->id
            ]);

            foreach ($validatedData['filasSeleccionadas'] as $pieza) {
                PiezasVale::create([
                    'id_vale' => $vale->id,
                    'id_pieza' => $pieza['id_pieza'], // Asegúrate de que este campo exista en filasSeleccionadas
                    'cantidad' => $pieza['cantidad_pieza'],
                ]);
            }

            return response()->json([
                'success' => true,
                'title' => '¡Éxito!',
                'message' => 'Vale añadido correctamente',
                'icon' => 'success', // Icono de éxito
                'vale' => $vale,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'No se pudo agregar el vale: ' . $e->getMessage(),
                'icon' => 'error', // Icono de error
            ], 500);
        }
    }

    public function cancelar(Request $request, $perfil = null, $id)
    {
        // dd($perfil);
        try {
            $nombre_perfil = $this->formatPerfilToPermisos($perfil);
            $this->authorize("cancelVales$nombre_perfil", Vale::class);

            // Cancelar vale y sus relaciones
            $vale = Vale::with('piezas', 'albaranes', 'entradas', 'presupuestos')->where('id', $id)->first();

            if ($vale->albaranes()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'title' => '¡Error!',
                    'message' => 'No se puede cancelar el vale porque tiene albaranes asociados',
                    'icon' => 'error', // Icono de error
                    'vale' => $vale,
                ]);
            }

            if ($vale->entradas()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'title' => '¡Error!',
                    'message' => 'No se puede cancelar el vale porque tiene entradas asociadas',
                    'icon' => 'error', // Icono de error
                    'vale' => $vale,
                ]);
            }

            if ($vale->piezas()->count() > 0) {
                foreach ($vale->piezas as $pieza) {
                    // Desactivar la relación en la tabla pivote piezas_vales
                    $vale->piezas()->updateExistingPivot($pieza->id, ['activo' => 0]);
                }
            }
            // if($vale->albaranes->count() > 0){
            //     foreach ($vale->albaranes as $albaran) {
            //     $albaran->update(['estado' => 'Cancelado']);

            //     PiezasAlbaran::where('id_albaran', $albaran->id)->update(['activo' => 0]);
            // }

            // foreach ($vale->entradas as $entrada) {
            //     $entrada->update(['estado' => 'Cancelado']);

            //     PiezasEntrada::where('id_entrada', $entrada->id)->update(['activo' => 0]);
            // }

            $vale->update([
                'estado' => 'Cancelado',
                'motivo_cancelacion' => $request->input('motivo'),
                'id_usuario_cancelacion' => Auth::id(),
                'fecha_cancelacion' => now()
            ]);

            // Notificar al usuario que cotizó el presupuesto si el vale es cancelado por Autocar y el proveedor es CHEVROLET
            if (str_contains(strtolower($perfil), 'autocar') && $vale->presupuestos && strtoupper($vale->presupuestos->proveedor) === 'CHEVROLET') {
                $usuario = User::find($vale->presupuestos->id_usuario_cotizacion);
                if ($usuario && !empty($usuario->email)) {
                    $mailLinkResolver = app(\App\Services\MailLinkResolver::class);
                    $datos = [
                        'numeroPresupuesto' => $vale->presupuestos->numero_presupuesto,
                        'numVale' => $vale->numero_vale,
                        'linkVale' => $mailLinkResolver->buildValeLink($vale->presupuestos, $vale->numero_vale),
                        'motivo' => $request->input('motivo') ?? 'Sin motivo especificado'
                    ];
                    Mail::to($usuario->email)->send(new \App\Mail\MailValeCancelado($datos));
                }
            }

            // Respuesta JSON para SweetAlert
            return response()->json([
                'success' => true,
                'title' => '¡Éxito!',
                'message' => 'Vale cancelado correctamente',
                'icon' => 'success', // Icono de éxito
                'vale' => $vale,
            ]);
        } catch (\Exception $e) {
            // Respuesta de error para SweetAlert
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'No se pudo cancelar el vale: ' . $e->getMessage(),
                'icon' => 'error', // Icono de error
            ], 500);
        }
    }

    public function getPiezasDisponibles($perfil, $numeroPresupuesto = null)
    {
        $presupuesto = Presupuesto::with('piezas.vales')->where('numero_presupuesto', $numeroPresupuesto)->first();

        if (!$presupuesto) {
            return response()->json([
                'error' => true,
                'mensaje' => 'No se encontró un presupuesto con el número proporcionado.'
            ], 404);
        }

        $piezasDisponibles = [];

        foreach ($presupuesto->piezas as $pieza) {
            // Solo sumar las cantidades de los vales donde la pieza esté ACTIVA
            $totalAsignado = $pieza->vales->where('pivot.activo', true)->sum('pivot.cantidad');
            //    $totalAsignado = $pieza->vales->sum(function ($vale) {
            //     return $vale->pivot->cantidad;
            // })

            $restante = $pieza->numero_pzas_presupuesto - $totalAsignado;

            if ($restante > 0) {
                $pieza->restante_para_vale = $restante;
                $piezasDisponibles[] = $pieza;
            }
        }

        // dd($piezasDisponibles);

        return response()->json($piezasDisponibles);
    }

    public function asignarEntrada(Request $request, $perfil = null, $vale)
    {

        // dd($request);
        $validatedData = $request->except(['_token']);

        $vale = $this->queryValePorNumeroEnPerfil($vale, $request->input('id_vale'))
            ->with('presupuestos.siniestros.vehiculoInfo')
            ->first();
        if (!$vale) {
            return response()->json([
                'success' => false,
                'title' => 'Vale no encontrado',
                'message' => 'No se encontró el vale especificado.',
                'icon' => 'error',
            ], 404);
        }

        $piezasVale = PiezasVale::with('piezasPresupuesto')->where('id_vale', $vale->id)->get();

        // El siniestro válido es el que pertenece al vale seleccionado
        $siniestro = $vale->presupuestos?->siniestros;
        if (!$siniestro) {
            return response()->json([
                'success' => false,
                'title' => 'Siniestro no encontrado',
                'message' => 'No se encontró el siniestro asociado al vale seleccionado.',
                'icon' => 'error',
            ], 404);
        }

        $numeroSiniestroRequest = trim((string) $request->input('numero_siniestro_asignacion'));
        if ($numeroSiniestroRequest !== '' && trim((string) $siniestro->numero_siniestro) !== $numeroSiniestroRequest) {
            return response()->json([
                'success' => false,
                'title' => 'Siniestro inválido',
                'message' => 'La entrada solo puede asignarse al siniestro asociado al vale seleccionado.',
                'icon' => 'warning',
            ], 422);
        }

        // Obtener el taller del siniestro actual
        $tallerActual = $siniestro->vehiculoInfo->taller ?? null;

        // Validar que la entrada no esté duplicada para el mismo taller
        $entradaExistente = Entrada::where('numero_entrada', $validatedData['entrada'])
            ->whereHas('siniestros.vehiculoInfo', function ($q) use ($tallerActual) {
                $q->where('taller', $tallerActual);
            })
            ->first();

        if ($entradaExistente) {
            return response()->json([
                'success' => false,
                'title' => 'Entrada duplicada',
                'message' => 'Esta entrada ya fue asignada para este taller',
                'icon' => 'warning',
                'entrada' => $entradaExistente,
            ]);
        }

        $entrada = Entrada::create([
            'numero_entrada' => $validatedData['entrada'],
            'id_vale' => $vale->id,
            'id_siniestro' => $siniestro->id,
            'importe' => round($validatedData['importe'] / 1.16, 2),
            'total'   => $validatedData['importe'],
            'estado' => 'Activo',
            'id_usuario_registro' => Auth::user()->id,
        ]);

        if (isset($validatedData['piezas'])) {

            foreach ($validatedData['piezas'] as $pieza) {
                // Buscar la piezaVale cuyo piezasPresupuesto->numero_parte coincida con el numero de parte
                $piezaVale = $piezasVale->first(function ($pv) use ($pieza) {
                    return $pv->piezasPresupuesto && $pv->piezasPresupuesto->numero_parte == $pieza['numero_parte'];
                });

                if ($piezaVale) {
                    PiezasEntrada::create([
                        'id_entrada' => $entrada->id,
                        'id_pieza' => $piezaVale->id_pieza,
                        'cantidad' => $pieza['cantidad']
                    ]);
                }
            }
        }

        // 1. Verificar si el Vale está completado
        // Para AUTOCAR solo cuentan las entradas como surtido definitivo
        $esAutocar = in_array($tallerActual, ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO'], true);

        $todasLasPiezasDelValeRecibidas = $piezasVale->every(function ($pv) use ($vale, $esAutocar) {
            // Sumar cantidad recibida en entradas para esta pieza en este vale
            $cantidadEnEntradas = PiezasEntrada::whereHas('entrada', function ($q) use ($vale) {
                $q->where('id_vale', $vale->id)->where('estado', '!=', 'Cancelado');
            })->where('id_pieza', $pv->id_pieza)
                ->where('activo', 1)
                ->sum('cantidad');

            // AUTOCAR: solo entradas cuentan como surtido definitivo
            if ($esAutocar) {
                return $cantidadEnEntradas >= $pv->cantidad;
            }

            // Otros talleres: sumar entradas + albaranes
            $cantidadEnAlbaranes = PiezasAlbaran::whereHas('albaran', function ($q) use ($vale) {
                $q->where('id_vale', $vale->id)->where('estado', '!=', 'Cancelado');
            })->where('id_pieza', $pv->id_pieza)
                ->where('activo', 1)
                ->sum('cantidad');

            return ($cantidadEnEntradas + $cantidadEnAlbaranes) >= $pv->cantidad;
        });

        if ($todasLasPiezasDelValeRecibidas) {
            $vale->estado = 'Completado';
            $vale->fecha_surtido = now();
            $vale->save();
        }

        // 2. Verificar si el Siniestro está completado
        // Refrescamos el siniestro con el scope que calcula pzs_faltantes
        $siniestroConScope = Siniestro::withPresupuestosAndVales()->find($siniestro->id);

        // Si pzs_faltantes <= 0, entonces ya se recibió todo lo autorizado
        // Nota: pzs_faltantes viene del scopeWithPresupuestosAndVales en Siniestro.php
        if ($siniestroConScope && $siniestroConScope->pzs_faltantes <= 0) {
            $siniestro->estado = 'Completado';
            $siniestro->save();
        }

        return response()->json([
            'success' => true,
            'title' => '¡Éxito!',
            'message' => 'Entrada asignada correctamente',
            'icon' => 'success',
            'entrada' => $entrada,
        ]);
    }

    public function asignarAlbaran(Request $request, $perfil = null, $vale)
    {

        // dd($request->all());
        $validatedData = $request->except(['_token']);

        // dd($validatedData);

        $vale = $this->queryValePorNumeroEnPerfil($vale, $request->input('id_vale'))
            ->with('presupuestos.siniestros.vehiculoInfo')
            ->first();
        if (!$vale) {
            return response()->json([
                'success' => false,
                'title' => 'Vale no encontrado',
                'message' => 'No se encontró el vale especificado.',
                'icon' => 'error',
            ], 404);
        }

        $piezasVale = PiezasVale::with('piezasPresupuesto')->where('id_vale', $vale->id)->get();
        // El siniestro válido es el que pertenece al vale seleccionado
        $siniestro = $vale->presupuestos?->siniestros;
        if (!$siniestro) {
            return response()->json([
                'success' => false,
                'title' => 'Siniestro no encontrado',
                'message' => 'No se encontró el siniestro asociado al vale seleccionado.',
                'icon' => 'error',
            ], 404);
        }

        $numeroSiniestroRequest = trim((string) $request->input('numero_siniestro_asignacion'));
        if ($numeroSiniestroRequest !== '' && trim((string) $siniestro->numero_siniestro) !== $numeroSiniestroRequest) {
            return response()->json([
                'success' => false,
                'title' => 'Siniestro inválido',
                'message' => 'El albarán solo puede asignarse al siniestro asociado al vale seleccionado.',
                'icon' => 'warning',
            ], 422);
        }


        $tallerActual = $siniestro->vehiculoInfo->taller ?? null;

        // Validar que el albarán no esté duplicado para el mismo taller
        $albaranDuplicado = Albaran::where('numero_albaran', $request->albaran)
            ->whereHas('siniestros.vehiculoInfo', function ($q) use ($tallerActual) {
                $q->where('taller', $tallerActual);
            })
            ->first();

        if ($albaranDuplicado) {
            return response()->json([
                'success' => false,
                'title' => 'Albaran duplicado',
                'message' => 'Este albaran ya fue asignado para este taller',
                'icon' => 'warning',
                'albaran' => $albaranDuplicado,
            ]);
        }

        $albaran = Albaran::create([
            'numero_albaran' => $validatedData['albaran'],
            'id_vale' => $vale->id,
            'id_siniestro' => $siniestro->id,
            'importe' => round($validatedData['importe'] / 1.16, 2),
            'total'   => $validatedData['importe'],
            'estado' => 'Activo',
            'id_usuario_registro' => Auth::user()->id,
        ]);

        foreach ($validatedData['piezas'] as $pieza) {
            // Buscar la piezaVale cuyo piezasPresupuesto->numero_parte coincida con la referencia
            $piezaVale = $piezasVale->first(function ($pv) use ($pieza) {
                $ref = isset($pieza['referencia']) ? trim($pieza['referencia']) : '';
                return $pv->piezasPresupuesto && trim($pv->piezasPresupuesto->numero_parte) == $ref;
            });

            if ($piezaVale) {
                PiezasAlbaran::create([
                    'id_albaran' => $albaran->id,
                    'id_pieza' => $piezaVale->id_pieza,
                    'cantidad' => $pieza['cantidad']
                ]);
            }
        }

        // Verificar si todas las piezas del vale están cubiertas al 100% (Albaranes + Entradas)
        $todasCubiertas = true;
        foreach ($piezasVale as $piezaVale) {
            // Sumar la cantidad de la pieza en todos los albaranes relacionados a este vale
            $cantidadEnAlbaranes = PiezasAlbaran::whereHas('albaran', function ($q) use ($vale) {
                $q->where('id_vale', $vale->id)->where('estado', '!=', 'Cancelado');
            })->where('id_pieza', $piezaVale->id_pieza)
                ->where('activo', 1)
                ->sum('cantidad');

            // Sumar la cantidad de la pieza en todas las entradas relacionadas a este vale
            $cantidadEnEntradas = PiezasEntrada::whereHas('entrada', function ($q) use ($vale) {
                $q->where('id_vale', $vale->id)->where('estado', '!=', 'Cancelado');
            })->where('id_pieza', $piezaVale->id_pieza)
                ->where('activo', 1)
                ->sum('cantidad');

            // Si el total recibido es menor que lo solicitado, no está cubierta
            if (($cantidadEnAlbaranes + $cantidadEnEntradas) < $piezaVale->cantidad) {
                $todasCubiertas = false;
                break;
            }
        }

        // Restricción: No autocompletar en albaranes para AUTOCAR (esperan entradas)
        if ($todasCubiertas && !in_array($tallerActual, ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO'])) {
            $vale->estado = 'Completado';
            $vale->fecha_surtido = now();
            $vale->save();
        }

        // 2. Verificar si el Siniestro está completado
        // Para AUTOCAR, el siniestro no puede completarse solo con albaranes (aún faltan entradas)
        if (!in_array($tallerActual, ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO'])) {
            $siniestroConScope = Siniestro::withPresupuestosAndVales()->find($siniestro->id);

            if ($siniestroConScope && $siniestroConScope->pzs_faltantes <= 0) {
                $siniestro->estado = 'Completado';
                $siniestro->save();
            }
        }

        return response()->json([
            'success' => true,
            'title' => '¡Éxito!',
            'message' => 'Albaran asignado correctamente',
            'icon' => 'success',
            'albaran' => $albaran,
            'vale_cerrado' => $todasCubiertas,
        ]);
    }

    public function agregarComplemento(Request $request, $vale)
    {

        // dd($request);
        $validatedData = $request->except(['_token']);

        $vale = $this->queryValePorNumeroEnPerfil($vale, $request->input('id_vale'))->first();

        if ($vale && ($vale->estado === 'Cerrado' || $vale->estado === 'Completado')) {
            $vale->update([
                'estado' => 'Abierto'
            ]);
        }

        $complemento = Piezas::create([
            'numero_parte' => $request['numero_parte'],
            'descripcion' => $request['descripcion'],
            'descripcion_w32' => $request['descripcion_w32'],
            'numero_pzas_presupuesto' => $request['cantidad'],
            'importe_unitario' => $request['importe_unitario'],
            'importe_total' => $request['importe_total'], // Multiplicación aquí
            'id_presupuesto' => $vale->id_presupuesto,
            'tiempoentrega' => "1a3dias",
            'existencia' => $request['existencia'],
            'es_complemento' => 1
        ]);
        $piezaVale = PiezasVale::create([
            'id_vale' => $vale->id,
            'id_pieza' => $complemento->id,
            'cantidad' => $request['cantidad'],
        ]);




        return response()->json([
            'success' => true,
            'title' => '¡Éxito!',
            'message' => 'Complemento agregado correctamente',
            'icon' => 'success',
            'complemento' => $complemento,
        ]);
    }

    public function enviarMailComplemento(Request $request)
    {

        $datos = [
            'numVale' => $request->input('numVale'),
            'descripcion' => $request->input('descripcion'),
            'cantidad' => $request->input('cantidad'),
            'linkVale' => $request->input('link')
        ];

        // dd($datos);

        Mail::to('programador4.ti@grupodc.com.mx')->send(new MailComplemento($datos));

        return response()->json([
            'success' => true,
            'title' => '¡Éxito!',
            'message' => 'Correo enviado exitosamente',
            'icon' => 'success'
        ]);
    }

    public function notificarValeCreado(Request $request, MailLinkResolver $mailLinkResolver)
    {
        try {
            $validatedData = $request->validate([
                'numero_presupuesto' => 'required|string',
                'numero_vale' => 'required',
            ]);

            $presupuesto = Presupuesto::with('siniestros')
                ->where('numero_presupuesto', $validatedData['numero_presupuesto'])
                ->firstOrFail();

            $datos = [
                'numeroPresupuesto' => $presupuesto->numero_presupuesto,
                'numVale' => $validatedData['numero_vale'],
                'linkVale' => $request->input('link')
                    ?: $mailLinkResolver->buildValeLink($presupuesto, $validatedData['numero_vale'])
            ];

            $usuario = User::findOrFail($presupuesto->id_usuario_cotizacion);
            Mail::to($usuario->email)->send(new MailValeCreado($datos));

            return response()->json([
                'success' => true,
                'title' => '¡Éxito!',
                'message' => 'Correo enviado exitosamente',
                'icon' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'title' => 'Error al enviar correo',
                'message' => 'No se pudo notificar la creación del vale: ' . $e->getMessage(),
                'icon' => 'error',
            ], 500);
        }
    }

    public function exportVale(Request $request, $perfil, $id = null)
    {
        if ($id === null) {
            $id = $perfil; // Fallback in case perfil is missing and id shifted
        }

        $vale = Vale::with(['presupuestos.siniestros.vehiculoInfo', 'piezas'])->where('id', $id)->first();

        if (!$vale) {
            abort(404, 'Vale no encontrado.');
        }

        $ExportVale = new ValesExport;
        $ExportVale->folio = $request->folio;
        $ExportVale->nameFile = "Vale {$request->folio}";
        $ExportVale->infoVale = $vale;

        $tempFile = $ExportVale->exportar();

        return response()->download($tempFile, "{$ExportVale->nameFile}.xlsx")->deleteFileAfterSend(true);
    }

    public function validarExistenciaVale($numeroVale)
    {
        $existeNumeroVale = $this->queryValePorNumeroEnPerfil($numeroVale)
            ->where('estado', '!=', 'Cancelado')
            ->exists();

        return response()->json([
            'numeroValeExists' => $existeNumeroVale,
        ]);
    }
    //working
    public function validarMatchAlbaran(Request $request, $perfil, $numVale)
    {
        // dd($numVale);
        $piezasAlbaran = $request->input('piezas');
        $vale = $this->queryValePorNumeroEnPerfil($numVale, $request->input('id_vale'))->first();

        if (!$vale) {
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'No se encontró el vale.',
                'icon' => 'error'
            ]);
        }

        // Obtener las piezas asociadas al vale, con la relación piezasPresupuesto
        $piezasVale = PiezasVale::where('id_vale', $vale->id)
            ->with('piezasPresupuesto')
            ->get();


        // Construir un array indexado por numero_parte para las piezas del vale
        $piezasValeArray = [];
        foreach ($piezasVale as $piezaVale) {
            $piezaPresupuesto = $piezaVale->piezasPresupuesto;
            if ($piezaPresupuesto) {
                $numeroParte = trim($piezaPresupuesto->numero_parte);
                $cantidad = $piezaVale->cantidad;
                $piezasValeArray[$numeroParte] = $cantidad;
            }
        }

        $errores = [];


        // dd($piezasValeArray);

        if (!$piezasAlbaran || !is_array($piezasAlbaran)) {
            return response()->json([
                'success' => false,
                'match' => false,
                'title' => 'Error de validación',
                'message' => 'No se recibieron piezas para validar.',
                'icon' => 'error',
            ]);
        }

        if (count($piezasAlbaran) > count($piezasValeArray)) {
            $errores[] = "El número de partes del albarán es mayor al del vale.";
        }

        // Validar cada pieza del albarán contra las del vale
        foreach ($piezasAlbaran as $piezaAlbaran) {
            $referencia = isset($piezaAlbaran['referencia']) ? trim($piezaAlbaran['referencia']) : '';
            $cantidadAlbaran = $piezaAlbaran['cantidad'] ?? 0;

            if ($referencia === '' || !isset($piezasValeArray[$referencia])) {
                $errores[] = "No existe ninguna pieza con el número {$referencia} en el vale.";
            } else {
                $cantidadVale = $piezasValeArray[$referencia];
                if ($cantidadAlbaran > $cantidadVale) {
                    $errores[] = "La cantidad de la referencia {$referencia} es mayor en el albarán. Albarán: {$cantidadAlbaran}, Vale: {$cantidadVale}";
                }
            }
        }

        if (count($errores) > 0) {
            // Convertir los errores en una lista HTML para mostrar en Swal.fire
            $listaErrores = '<ul style="text-align:left;">';
            foreach ($errores as $error) {
                $listaErrores .= "<li>* {$error}</li>";
            }
            $listaErrores .= '</ul>';

            return response()->json([
                'success' => false,
                'match' => false,
                'title' => 'Error de validación',
                'html' => $listaErrores,
                'icon' => 'error',
            ]);
        }

        return response()->json([
            'match' => true
        ]);
    }
    //working
    public function validarMatchEntrada(Request $request, $perfil = null, $numVale, $proveedor)
    {
        // if ($proveedor != "CHEVROLET") {
        //     return response()->json([
        //         'match' => true
        //     ]);
        // }
        $piezasEntrada = $request->input('piezas');

        $vale = $this->queryValePorNumeroEnPerfil($numVale, $request->input('id_vale'))->first();

        if (!$vale) {
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'html' => 'No se encontró el vale.',
                'icon' => 'error'
            ]);
        }

        // Obtener las piezas asociadas al vale, con la relación piezasPresupuesto
        $piezasVale = PiezasVale::where('id_vale', $vale->id)
            ->with('piezasPresupuesto')
            ->get();


        // Construir un array indexado por numero_parte para las piezas del vale
        $piezasValeArray = [];
        foreach ($piezasVale as $piezaVale) {
            $piezaPresupuesto = $piezaVale->piezasPresupuesto;
            if ($piezaPresupuesto) {
                $numeroParte = trim($piezaPresupuesto->numero_parte);
                $cantidad = $piezaVale->cantidad;
                $piezasValeArray[$numeroParte] = $cantidad;
            }
        }

        $errores = [];

        if (count($piezasEntrada) > count($piezasValeArray)) {
            $errores[] = "El número de partes de la entrada es mayor al del vale.";
        }

        // Validar cada pieza del albarán contra las del vale
        foreach ($piezasEntrada as $piezaEntrada) {
            $numParte = $piezaEntrada['numero_parte'];
            $cantidadEntrada = $piezaEntrada['cantidad'];

            if (!isset($piezasValeArray[$numParte])) {
                $errores[] = "No existe ninguna pieza con el número {$numParte} en el vale.";
            } else {
                $cantidadVale = $piezasValeArray[$numParte];
                if ($cantidadEntrada > $cantidadVale) {
                    $errores[] = "La cantidad de la parte {$numParte} es mayor en la entrada. Entrada: {$cantidadEntrada}, Vale: {$cantidadVale}";
                }
            }
        }

        if (count($errores) > 0) {
            // Convertir los errores en una lista HTML para mostrar en Swal.fire
            $listaErrores = '<ul style="text-align:left;">';
            foreach ($errores as $error) {
                $listaErrores .= "<li>* {$error}</li>";
            }
            $listaErrores .= '</ul>';

            return response()->json([
                'success' => false,
                'match' => false,
                'title' => 'Error de validación',
                'html' => $listaErrores,
                'icon' => 'error',
            ]);
        }

        return response()->json([
            'match' => true
        ]);
    }

    public function modificarParte(Request $request)
    {
        // dd($request->all());
        // Validar que los datos sean opcionales

        // dd($request->all());

        $request->validate([
            'id_vale' => 'nullable|integer|exists:vales,id',
            'numero_parte' => 'nullable|array',
            'descripcion' => 'nullable|array',
            'cantidad' => 'nullable|array',
            'importe_total' => 'nullable|array',
            'numero_parte_original' => 'nullable|array',
        ]);

        $idVale = $request->input('id_vale');
        $numerosParte = $request->input('numero_parte', []);
        $descripciones = $request->input('descripcion', []);
        $cantidades = $request->input('cantidad', []);
        $importesTotales = $request->input('importe_total', []);
        $numerosParteOriginal = $request->input('numero_parte_original', []);

        // Si no hay datos mínimos requeridos, retornar error
        if (empty($idVale) || empty($numerosParte) || empty($numerosParteOriginal)) {
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'icon' => 'error',
                'message' => 'No se recibieron datos para actualizar.'
            ]);
        }

        foreach ($numerosParte as $index => $numeroParteNuevo) {
            $numeroParteOriginal = $numerosParteOriginal[$index] ?? null;
            $descripcionNueva = $descripciones[$index] ?? null;
            $cantidad = $cantidades[$index] ?? null;
            $importeTotal = $importesTotales[$index] ?? null;

            if ($numeroParteOriginal === null) {
                continue;
            }

            // Buscar la pieza en la tabla piezas_vale por id_vale y numero_parte_original
            $piezaVale = PiezasVale::where('id_vale', $idVale)
                ->whereHas('piezasPresupuesto', function ($query) use ($numeroParteOriginal) {
                    $query->where('numero_parte', $numeroParteOriginal);
                })
                ->first();

            if ($piezaVale) {
                // Actualizar la información en la tabla piezas (relación con pieza)
                $pieza = $piezaVale->piezasPresupuesto;
                if ($pieza && $numeroParteNuevo !== null) {
                    $pieza->numero_parte = $numeroParteNuevo;
                }
                if ($pieza && $descripcionNueva !== null) {
                    $pieza->descripcion = $descripcionNueva;
                }

                // Actualizar la información en la tabla piezas_vale
                if ($cantidad !== null) {
                    $pieza->numero_pzas_presupuesto = $cantidad;
                    $piezaVale->cantidad = $cantidad;
                }
                if ($importeTotal !== null) {
                    $pieza->importe_total = $importeTotal;
                }
                $pieza->save();
                $piezaVale->save();
            }
        }

        return response()->json([
            'title' => '¡Éxito!',
            'icon' => 'success',
            'success' => true,
            'message' => 'Las piezas han sido actualizadas correctamente.'
        ]);
    }

    public function eliminarParte(Request $request)
    {
        // Buscar el vale por su número
        $vale = $this->queryValePorNumeroEnPerfil($request->numVale, $request->input('id_vale'))->first();

        if (!$vale) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró el vale.'
            ], 404);
        }

        // Buscar la pieza por su número de parte
        $pieza = Piezas::where('numero_parte', $request->numParte)->where('id_presupuesto', $vale->id_presupuesto)->first();

        if (!$pieza) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró la pieza.'
            ], 404);
        }

        try {
            $resultado = DB::transaction(function () use ($vale, $pieza) {
                $piezaVale = PiezasVale::where('id_vale', $vale->id)
                    ->where('id_pieza', $pieza->id)
                    ->lockForUpdate()
                    ->first();

                if (!$piezaVale) {
                    throw new \RuntimeException('pieza_vale_no_encontrada');
                }

                $albaranesActivos = PiezasAlbaran::where('id_pieza', $pieza->id)
                    ->where('activo', true)
                    ->whereHas('albaran', function ($q) use ($vale) {
                        $q->where('id_vale', $vale->id)
                            ->where('estado', '!=', 'Cancelado');
                    })
                    ->count();

                $entradasActivas = PiezasEntrada::where('id_pieza', $pieza->id)
                    ->where('activo', true)
                    ->whereHas('entrada', function ($q) use ($vale) {
                        $q->where('id_vale', $vale->id)
                            ->where('estado', '!=', 'Cancelado');
                    })
                    ->count();

                if ($albaranesActivos > 0 || $entradasActivas > 0) {
                    throw new \DomainException('pieza_con_surtido_activo');
                }

                $albaranIds = PiezasAlbaran::where('id_pieza', $pieza->id)
                    ->whereHas('albaran', function ($q) use ($vale) {
                        $q->where('id_vale', $vale->id);
                    })
                    ->pluck('id_albaran')
                    ->unique();

                $piezaVale->activo = false;
                $piezaVale->solicitud_eliminacion = false;
                $piezaVale->id_usuario_solicita_eliminacion = null;
                $piezaVale->fecha_solicitud_eliminacion = null;
                $piezaVale->save();

                $albaranesCancelados = 0;
                foreach ($albaranIds as $albaranId) {
                    $tienePiezasActivas = PiezasAlbaran::where('id_albaran', $albaranId)
                        ->where('activo', true)
                        ->exists();

                    if (!$tienePiezasActivas) {
                        $albaranesCancelados += Albaran::where('id', $albaranId)
                            ->where('estado', '!=', 'Cancelado')
                            ->update(['estado' => 'Cancelado']);
                    }
                }

                $vale->refresh()->load('presupuestos.siniestros.vehiculoInfo');
                $estadoVale = $this->recalcularEstadoVale($vale);
                $estadoSiniestro = $this->recalcularEstadoSiniestro($vale->presupuestos?->siniestros);
                $piezasActivas = PiezasVale::where('id_vale', $vale->id)
                    ->where('activo', true)
                    ->count();

                return compact('albaranesCancelados', 'estadoVale', 'estadoSiniestro', 'piezasActivas');
            });
        } catch (\DomainException $e) {
            return response()->json([
                'title' => 'No se puede eliminar',
                'icon' => 'warning',
                'success' => false,
                'message' => 'Esta pieza tiene albaranes o entradas activas. Debe liberar las partes antes de eliminarla.'
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró la pieza en el vale.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se pudo eliminar la pieza: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'title' => '¡Éxito!',
            'icon' => 'success',
            'success' => true,
            'message' => 'La pieza ha sido eliminada correctamente.',
            'vale_cerrado' => $resultado['piezasActivas'] === 0,
            'estado_vale' => $resultado['estadoVale'],
            'estado_siniestro' => $resultado['estadoSiniestro'],
            'albaranes_cancelados' => $resultado['albaranesCancelados'],
        ]);
    }

    private function recalcularEstadoVale(Vale $vale)
    {
        $vale->loadMissing('presupuestos.siniestros.vehiculoInfo');

        if ($vale->estado === 'Cancelado') {
            return $vale->estado;
        }

        $piezasVale = PiezasVale::where('id_vale', $vale->id)
            ->where('activo', true)
            ->get();

        if ($piezasVale->isEmpty()) {
            $vale->estado = 'Cerrado';
            $vale->fecha_surtido = null;
            $vale->save();
            return $vale->estado;
        }

        $taller = strtoupper(trim((string) ($vale->presupuestos?->siniestros?->vehiculoInfo?->taller ?? '')));
        $esAutocar = in_array($taller, ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO'], true);

        $todasCubiertas = $piezasVale->every(function ($piezaVale) use ($vale, $esAutocar) {
            $cantidadEnAlbaranes = PiezasAlbaran::whereHas('albaran', function ($q) use ($vale) {
                $q->where('id_vale', $vale->id)
                    ->where('estado', '!=', 'Cancelado');
            })->where('id_pieza', $piezaVale->id_pieza)
                ->where('activo', true)
                ->sum('cantidad');

            $cantidadEnEntradas = PiezasEntrada::whereHas('entrada', function ($q) use ($vale) {
                $q->where('id_vale', $vale->id)
                    ->where('estado', '!=', 'Cancelado');
            })->where('id_pieza', $piezaVale->id_pieza)
                ->where('activo', true)
                ->sum('cantidad');

            if ($esAutocar) {
                return $cantidadEnEntradas >= $piezaVale->cantidad;
            }

            return ($cantidadEnAlbaranes + $cantidadEnEntradas) >= $piezaVale->cantidad;
        });

        if ($todasCubiertas) {
            $vale->estado = 'Completado';
            $vale->fecha_surtido = $vale->fecha_surtido ?: now();
        } else {
            $vale->estado = 'Abierto';
            $vale->fecha_surtido = null;
        }

        $vale->save();
        return $vale->estado;
    }

    private function recalcularEstadoSiniestro($siniestro)
    {
        if (!$siniestro || in_array($siniestro->estado, ['Cancelado', 'Cerrado'], true)) {
            return $siniestro?->estado;
        }

        $siniestroConScope = Siniestro::withPresupuestosAndVales()->find($siniestro->id);
        if (!$siniestroConScope) {
            return $siniestro->estado;
        }

        $nuevoEstado = $siniestroConScope->pzs_faltantes <= 0 ? 'Completado' : 'Abierto';
        if ($siniestro->estado !== $nuevoEstado) {
            $siniestro->estado = $nuevoEstado;
            $siniestro->save();
        }

        return $siniestro->estado;
    }

    public function notificarEliminarParteAut(Request $request)
    {

        // Buscar el vale por su número
        $vale = $this->queryValePorNumeroEnPerfil($request->numVale, $request->input('id_vale'))->first();

        if (!$vale) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró el vale.'
            ], 404);
        }

        // Buscar la pieza por su número de parte
        $pieza = Piezas::where('numero_parte', $request->numParte)->where('id_presupuesto', $vale->id_presupuesto)->first();

        if (!$pieza) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró la pieza.'
            ], 404);
        }

        // Buscar la relación en piezas_vale
        $piezaVale = PiezasVale::where('id_vale', $vale->id)
            ->where('id_pieza', $pieza->id)
            ->first();

        if (!$piezaVale) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró la relación entre la pieza y el vale.'
            ], 404);
        }

        // Actualizar la columna solicitud_eliminacion a true
        $piezaVale->solicitud_eliminacion = true;
        $piezaVale->id_usuario_solicita_eliminacion = Auth::id();
        $piezaVale->fecha_solicitud_eliminacion = now();
        $piezaVale->save();

        // Obtener el usuario que solicita la eliminación
        $usuarioSolicita = Auth::user();

        // Preparar datos para el correo
        $datos = [
            'numVale' => $vale->numero_vale,
            'numeroParte' => $pieza->numero_parte,
            'descripcionPieza' => $pieza->descripcion ?? 'Sin descripción',
            'linkVale' => $this->buildValeLinkPorPerfil($vale->numero_vale, $vale->id),
            'usuarioSolicita' => $usuarioSolicita->name ?? 'Usuario desconocido',
            'motivo' => $request->motivo ?? '',
        ];

        // Obtener el usuario que cotizó para enviarle la notificación
        $presupuesto = Presupuesto::where('id', $vale->id_presupuesto)->first();
        $usuario = User::find($presupuesto->id_usuario_cotizacion);

        // Enviar notificación por correo
        Mail::to($usuario->email ?? 'programador4.ti@grupodc.com.mx')->send(new MailSolicitudEliminacion($datos));

        return response()->json([
            'title' => '¡Éxito!',
            'icon' => 'success',
            'success' => true,
            'message' => 'Solicitud de eliminación registrada correctamente.'
        ]);
    }

    public function pedirModificacionPartes(Request $request)
    {
        $datos = [
            'mensaje' => $request->input('mensaje'),
            'numVale' => $request->input('num_vale'),
            'linkVale' => $request->input('link')
        ];

        $vale = $this->queryValePorNumeroEnPerfil($request->input('num_vale'), $request->input('id_vale'))->first();
        $presupuesto = Presupuesto::where('id', $vale->id_presupuesto)->first();

        $usuario = User::find($presupuesto->id_usuario_cotizacion);

        Mail::to($usuario->email)->send(new MailPedirModificacionParte($datos));

        return response()->json([
            'success' => true,
            'title' => '¡Éxito!',
            'message' => 'Correo enviado exitosamente',
            'icon' => 'success'
        ]);
    }

    public function rechazarSolicitudEliminacion(Request $request)
    {
        // Buscar el vale por su número
        $vale = $this->queryValePorNumeroEnPerfil($request->numVale, $request->input('id_vale'))->first();

        if (!$vale) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró el vale.'
            ], 404);
        }

        // Buscar la pieza por número de parte y presupuesto
        $pieza = Piezas::where('numero_parte', $request->numParte)
            ->where('id_presupuesto', $vale->id_presupuesto)
            ->first();

        if (!$pieza) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró la pieza.'
            ], 404);
        }

        // Buscar la relación en piezas_vales
        $piezaVale = PiezasVale::where('id_vale', $vale->id)
            ->where('id_pieza', $pieza->id)
            ->first();

        if (!$piezaVale) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró la relación entre la pieza y el vale.'
            ], 404);
        }

        // Actualizar la columna solicitud_eliminacion a false/0
        $piezaVale->solicitud_eliminacion = false;
        $piezaVale->id_usuario_solicita_eliminacion = null;
        $piezaVale->fecha_solicitud_eliminacion = null;
        $piezaVale->save();

        // Enviar notificación por correo
        $usuarioRechazo = Auth::user();

        $datos = [
            'numVale' => $vale->numero_vale,
            'numeroParte' => $pieza->numero_parte,
            'descripcionPieza' => $pieza->descripcion ?? 'Sin descripción',
            'linkVale' => $this->buildValeLinkPorPerfil($vale->numero_vale, $vale->id),
            'usuarioRechazo' => $usuarioRechazo->name ?? 'Usuario desconocido',
        ];

        // Obtener el usuario que cotizó para enviarle la notificación del rechazo
        $presupuesto = Presupuesto::where('id', $vale->id_presupuesto)->first();
        $usuario = User::find($presupuesto->id_usuario_cotizacion);

        Mail::to($usuario->email ?? 'programador4.ti@grupodc.com.mx')->send(new MailSolicitudRechazada($datos));

        return response()->json([
            'title' => '¡Solicitud rechazada!',
            'icon' => 'success',
            'success' => true,
            'message' => 'La solicitud de eliminación ha sido rechazada correctamente.'
        ]);
    }
}
