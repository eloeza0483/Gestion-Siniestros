<?php

namespace App\Http\Controllers;

use App\Mail\MailEntradaAsignada;
use App\Mail\MailLiberacionPartes;
use App\Models\Albaran;
use App\Models\Entrada;
use App\Models\Piezas;
use App\Models\PiezasEntrada;
use App\Models\User;
use App\Models\PermisosUsuarios;
use App\Models\Vale;
use Illuminate\Http\Request;
use App\Http\Traits\SiniestroTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EntradaController extends Controller
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

    public function getEntradasView()
    {
        return view('entradas');
    }

    public function entradaView()
    {
        $numEntrada = $_GET['numEntrada'];
        // $entrada = Entrada::where('numero_entrada', $numEntrada)->first();
        // dd($entrada->vales);
        return view('verEntrada');
    }

    // NUEVO
    public function detalle($perfil, $id)
    {
        $entrada = Entrada::find($id);
        if (!$entrada)
            return redirect()->route("home")->with('error', 'No se encontró la información solicitada');
        $partes = PiezasEntrada::where('id_entrada', $id)->where('activo', 1)->get();
        // dd($partes);
        return view('verEntrada', compact('entrada', 'partes'));
    }

    public function getEntradas(Request $request, $perfil = null)
    {


        $fechaActual = now();
        $fechaHaceDosAnios = $fechaActual->copy()->subYears(2);

        $query = Entrada::with('siniestros.vehiculoInfo', 'vales.presupuestos')
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
            $query->where('estado', $request->estado);
        }

        return $query->select('*', DB::raw('created_at as fecha_recepcion'))->get();
    }

    public function getEntrada($perfil = null, $numeroEntrada)
    {
        $entrada = Entrada::where('numero_entrada', $numeroEntrada)
            ->with(['siniestros.vehiculoInfo', 'vales.presupuestos'])
            ->get();
        return $entrada;
    }

    public function consultarEntrada($perfil = null, $numEntrada)
    {

        // Obtener los datos de la entrada desde Firebird
        $entrada = Entrada::where('numero_entrada', $numEntrada)->with('siniestros.vehiculoInfo')->first();
        $piezasEntrada = PiezasEntrada::where('id_entrada', $entrada->id)
            ->where('activo', 1)
            ->with('piezas')
            ->get();
        return response()->json([
            'entrada' => $entrada,
            'piezas' => $piezasEntrada ? $piezasEntrada : null,
        ]);
    }

    private function idAutocarPorTaller(?string $taller)
    {
        $idAutocares = [
            'AUTOCAR PENSIONES' => 5815609,
            // 'AUTOCAR PENSIONES' => 5815609,
            'AUTOCAR PERIFERICO' => 343962763,
        ];

        return $idAutocares[$taller];
    }

    public function consultarEntradaW32(Request $request, $perfil = null, $numEntrada)
    {
        $entrada = new Entrada();
        $numVale = $request->input('numeroVale');
        $vale = null;
        $taller = null;
        $autocares = ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO'];
        $perfilSql = $this->formatPerfilToSQL($perfil);

        if (in_array($perfilSql, $autocares, true)) {
            $taller = $perfilSql;
        } elseif ($numVale) {
            $vale = $this->queryValePorNumeroEnPerfil($numVale, $request->input('idVale'))
                ->with('presupuestos.siniestros.vehiculoInfo')
                ->first();
            if ($vale?->presupuestos?->siniestros?->vehiculoInfo) {
                $taller = $vale->presupuestos->siniestros->vehiculoInfo->taller;
            }
        }

        $idPerfil = $this->idAutocarPorTaller($taller);
        if (!$idPerfil) {
            return response()->json([
                'firebird' => [],
                'presupuestos' => $vale?->presupuestos,
                'message' => 'No se pudo determinar el taller Autocar para consultar la entrada en W32.',
            ], 422);
        }

        try {
            $resultados = $entrada->consultarEntradaW32($numEntrada, $idPerfil);
        } catch (\Throwable $e) {
            Log::error('Error consultarEntradaW32: ' . $e->getMessage(), [
                'numEntrada' => $numEntrada,
                'idPerfil' => $idPerfil,
            ]);

            return response()->json([
                'firebird' => [],
                'presupuestos' => $vale?->presupuestos,
                'message' => 'Error al consultar la entrada en W32.',
            ], 500);
        }

        return response()->json([
            'firebird' => $resultados,
            'presupuestos' => $vale?->presupuestos,
        ]);
    }

    public function consultarEntradaW32All(Request $request, $perfil = null, $numeroVale)
    {
        $entrada = new Entrada();
        $entradas = [];
        $taller = null;

        // Obtener el taller del vale
        if ($numeroVale) {
            $vale = $this->queryValePorNumeroEnPerfil($numeroVale, $request->input('idVale'))
                ->with('presupuestos.siniestros.vehiculoInfo')
                ->first();
            if ($vale && $vale->presupuestos && $vale->presupuestos->siniestros && $vale->presupuestos->siniestros->vehiculoInfo) {
                $taller = $vale->presupuestos->siniestros->vehiculoInfo->taller;
            }
        }

        $idPerfil = $this->idAutocarPorTaller($taller);
        if (!$idPerfil) {
            return response()->json([], 422);
        }

        foreach ($request->all() as $numEntrada) {
            $entradas[] = $entrada->consultarEntradaW32($numEntrada, $idPerfil);
        }

        return response()->json($entradas);
    }

    public function notificarEntradaAsignada(Request $request)
    {
        // dd($request->all());
        $datos = [
            'numEntrada' => $request->input('numEntrada'),
            'numVale' => $request->input('numVale'),
            'linkEntrada' => $request->input('link')
        ];

        $vale = $this->queryValePorNumeroEnPerfil($request->input('numVale'), $request->input('idVale'))->first();
        $usuario = User::where('id', $vale->id_usuario_registro);

        Mail::to('programador4.ti@grupodc.com.mx')->send(new MailEntradaAsignada($datos));

        return response()->json([
            'success' => true,
            'title' => '¡Éxito!',
            'message' => 'Correo enviado exitosamente',
            'icon' => 'success'
        ]);
    }

    public function notificarLiberacionPartes(Request $request)
    {
        $datos = [
            'numEntrada' => $request->input('numEntrada'),
            'numParte' => $request->input('numParte'),
            'linkEntrada' => $request->input('link'),
            'cantidad' => $request->input('cantidad')
        ];
        $albaran = Albaran::where('id_vale', $request->input('idVale'))->first();
        $idUsuario = $albaran->id_usuario_registro;
        $usuario = User::find($idUsuario);

        Mail::to('programador4.ti@grupodc.com.mx')->send(new MailLiberacionPartes($datos));

        return response()->json([
            'success' => true,
            'title' => '¡Éxito!',
            'message' => 'Correo enviado exitosamente',
            'icon' => 'success'
        ]);
    }

    public function liberarParte(Request $request)
    {
        // dd($request->all(  ));
        // Buscar la entrada por su número
        $entrada = Entrada::where('numero_entrada', $request->numEntrada)->first();

        if (!$entrada) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró la entrada.'
            ], 404);
        }

        $piezaEntrada = PiezasEntrada::where('id_pieza', $request->idParte)
            ->where('id_entrada', $entrada->id)
            ->orderByDesc('cantidad') //Agarra el que tenga mayor cantidad en caso de haber más
            ->first();

        $cantidad = $request->cantidad;

        if (!$piezaEntrada) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'success' => false,
                'message' => 'No se encontró la pieza.'
            ], 404);
        }

        $piezaEntrada->activo = 0;
        $piezaEntrada->save();

        return response()->json([
            'title' => '¡Éxito!',
            'icon' => 'success',
            'success' => true,
            'message' => 'La pieza ha sido liberada correctamente.'
        ]);
    }
}
