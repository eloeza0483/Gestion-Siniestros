<?php

namespace App\Http\Controllers;

use App\Exports\Presupuesto2Export;
use App\Exports\PresupuestoExport;
use App\Http\Traits\SiniestroTrait;
use App\Mail\MailCotizacionPresupuesto;
use App\Models\PermisosUsuarios;
use Illuminate\Support\Facades\Auth;
use App\Models\Presupuesto;
use App\Models\Piezas;
use App\Models\Aseguradora;
use App\Models\Marca;
use App\Models\Siniestro;
use App\Models\Vehiculo;
use App\Models\Perfile;
use App\Services\MailLinkResolver;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PresupuestoController extends Controller
{
    use SiniestroTrait;

    public function getPresupuestos(Request $request, $perfil)
    {
        // $id_perfil = $this->formatPerfilToSQL($this->id_perfil);
        $id_perfil = $this->id_perfil;

        $query = Presupuesto::with('piezas', 'siniestros.vehiculoInfo', 'usuarioCreacion'); //debe traer numero de orden y vin
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        } else {
            // Por defecto mostrar SinCotizar, Pendiente y Cotizado
            $query->whereIn('estado', ['SinCotizar', 'Pendiente', 'Cotizado']);
        }

        if ($id_perfil === 3) {
            // Refacciones: todos los presupuestos con proveedor CHEVROLET
            $query->where('proveedor', 'CHEVROLET');
        } else {
            // Autocar: filtra por su taller específico a través del siniestro
            $query->whereHas('siniestros', function ($q) use ($id_perfil) {
                $q->where('perfil_id', $id_perfil);
            });
        }
        $presupuestos = $query->select("*", "created_at as fecha_registro")->get()->map->permisosUsuarios();
        return [
            "data" => $presupuestos,
            "perfil" => PermisosUsuarios::permisosAuth($id_perfil)
        ];
    }

    public function getPresupuestosTalleresChevrolet(Request $request)
    {
        $query = Presupuesto::with('piezas', 'siniestros.vehiculoInfo', 'usuarioCreacion')
            ->where('proveedor', 'CHEVROLET');

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // dd($request->taller);
        // $request->taller
        if ($request->filled('taller')) {
            $taller = $request->taller;

            // Caso especial: REFACCIONES muestra todos EXCEPTO los AUTOCAR
            if (strtoupper($taller) === 'REFACCIONES') {
                $query->whereHas('siniestros.vehiculoInfo', function ($q) {
                    $q->whereNotIn('taller', ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO']);
                });
            } else {
                // Caso normal: filtra por el taller específico
                $query->whereHas('siniestros.vehiculoInfo', function ($q) use ($taller) {
                    $q->where('taller', $taller);
                });
            }
        }

        $presupuestos = $query->select("*", "created_at as fecha_registro")->get();

        foreach ($presupuestos as $presupuesto) {
            $presupuesto->numero_productos = $presupuesto->numero_productos;
        }

        return $presupuestos;
    }

    public function getPresupuestosTalleresExternos(Request $request)
    {
        $query = Presupuesto::with('piezas', 'siniestros.vehiculoInfo', 'usuarioCreacion')
            ->where('proveedor', '!=', 'CHEVROLET');

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('taller')) {
            $taller = $request->taller;

            // Caso especial: REFACCIONES muestra todos EXCEPTO los AUTOCAR
            if (strtoupper($taller) === 'REFACCIONES') {
                $query->whereHas('siniestros.vehiculoInfo', function ($q) {
                    $q->whereNotIn('taller', ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO']);
                });
            } else {
                // Caso normal: filtra por el taller específico
                $query->whereHas('siniestros.vehiculoInfo', function ($q) use ($taller) {
                    $q->where('taller', $taller);
                });
            }
        }

        $presupuestos = $query->select("*", "created_at as fecha_registro")->get()->map(function ($presupuesto) {
            $presupuesto->numero_productos = $presupuesto->numero_productos;

            return $presupuesto;
        });

        // foreach ($presupuestos as $presupuesto) {
        //     $presupuesto->numero_productos = $presupuesto->numero_productos;
        // }

        return $presupuestos;
    }

    //FUNCION PARA OBTENER NUMERO DE PARTES COTIZADOS
    public function getPresupuestoByNumero(Request $request, $perfil, $numero, $isVale = false) //El numero de presupuesto es el FOLIO
    {
        $nombre_perfil = $perfil ? str_replace(' ', '', ucwords(str_replace('_', ' ', $perfil))) : '';
        $this->authorize("viewPresupuestos{$nombre_perfil}", Presupuesto::class);

        // if ($isVale) {
        //     $query = Presupuesto::with([
        //         'piezas' => function ($queryPiezas) {
        //             $queryPiezas->whereNotNull('numero_parte');
        //         },
        //         'siniestros.vehiculoInfo',
        //         'usuarioCreacion'
        //     ])->whereHas('piezas', function ($queryPiezas) {
        //         $queryPiezas->whereNotNull('numero_parte');
        //     })->where('numero_presupuesto', $numero); //debe traer numero de orden y vin
        // } else {
        $query = Presupuesto::with('piezas', 'siniestros.vehiculoInfo', 'usuarioCreacion', 'siniestros.cliente')->where('numero_presupuesto', $numero);
        // }
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('vehiculo_info.taller') && $request->input('vehiculo_info.taller') !== null) {
            $query->whereHas('siniestros.vehiculoInfo', function ($q) use ($request) {
                $q->where('taller', $request->input('vehiculo_info.taller'));
            });
        }

        return $query->get();
    }

    //vista principal presupuestos
    public function getPresupuestosList(Request $request)
    {
        $perfil = $request->route('perfil');
        $nombre_perfil = $perfil ? str_replace(' ', '', ucwords(str_replace('_', ' ', $perfil))) : '';
        $this->authorize("viewPresupuestos{$nombre_perfil}", Presupuesto::class);

        $talleres = Perfile::orderBy('nombre')->get();
        $user = Auth::user();
        // dd($user);
        return view('presupuestos', compact('talleres', 'user'));
    }

    public function crearPresupuesto(Request $request, $perfil = null)
    {
        if ($request->has('id_cliente') && ($request->id_cliente === 'undefined' || $request->id_cliente === 'null')) {
            $request->merge(['id_cliente' => null]);
        }

        $validatedData = $request->validate([
            'id_siniestro' => 'required|integer|exists:siniestros,id',
            'proveedor' => 'required|string|max:255',
            'taller' => 'required|string|max:255',
            'piezas' => 'required|string',
            'subtotal' => 'nullable|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'id_cliente' => 'nullable|integer|exists:clientes,id',
        ]);

        try {
            $usuario = Auth::user();
            $nombre_perfil = $this->formatPerfilToPermisos($perfil ?? '');
            $puedeCotizarDirectamente = $usuario->can("cotizarDirectamente$nombre_perfil", Presupuesto::class);
            $piezas = json_decode($validatedData['piezas'], true);

            if (!is_array($piezas) || empty($piezas)) {
                return response()->json([
                    'success' => false,
                    'title' => 'Error de validación',
                    'message' => 'Debes proporcionar al menos una pieza válida para crear el presupuesto',
                    'icon' => 'error',
                ], 422);
            }

            $presupuesto = DB::transaction(function () use ($validatedData, $usuario, $puedeCotizarDirectamente, $piezas, $request) {
                // Si viene id_cliente en la petición, actualizar el siniestro asociado
                if ($request->filled('id_cliente')) {
                    Siniestro::where('id', $validatedData['id_siniestro'])->update([
                        'id_cliente' => $request->id_cliente,
                    ]);
                }
                if ($puedeCotizarDirectamente) {
                    if (!isset($validatedData['subtotal']) || !isset($validatedData['iva']) || !isset($validatedData['total'])) {
                        throw new \InvalidArgumentException('Debes proporcionar subtotal, IVA y total para cotizar directamente');
                    }

                    $presupuesto = Presupuesto::create([
                        'id_siniestro' => $validatedData['id_siniestro'],
                        'subtotal' => $validatedData['subtotal'],
                        'iva' => $validatedData['iva'],
                        'total' => $validatedData['total'],
                        'estado' => 'Cotizado',
                        'proveedor' => $validatedData['proveedor'],
                        'fecha_cotizado' => now(),
                        'id_usuario_creacion' => $usuario->id,
                        'id_usuario_cotizacion' => $usuario->id,
                    ]);
                } else {
                    $presupuesto = Presupuesto::create([
                        'id_siniestro' => $validatedData['id_siniestro'],
                        'proveedor' => $validatedData['proveedor'],
                        'id_usuario_creacion' => $usuario->id,
                        'estado' => $validatedData['proveedor'] != "CHEVROLET" ? 'SinCotizar' : 'Pendiente',
                    ]);

                    Siniestro::where('id', $validatedData['id_siniestro'])->update([
                        'estado' => 'Abierto',
                    ]);
                }

                if ($validatedData['taller'] === 'AUTOCAR PENSIONES') {
                    $presupuesto->numero_presupuesto = 'APN' . str_pad($presupuesto->id, 7, '0', STR_PAD_LEFT);
                } else if ($validatedData['taller'] === 'AUTOCAR PERIFERICO') {
                    $presupuesto->numero_presupuesto = 'APR' . str_pad($presupuesto->id, 7, '0', STR_PAD_LEFT);
                } else {
                    // Para REFACCIONES y otros talleres
                    $presupuesto->numero_presupuesto = 'REF' . str_pad($presupuesto->id, 7, '0', STR_PAD_LEFT);
                }

                foreach ($piezas as $pieza) {
                    $presupuesto->piezas()->create([
                        'numero_parte' => $pieza['numero_parte'] ?? null,
                        'descripcion' => $pieza['descripcion'] ?? null,
                        'numero_pzas_presupuesto' => $pieza['cantidad'] ?? 0,
                        'importe_unitario' => $pieza['precio_unitario'] ?? 0,
                        'importe_total' => $pieza['total'] ?? 0,
                        'existencia' => $pieza['existencia'] ?? null,
                        'tiempo_entrega' => $pieza['tiempo_entrega'] ?? null,
                        'descripcion_w32' => $pieza['descripcion_w32'] ?? null,
                    ]);
                }

                $presupuesto->save();

                return $presupuesto;
            });

            return response()->json([
                'success' => true,
                'title' => '¡Éxito!',
                'message' => 'Presupuesto creado correctamente',
                'icon' => 'success',
                'presupuesto' => $presupuesto,
                'evidencias' => $presupuesto->evidencias ?? null,
                'proveedor' => $presupuesto->proveedor ?? null
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'title' => 'Error de validación',
                'message' => $e->getMessage(),
                'icon' => 'error',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'No se pudo crear el presupuesto: ' . $e->getMessage(),
                'icon' => 'error',
            ], 500);
        }
    }

    //Esto también está implementado en crearPresupuesto()
    public function subirEvidencias(Request $request)
    {
        // dd($request->folio);
        $presupuesto = Presupuesto::where('numero_presupuesto', $request->folio)->first();
        try {
            if ($presupuesto->estado != 'SinCotizar') {
                $presupuesto->update(['estado' => 'SinCotizar']);
            }
            return response()->json([
                'success' => true,
                'title' => '¡Éxito!',
                'message' => 'Evidencias añadidas correctamente',
                'icon' => 'success', // Icono de éxito
                'presupuesto' => $presupuesto,
            ]);
        } catch (\Exception $e) {
            // Respuesta de error para SweetAlert
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'No se pudo añadir la evidencia: ' . $e->getMessage(),
                'icon' => 'error', // Icono de error
            ], 500);
        }
    }

    public function addPresupuestoView(Request $request, $perfil)
    {
        // dd($perfil);
        // $perfil = $request->route('perfil');
        $nombre_perfil = $perfil ? str_replace(' ', '', ucwords(str_replace('_', ' ', $perfil))) : '';

        $this->authorize("viewPresupuestos{$nombre_perfil}", Presupuesto::class);

        $aseguradoras = Aseguradora::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $vehiculos = Vehiculo::orderBy('nombre')->get();
        $taller = $this->formatPerfilToSQL($perfil);
        $talleres = Perfile::where('nombre', $taller)->get();
        // Obtener el taller de la query string si existe
        // $tallerSeleccionado = $request->query('taller');
        // dd($request->all());

        return view('administrarPresupuestos', [
            'aseguradoras' => $aseguradoras,
            'marcas' => $marcas,
            'vehiculos' => $vehiculos,
            'talleres' => $talleres,
            'modo' => 'añadir',
            'estado' => null,
            // 'tallerSeleccionado' => $tallerSeleccionado,
            'nombre_perfil' => $nombre_perfil,
        ]);
    }

    public function presupuestoView(Request $request)
    {
        $perfil = $request->route('perfil');
        $nombre_perfil = $perfil ? str_replace(' ', '', ucwords(str_replace('_', ' ', $perfil))) : '';
        $this->authorize("viewPresupuestos{$nombre_perfil}", Presupuesto::class);

        $numeroPresupuesto = $request->query('folio');
        $presupuesto = Presupuesto::where('numero_presupuesto', $numeroPresupuesto)->first();
        $aseguradoras = Aseguradora::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $vehiculos = Vehiculo::orderBy('nombre')->get();
        $talleres = Perfile::orderBy('nombre')->get();

        return view('administrarPresupuestos', [
            'aseguradoras' => $aseguradoras,
            'marcas' => $marcas,
            'vehiculos' => $vehiculos,
            'talleres' => $talleres,
            'modo' => 'ver',
            'estado' => $presupuesto->estado ?? null,
            'nombre_perfil' => $nombre_perfil,
        ]);
    }

    public function cotizarPresupuestoView(Request $request)
    {
        // echo $request->folio;Z
        $perfil = $request->route('perfil');
        $nombre_perfil = $perfil ? str_replace(' ', '', ucwords(str_replace('_', ' ', $perfil))) : '';
        $this->authorize("viewPresupuestos{$nombre_perfil}", Presupuesto::class);


        // $aseguradoras = Aseguradora::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $vehiculos = Vehiculo::orderBy('nombre')->get();
        $talleres = Perfile::orderBy('nombre')->get();
        $canUpdate = Auth::user()->can('updatePresupuestos', Presupuesto::class);
        return view('administrarPresupuestos', [
            // 'aseguradoras' => $aseguradoras,
            'marcas'        => $marcas,
            'vehiculos'     => $vehiculos,
            'talleres'      => $talleres,
            'modo'          => 'cotizar',
            'estado'        => null,
            'nombre_perfil' => $nombre_perfil,
            'canUpdate'     => $canUpdate,
        ]);
    }

    public function cotizarPresupuesto(Request $request, MailLinkResolver $mailLinkResolver)
    {
        $numeroPresupuesto = $request->route('presupuesto');
        $validatedData = $request->except(['_token']);

        try {
            $presupuesto = Presupuesto::where('numero_presupuesto', $numeroPresupuesto)->first();

            if (!$presupuesto) {
                return response()->json([
                    'success' => false,
                    'title' => 'Error',
                    'message' => 'No se encontró el presupuesto especificado.',
                    'icon' => 'error'
                ], 404);
            }

            if ($presupuesto->proveedor === 'CHEVROLET') {
                $codigoCliente = $request->input('codigoCliente');
                if (empty($codigoCliente)) {
                    return response()->json([
                        'success' => false,
                        'title' => 'Error',
                        'message' => 'No se proporcionó el código de cliente.',
                        'icon' => 'error'
                    ], 422);
                }

                $cliente = \App\Models\Cliente::where('codigo', $codigoCliente)
                    ->where('activo', 1)
                    ->first();

                if (!$cliente) {
                    return response()->json([
                        'success' => false,
                        'title' => 'Error de Cliente',
                        'message' => "El código de cliente '{$codigoCliente}' no existe en la base de datos local o está inactivo. Asegúrate de que el cliente esté dado de alta en G-Castul.",
                        'icon' => 'error'
                    ], 422);
                }

                // Guardar id_cliente en el Siniestro asociado
                if ($presupuesto->id_siniestro) {
                    Siniestro::where('id', $presupuesto->id_siniestro)->update([
                        'id_cliente' => $cliente->id
                    ]);
                }
            }

            $presupuesto->update([
                'subtotal' => $validatedData['subtotal'] ?? 0,
                'iva' => $validatedData['iva'] ?? 0,
                'total' => $validatedData['total'] ?? 0,
                'estado' => 'Cotizado',
                'fecha_cotizado' => now(),
                'id_usuario_cotizacion' => Auth::user()->id,
            ]);

            $piezasJson = $request->input('piezas');
            $piezas = json_decode($piezasJson, true);

            DB::transaction(function () use ($presupuesto, $piezas) {
                foreach ($piezas as $pieza) {
                    $id = (isset($pieza['id']) && is_numeric($pieza['id'])) ? (int) $pieza['id'] : null;

                    // Si no hay ID válido, la pieza ya fue creada vía agregarPiezaCotizacion;
                    // omitirla aquí evita duplicarla.
                    if (!$id) continue;

                    $presupuesto->piezas()->updateOrCreate(
                        ['id' => $id],
                        [
                            'numero_parte'            => $pieza['numero_parte'],
                            'descripcion'             => $pieza['descripcion'],
                            'descripcion_w32'         => $pieza['descripcion_w32'],
                            'numero_pzas_presupuesto' => $pieza['cantidad'],
                            'importe_unitario'        => $pieza['precio_unitario'],
                            'importe_total'           => $pieza['total'],
                            'existencia'              => $pieza['existencia'],
                            'tiempoentrega'           => $pieza['tiempo_entrega'],
                        ]
                    );
                }
            });

            $perfilStr = $mailLinkResolver->resolvePerfilCotizacion($presupuesto);
            $linkPresupuesto = $mailLinkResolver->buildCotizacionLink($presupuesto);

            return response()->json([
                'success' => true,
                'title' => '¡Éxito!',
                'message' => 'Presupuesto cotizado correctamente',
                'icon' => 'success',
                'presupuesto' => $presupuesto,
                'perfil_siniestro' => $perfilStr,
                'link_presupuesto' => $linkPresupuesto,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'No se pudo crear el presupuesto: ' . $e->getMessage(),
                'icon' => 'error',
            ], 500);
        }
    }


    public function exportPresupuesto(Request $request, $perfil = null, $id)
    {

        // Cargar presupuesto con todas las relaciones necesarias
        $pres = Presupuesto::with(['siniestros.vehiculoInfo', 'piezas'])
            ->where('id', $id)
            ->first();

        // Validar que existe
        if (!$pres) {
            return response()->json(['error' => 'Presupuesto no encontrado', 'id' => $id], 404);
        }

        $ExportPres = new PresupuestoExport;
        $ExportPres->folio = $request->folio;
        $ExportPres->taller = $request->taller;
        $ExportPres->nameFile = "Presupuesto {$request->folio}";
        $ExportPres->infoPresupuesto = $pres;

        // Obtener el path del archivo temporal
        $tempFile = $ExportPres->exportar();

        // Descargar el archivo y eliminarlo después
        return response()->download($tempFile, "{$ExportPres->nameFile}.xlsx")->deleteFileAfterSend(true);

        // ### ANTERIOR
        // $pro = DetallePieza::where('id_presupuesto',$id)->select('numero_parte','descripcion','numero_pzas_presupuesto','importe_unidad','importe_total');

        // return (new PresupuestoExport($pro))->cabeceras([["Presupuesto $request->folio"],['Numero Parte','Descripcion','Cantidad','Importe Unitario','Importe Total']])->download("Presupuesto $request->folio.xlsx");
    }

    public function enviarMailCotizacion(Request $request, MailLinkResolver $mailLinkResolver)
    {
        $validatedData = $request->validate([
            'numeroPresupuesto' => 'required|string',
        ]);

        $presupuesto = Presupuesto::with(['siniestros', 'usuarioCreacion'])
            ->where('numero_presupuesto', $validatedData['numeroPresupuesto'])
            ->first();

        if (!$presupuesto) {
            return response()->json([
                'success' => false,
                'title' => 'Error',
                'message' => 'No se encontró el presupuesto para generar el correo.',
                'icon' => 'error',
            ], 404);
        }

        $datos = [
            'numeroPresupuesto' => $presupuesto->numero_presupuesto,
            'linkPresupuesto' => $mailLinkResolver->buildCotizacionLink($presupuesto),
        ];

        // Se obtiene el usuario que registró el presupuesto (vale)
        $usuarioCreador = $presupuesto->usuarioCreacion->first();
        $destinatario = $usuarioCreador->email ?? 'programador4.ti@grupodc.com.mx';

        Mail::to($destinatario)->send(new MailCotizacionPresupuesto($datos));

        return response()->json([
            'success' => true,
            'title' => '¡Éxito!',
            'message' => 'Correo enviado exitosamente',
            'icon' => 'success'
        ]);
    }
    public function agregarPiezaCotizacion(Request $request, $perfil, $numeroPresupuesto)
    {
        $this->authorize('updatePresupuestos', Presupuesto::class);

        try {
            $presupuesto = Presupuesto::where('numero_presupuesto', $numeroPresupuesto)->first();

            if (!$presupuesto) {
                return response()->json([
                    'success' => false,
                    'title'   => 'Error',
                    'message' => 'Presupuesto no encontrado.',
                    'icon'    => 'error',
                ], 404);
            }

            $pieza = Piezas::create([
                'id_presupuesto'          => $presupuesto->id,
                'numero_parte'            => $request->input('numero_parte'),
                'descripcion'             => $request->input('descripcion'),
                'descripcion_w32'         => $request->input('descripcion_w32'),
                'numero_pzas_presupuesto' => $request->input('cantidad', 1),
                'importe_unitario'        => $request->input('precio_unitario', 0),
                'importe_total'           => $request->input('importe_total', 0),
                'existencia'              => $request->input('existencia', 0),
                'tiempoentrega'           => $request->input('tiempoentrega', '1a3dias'),
            ]); // El PiezasObserver registra el log 'agregar_pieza' automáticamente

            return response()->json([
                'success' => true,
                'title'   => '¡Listo!',
                'message' => 'Pieza agregada correctamente.',
                'icon'    => 'success',
                'pieza'   => $pieza,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'title'   => 'Error',
                'message' => 'No se pudo agregar la pieza: ' . $e->getMessage(),
                'icon'    => 'error',
            ], 500);
        }
    }

    public function modificarPiezaCotizacion(Request $request, $perfil, $numeroPresupuesto, $pieza)
    {
        $this->authorize('updatePresupuestos', Presupuesto::class);

        try {
            $presupuesto = Presupuesto::where('numero_presupuesto', $numeroPresupuesto)->first();

            if (!$presupuesto) {
                return response()->json([
                    'success' => false,
                    'title'   => 'Error',
                    'message' => 'Presupuesto no encontrado.',
                    'icon'    => 'error',
                ], 404);
            }

            $piezaModel = Piezas::where('id', $pieza)
                ->where('id_presupuesto', $presupuesto->id)
                ->first();

            if (!$piezaModel) {
                return response()->json([
                    'success' => false,
                    'title'   => 'Error',
                    'message' => 'Pieza no encontrada en este presupuesto.',
                    'icon'    => 'error',
                ], 404);
            }

            // Mapa campo request -> campo modelo
            $camposMap = [
                'numero_parte'    => 'numero_parte',
                'descripcion'     => 'descripcion',
                'descripcion_w32' => 'descripcion_w32',
                'cantidad'        => 'numero_pzas_presupuesto',
                'precio_unitario' => 'importe_unitario',
                'importe_total'   => 'importe_total',
                'existencia'      => 'existencia',
                'tiempoentrega'   => 'tiempoentrega',
            ];

            foreach ($camposMap as $campoRequest => $campoModelo) {
                if ($request->has($campoRequest)) {
                    $piezaModel->$campoModelo = $request->input($campoRequest);
                }
            }

            // El PiezasObserver detecta los campos dirty y registra los logs automáticamente
            $piezaModel->save();

            return response()->json([
                'success' => true,
                'title'   => '¡Guardado!',
                'message' => 'Pieza actualizada correctamente.',
                'icon'    => 'success',
                'pieza'   => $piezaModel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'title'   => 'Error',
                'message' => 'No se pudo modificar la pieza: ' . $e->getMessage(),
                'icon'    => 'error',
            ], 500);
        }
    }
}
