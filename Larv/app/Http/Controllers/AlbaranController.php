<?php

namespace App\Http\Controllers;

use App\Mail\MailAlbaranAsignado;
use App\Models\Albaran;
use App\Models\PiezasAlbaran;
use App\Models\Presupuesto;
use App\Models\User;
use App\Models\Vale;
use App\Services\MailLinkResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AlbaranController extends Controller
{
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

    public function getAlbaranesView()
    {
        return view('albaranes');
    }

    public function albaranView()
    {
        return view('verAlbaran');
    }

    public function getAlbaranes(Request $request, $perfil = null)
    {
        $fechaActual = now();
        $fechaHaceDosAnios = $fechaActual->copy()->subYears(2);

        $query = Albaran::with('siniestros.vehiculoInfo', 'vales.presupuestos')
            ->whereDate('created_at', '>=', $fechaHaceDosAnios);

        if ($perfil === 'refacciones') {
            $query->whereHas('vales.presupuestos', function ($q) {
                $q->where('proveedor', 'CHEVROLET');
            });
        } else {
            $id_perfil_query = $this->getIdPerfil($perfil);
            if ($id_perfil_query) {
                $query->whereHas('siniestros', function ($q) use ($id_perfil_query) {
                    $q->where('perfil_id', $id_perfil_query);
                });
            }
        }

        if ($request->has('estado')) {
            if ($request->estado === 'Facturado') {
                // Buscar albaranes con estado "Facturado" y siniestros relacionados con estado "Cerrado"
                $query->where('estado', 'Facturado')
                    ->whereHas('siniestros', function ($q) {
                        $q->where('estado', 'Cerrado');
                    });
            } else {
                $query->where('estado', $request->estado);
            }
        }

        // Selecciona todos los campos y agrega created_at como alias fecha_surtido
        $albaranes = $query->select('*', DB::raw('created_at as fecha_surtido'))->get();

        return $albaranes;
    }

    public function getAlbaran($perfil = null, $numeroAlbaran)
    {
        $albaran = Albaran::where('numero_albaran', $numeroAlbaran)
            ->with('siniestros.vehiculoInfo')
            ->with('vales.presupuestos.piezas')
            ->get();
        return $albaran;
    }

    public function consultarAlbaranW32(Request $request, $perfil = null, $numAlbaran)
    {
        // dd($request->all());
        // Obtener los datos del albarán desde Firebird
        $albaran = new Albaran();
        // También obtener la relación 'siniestros' y su vehiculoInfo del albarán en la base de datos principal
        $numVale = $request->input('numVale');
        $vale = null;
        $marca = null;
        if ($numVale) {
            $vale = $this->queryValePorNumeroEnPerfil($numVale, $request->input('idVale'))
                ->with('presupuestos.vehiculoInfo')
                ->first();
            if ($vale && $vale->presupuestos && $vale->presupuestos->siniestros && $vale->presupuestos->siniestros->vehiculoInfo) {
                $marca = $vale->presupuestos->siniestros->vehiculoInfo->marca;
            }
        }
        // dd($marca);
        $resultados = $albaran->consultarAlbaranW32($numAlbaran, $marca, $perfil);
        // $resultados = $albaran->consultarAlbaranW32($numAlbaran);
        // dd($resultados);
        return response()->json([
            'firebird' => $resultados,
            'presupuestos' => $vale ? $vale->presupuestos : null,
        ]);
    }

    public function consultarAlbaranW32All(Request $request, $perfil = null, $numeroVale)
    {
        $albaran = new Albaran();
        $albaranes = [];
        $marca = null;

        if ($numeroVale) {
            $vale = $this->queryValePorNumeroEnPerfil($numeroVale, $request->input('idVale'))
                ->with('presupuestos.vehiculoInfo')
                ->first();
            if ($vale && $vale->presupuestos && $vale->presupuestos->siniestros && $vale->presupuestos->siniestros->vehiculoInfo) {
                $marca = $vale->presupuestos->siniestros->vehiculoInfo->marca;
            }
        }
        // dd($request->all());
        foreach ($request->all() as $numAlbaran) {
            $albaranes[] = $albaran->consultarAlbaranW32($numAlbaran, null, $perfil);
        }
        // dd($albaranes);
        return response()->json($albaranes);
    }

    public function consultarAlbaran($perfil = null, $numAlbaran)
    {
        // dd($numAlbaran);
        // Obtener los datos del albarán desde Firebird
        $albaran = Albaran::where('numero_albaran', $numAlbaran)->with('siniestros.vehiculoInfo')->first();
        $piezasAlbaran = PiezasAlbaran::where('id_albaran', $albaran->id)
            ->where('activo', 1)
            ->with('piezas')
            ->get();
        return response()->json([
            'albaran' => $albaran,
            'piezas' => $piezasAlbaran ? $piezasAlbaran : null,
        ]);
    }

    public function notificarAlbaranAsignado(Request $request, MailLinkResolver $mailLinkResolver)
    {
        $validatedData = $request->validate([
            'numVale' => 'required',
            'numAlbaran' => 'required',
        ]);

        $vale = $this->queryValePorNumeroEnPerfil($validatedData['numVale'], $request->input('idVale'))
            ->with('presupuestos.siniestros')
            ->firstOrFail();

        $presupuesto = $vale->presupuestos;

        $datos = [
            'numVale' => $validatedData['numVale'],
            'numAlbaran' => $validatedData['numAlbaran'],
            'linkAlbaran' => $presupuesto
                ? $mailLinkResolver->buildAlbaranLink($presupuesto, $validatedData['numAlbaran'])
                : url('/albaranes/ver?numAlbaran=' . $validatedData['numAlbaran']),
        ];

        $usuario = User::find($vale->id_usuario_registro);

        Mail::to($usuario->email)->send(new MailAlbaranAsignado($datos));

        return response()->json([
            'success' => true,
            'title' => '¡Éxito!',
            'message' => 'Correo enviado exitosamente',
            'icon' => 'success'
        ]);
    }

    public function liberarParte(Request $request)
    {
        // Buscar el albaran por su número
        $albaran = Albaran::where('numero_albaran', $request->numAlbaran)->first();

        if (!$albaran) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró el albarán.'
            ], 404);
        }

        $piezaAlbaran = PiezasAlbaran::where('id_pieza', $request->idParte)
            ->where('id_albaran', $albaran->id)
            ->where('activo', 1)
            ->first();

        if (!$piezaAlbaran) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró la pieza.'
            ], 404);
        }

        $piezaAlbaran->activo = 0;
        $piezaAlbaran->save();

        return response()->json([
            'title' => '¡Éxito!',
            'icon' => 'success',
            'success' => true,
            'message' => 'La pieza ha sido liberada correctamente.'
        ]);
    }
}
