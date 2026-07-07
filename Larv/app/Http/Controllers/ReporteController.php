<?php

namespace App\Http\Controllers;

use App\Models\Entrada;
use App\Models\Factura;
use App\Models\Perfile;
use App\Models\Presupuesto;
use App\Models\Reporte;
use App\Models\Siniestro;
use App\Models\Vale;
use App\Http\Traits\SiniestroTrait;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    use SiniestroTrait;

    public function getReportesView()
    {
        $nombre_perfil = $this->formatPerfilToPermisos(request()->route('perfil') ?? '');
        $this->authorize("viewReportes{$nombre_perfil}", Reporte::class);

        $perfiles = Perfile::all();
        return view('reportes', compact('perfiles'));
    }

    public function getReportes(Request $request, $perfil = null)
    {
        $nombre_perfil = $this->formatPerfilToPermisos($perfil ?? '');
        $this->authorize("viewReportes{$nombre_perfil}", Reporte::class);

        $taller = $request->input('taller');
        $entidad = $request->input('entidad');
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_final = $request->input('fecha_final');
        $estado = $request->input('estado');
        $estado = $request->input('estado');

        // Definir variables para relaciones y campos
        $modelo = null;
        $relacionTaller = null;

        switch ($entidad) {
            case 'siniestros':
                $modelo = Siniestro::query();
                break;
            case 'presupuestos':
                $modelo = Presupuesto::query();
                break;
            case 'vales':
                $modelo = Vale::query();
                break;
            case 'entradas':
                $modelo = Entrada::query();
                break;
            case 'albaranes':
                $modelo = \App\Models\Albaran::query();
                break;
            default:
                return response()->json([]);
        }

        $modelo = $modelo->reportes();

        // Filtro taller 
        if ($taller && $taller !== 'Selecciona un taller' && $taller !== 'Todos') {
            if (strtoupper($taller) === 'REFACCIONES') {
                if ($entidad === 'siniestros') {
                    $modelo->where(function ($q) {
                        $q->whereExists(function ($sub) {
                            $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                                ->from('presupuestos')
                                ->whereColumn('presupuestos.id_siniestro', 'siniestros.id')
                                ->where('presupuestos.proveedor', 'CHEVROLET');
                        })->orWhere('siniestros.perfil_id', 3);
                    });
                } else {
                    $modelo->where(function ($q) {
                        $q->where('presupuestos.proveedor', 'CHEVROLET')
                          ->orWhere('siniestros.perfil_id', 3);
                    });
                }
            } else {
                $modelo->where('vehiculo_info.taller', $taller);
            }
        }

        // Filtrar estado
        if ($estado && strtolower($estado) !== 'todos') {
            $modelo->where($entidad . '.estado', $estado);
        }

        // Filtrar por fechas
        $ignorar_fechas = $request->input('ignorar_fechas');
        if ($ignorar_fechas) {
            $modelo->where($entidad . '.created_at', '>=', '2026-01-01 00:00:00');
        } else {
            if ($fecha_inicio && $fecha_final) {
                $f_inicio = $fecha_inicio . ' 00:00:00';
                $f_final = $fecha_final . ' 23:59:59';
                $modelo->whereBetween($entidad . '.created_at', [$f_inicio, $f_final]);
            }
        }

        $resultados = $modelo->get();

        return response()->json($resultados);
    }

    // Retorna estadísticas agregadas para el dashboard de gráficas
    /**
     * Retorna las estadísticas generales y datos de gráficas filtrados por taller, fecha, entidad y estado.
     */
    public function getStats(Request $request, $perfil = null)
    {
        $nombre_perfil = $this->formatPerfilToPermisos($perfil ?? '');
        $this->authorize("viewReportes{$nombre_perfil}", Reporte::class);

        $taller = $request->input('taller');
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_final = $request->input('fecha_final');
        $ignorar_fechas = $request->input('ignorar_fechas');
        $entidad = $request->input('entidad');
        $estado = $request->input('estado');

        // Normalizar fechas para buscar hasta el final del día
        $f_inicio = $fecha_inicio ? $fecha_inicio . ' 00:00:00' : null;
        $f_final = $fecha_final ? $fecha_final . ' 23:59:59' : null;

        // Base: filtro de taller en vehiculo_info
        $filtroTaller = ($taller && $taller !== 'Todos' && $taller !== 'Selecciona un taller');

        // Función auxiliar para aplicar el filtro de fecha
        $aplicarFiltroFecha = function ($query, $campo) use ($ignorar_fechas, $f_inicio, $f_final) {
            if ($ignorar_fechas) {
                $query->where($campo, '>=', '2026-01-01 00:00:00');
            } elseif ($f_inicio && $f_final) {
                $query->whereBetween($campo, [$f_inicio, $f_final]);
            }
        };

        // Función auxiliar para aplicar el filtro de taller
        $aplicarFiltroTaller = function ($query, $base) use ($filtroTaller, $taller) {
            if (!$filtroTaller) {
                return;
            }

            if (strtoupper($taller) === 'REFACCIONES') {
                if ($base === 'siniestros' || $base === 'entradas' || $base === 'albaranes') {
                    $query->where(function ($q) {
                        $q->whereExists(function ($sub) {
                            $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                                ->from('presupuestos')
                                ->whereColumn('presupuestos.id_siniestro', 'siniestros.id')
                                ->where('presupuestos.proveedor', 'CHEVROLET');
                        })->orWhere('siniestros.perfil_id', 3);
                    });
                } elseif ($base === 'vales') {
                    $query->where(function ($q) {
                        $q->where('presupuestos.proveedor', 'CHEVROLET')
                          ->orWhere('siniestros.perfil_id', 3);
                    });
                }
            } else {
                $query->where('vehiculo_info.taller', $taller);
            }
        };

        // Función auxiliar para aplicar el filtro de estado basado en la entidad seleccionada
        $aplicarFiltroEstado = function ($query, $base) use ($entidad, $estado) {
            if (!$estado || strtolower($estado) === 'todos') {
                return;
            }

            if ($base === 'siniestros') {
                switch ($entidad) {
                    case 'siniestros':
                        $query->where('siniestros.estado', $estado);
                        break;
                    case 'presupuestos':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('presupuestos')
                              ->whereColumn('presupuestos.id_siniestro', 'siniestros.id')
                              ->where('presupuestos.estado', $estado);
                        });
                        break;
                    case 'vales':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('presupuestos')
                              ->join('vales', 'presupuestos.id', '=', 'vales.id_presupuesto')
                              ->whereColumn('presupuestos.id_siniestro', 'siniestros.id')
                              ->where('vales.estado', $estado);
                        });
                        break;
                    case 'entradas':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('entradas')
                              ->whereColumn('entradas.id_siniestro', 'siniestros.id')
                              ->where('entradas.estado', $estado);
                        });
                        break;
                    case 'albaranes':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('albaranes')
                              ->whereColumn('albaranes.id_siniestro', 'siniestros.id')
                              ->where('albaranes.estado', $estado);
                        });
                        break;
                }
            } elseif ($base === 'vales') {
                switch ($entidad) {
                    case 'siniestros':
                        $query->where('siniestros.estado', $estado);
                        break;
                    case 'presupuestos':
                        $query->where('presupuestos.estado', $estado);
                        break;
                    case 'vales':
                        $query->where('vales.estado', $estado);
                        break;
                    case 'entradas':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('entradas')
                              ->whereColumn('entradas.id_vale', 'vales.id')
                              ->where('entradas.estado', $estado);
                        });
                        break;
                    case 'albaranes':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('albaranes')
                              ->whereColumn('albaranes.id_vale', 'vales.id')
                              ->where('albaranes.estado', $estado);
                        });
                        break;
                }
            } elseif ($base === 'entradas') {
                switch ($entidad) {
                    case 'siniestros':
                        $query->where('siniestros.estado', $estado);
                        break;
                    case 'presupuestos':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('presupuestos')
                              ->whereColumn('presupuestos.id_siniestro', 'entradas.id_siniestro')
                              ->where('presupuestos.estado', $estado);
                        });
                        break;
                    case 'vales':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('vales')
                              ->whereColumn('vales.id', 'entradas.id_vale')
                              ->where('vales.estado', $estado);
                        });
                        break;
                    case 'entradas':
                        $query->where('entradas.estado', $estado);
                        break;
                    case 'albaranes':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('albaranes')
                              ->whereColumn('albaranes.id_vale', 'entradas.id_vale')
                              ->where('albaranes.estado', $estado);
                        });
                        break;
                }
            } elseif ($base === 'albaranes') {
                switch ($entidad) {
                    case 'siniestros':
                        $query->where('siniestros.estado', $estado);
                        break;
                    case 'presupuestos':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('presupuestos')
                              ->whereColumn('presupuestos.id_siniestro', 'albaranes.id_siniestro')
                              ->where('presupuestos.estado', $estado);
                        });
                        break;
                    case 'vales':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('vales')
                              ->whereColumn('vales.id', 'albaranes.id_vale')
                              ->where('vales.estado', $estado);
                        });
                        break;
                    case 'entradas':
                        $query->whereExists(function ($q) use ($estado) {
                            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('entradas')
                              ->whereColumn('entradas.id_vale', 'albaranes.id_vale')
                              ->where('entradas.estado', $estado);
                        });
                        break;
                    case 'albaranes':
                        $query->where('albaranes.estado', $estado);
                        break;
                }
            }
        };

        // ── KPIs generales ────────────────────────────────────────────────────
        $qSiniestros = Siniestro::join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id');
        $aplicarFiltroTaller($qSiniestros, 'siniestros');
        $aplicarFiltroFecha($qSiniestros, 'siniestros.created_at');
        $aplicarFiltroEstado($qSiniestros, 'siniestros');
        $totalSiniestros = $qSiniestros->count();

        $qVales = Vale::join('presupuestos', 'vales.id_presupuesto', '=', 'presupuestos.id')
            ->join('siniestros', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id');
        $aplicarFiltroTaller($qVales, 'vales');
        $aplicarFiltroFecha($qVales, 'vales.created_at');
        $aplicarFiltroEstado($qVales, 'vales');
        $totalVales = $qVales->count();

        $qEntradas = Entrada::join('siniestros', 'entradas.id_siniestro', '=', 'siniestros.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id');
        $aplicarFiltroTaller($qEntradas, 'entradas');
        $aplicarFiltroFecha($qEntradas, 'entradas.created_at');
        $aplicarFiltroEstado($qEntradas, 'entradas');
        $totalEntradas = $qEntradas->count();

        $qAlbaranes = \App\Models\Albaran::join('siniestros', 'albaranes.id_siniestro', '=', 'siniestros.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id');
        $aplicarFiltroTaller($qAlbaranes, 'albaranes');
        $aplicarFiltroFecha($qAlbaranes, 'albaranes.created_at');
        $aplicarFiltroEstado($qAlbaranes, 'albaranes');
        $totalAlbaranes = $qAlbaranes->count();

        // ── Siniestros por estado ─────────────────────────────────────────────
        $qEstados = Siniestro::join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id')
            ->select('siniestros.estado', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'));
        $aplicarFiltroTaller($qEstados, 'siniestros');
        $aplicarFiltroFecha($qEstados, 'siniestros.created_at');
        $aplicarFiltroEstado($qEstados, 'siniestros');
        $porEstado = $qEstados->groupBy('siniestros.estado')->get();

        // ── Siniestros por taller ─────────────────────────────────────────────
        $qTalleres = Siniestro::join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id')
            ->select('vehiculo_info.taller', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'));
        $aplicarFiltroTaller($qTalleres, 'siniestros');
        $aplicarFiltroFecha($qTalleres, 'siniestros.created_at');
        $aplicarFiltroEstado($qTalleres, 'siniestros');
        $porTaller = $qTalleres->groupBy('vehiculo_info.taller')->orderByDesc('total')->limit(8)->get();

        // ── Vales por mes (últimos 6 meses) ───────────────────────────────────
        $qMeses = Vale::join('presupuestos', 'vales.id_presupuesto', '=', 'presupuestos.id')
            ->join('siniestros', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id')
            ->select(
                \Illuminate\Support\Facades\DB::raw('DATE_FORMAT(vales.created_at, "%Y-%m") as mes'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as total')
            )
            ->where('vales.created_at', '>=', now()->subMonths(6));
        $aplicarFiltroTaller($qMeses, 'vales');
        $aplicarFiltroEstado($qMeses, 'vales');
        $porMes = $qMeses->groupBy('mes')->orderBy('mes')->get();

        // ── Importe total vales ────────────────────────────────────────────────
        $qImporte = Vale::join('presupuestos', 'vales.id_presupuesto', '=', 'presupuestos.id')
            ->join('siniestros', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id');
        $aplicarFiltroTaller($qImporte, 'vales');
        $aplicarFiltroFecha($qImporte, 'vales.created_at');
        $aplicarFiltroEstado($qImporte, 'vales');
        $importeTotal = $qImporte->sum('vales.total');

        return response()->json([
            'kpis' => [
                'siniestros' => $totalSiniestros,
                'vales' => $totalVales,
                'entradas' => $totalEntradas,
                'albaranes' => $totalAlbaranes,
                'importe' => $importeTotal,
            ],
            'por_estado' => $porEstado,
            'por_taller' => $porTaller,
            'por_mes' => $porMes,
        ]);
    }

    /**
     * Muestra la vista del reporte de refacciones.
     */
    public function getReporteRefaccionesView()
    {
        if (!auth()->user()->tienePermiso('reporteRefacciones')) {
            abort(403, 'No tienes permiso para acceder a este reporte.');
        }

        $perfiles = Perfile::all();
        return view('reporteRefacciones', compact('perfiles'));
    }

    /**
     * Retorna los datos del reporte de refacciones en formato JSON para el Datatable.
     */
    public function getReporteRefaccionesData(Request $request)
    {
        if (!auth()->user()->tienePermiso('reporteRefacciones')) {
            return response()->json([]);
        }

        $query = Vale::join('presupuestos', 'vales.id_presupuesto', '=', 'presupuestos.id')
            ->join('siniestros', 'presupuestos.id_siniestro', '=', 'siniestros.id')
            ->join('vehiculo_info', 'siniestros.id_vehiculo', '=', 'vehiculo_info.id')
            ->select([
                'presupuestos.fecha_cotizado AS fecha_cotizacion',
                'vehiculo_info.vehiculo',
                'vehiculo_info.modelo AS anio',
                \DB::raw('(SELECT COALESCE(SUM(piezas.numero_pzas_presupuesto), 0) FROM piezas WHERE piezas.id_presupuesto = presupuestos.id) AS n_piezas_cotizadas'),
                'presupuestos.total AS importe_cotizado',
                'vehiculo_info.aseguradora AS seguro',
                'vales.numero_vale AS numero_solicitud',
                'vehiculo_info.taller',
                'vales.fecha_vale AS fecha_vale',
                \DB::raw('(SELECT COALESCE(SUM(piezas_vales.cantidad), 0) FROM piezas_vales WHERE piezas_vales.id_vale = vales.id AND piezas_vales.activo = 1) AS n_piezas_autorizadas'),
                'vales.total AS importe_autorizado'
            ]);

        // Aplicar filtros de taller
        $taller = $request->input('taller');
        if ($taller) {
            if (is_array($taller)) {
                $taller = array_filter($taller, function ($t) {
                    return $t !== 'Todos' && $t !== 'Selecciona un taller' && !empty($t);
                });
                if (!empty($taller)) {
                    $query->whereIn('vehiculo_info.taller', $taller);
                }
            } elseif ($taller !== 'Todos' && $taller !== 'Selecciona un taller') {
                if (strtoupper($taller) === 'REFACCIONES') {
                    $query->whereNotIn('vehiculo_info.taller', ['AUTOCAR PENSIONES', 'AUTOCAR PERIFERICO']);
                } else {
                    $query->where('vehiculo_info.taller', $taller);
                }
            }
        }

        // Aplicar filtros de fecha del vale
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_final = $request->input('fecha_final');
        if ($fecha_inicio && $fecha_final) {
            $f_inicio = $fecha_inicio . ' 00:00:00';
            $f_final = $fecha_final . ' 23:59:59';
            $query->whereBetween('vales.created_at', [$f_inicio, $f_final]);
        }

        $resultados = $query->get();

        return response()->json($resultados);
    }
}

