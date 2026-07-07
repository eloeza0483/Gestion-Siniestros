<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\W32;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

class PiezaFirebirdController extends Controller
{
    public function consultarDatosW32(Request $request, $perfil = null)
    {

        $codigo = $request->codigo;
        $numeroParte = $request->numeroParte;

        try {
            $pdo = null;
            // Si el perfil es autocar_pensiones, usamos la conexión de Autocar
            // if ($perfil === 'autocar_pensiones') {
            $pdo = W32::coneccionMontecristo();
            // } else {
            // $pdo = W32::coneccionAutocar();
            // }

            // Consulta utilizando PDO raw para evitar problemas con el driver de Laravel
            $sql =  "SELECT id,fam,
            subfam,marca,estante,
            codigoart as PART_NUMBER, descrip as DESCRIPTION,
            stock as stock,

            (pvp1) AS cost,
            precompconce as precio_concesionario,
            linea
            FROM articulo
            WHERE codigoart = :numeroParte
            AND minempresas = 5815609   
               ";

            $query = $pdo->prepare($sql);
            $query->execute(['numeroParte' => $numeroParte]);
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            // dd($results);
            //             0 => array:11 [
            //     "ID" => 1311314146
            //     "FAM" => "ORIGINALES"
            //     "SUBFAM" => ""
            //     "MARCA" => "CHEVROLET"
            //     "ESTANTE" => ""
            //     "PART_NUMBER" => "23775767"
            //     "DESCRIPTION" => "ABRAZADERA,SPTSALPICADERODFNSDLNT"
            //     "STOCK" => "15"
            //     "COST" => "178.94"
            //     "PRECIO_CONCESIONARIO" => "128.84"
            //     "LINEA" => "23A"
            //   ]
            // ]

            // dd($results);



            if (count($results) > 0) {
                $clientes = [];
                // dd($codigo);
                if (!empty(trim($codigo))) {
                    $stmt = $pdo->prepare("SELECT cl.codigo, cl.cf1, cl.apellido1, cl.apellido2,
                    gl.descripcion AS grupo, cg.precio AS tipo_precio, cg.cargo as cargo_descuento
                    FROM cligrupo cg
                    INNER JOIN clientes cl ON cg.id_cliente = cl.id
                    LEFT JOIN grulinea gl ON cg.id_grulinea = gl.id
                    WHERE cl.codigo = :codigo");
                    $stmt->execute(['codigo' => trim($codigo)]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($rows as $r) {
                        $clientes[] = [
                            'CODIGO' => $r['CODIGO'] ?? '',
                            'CLIENTE' => trim(($r['CF1'] ?? '') . ' ' . ($r['APELLIDO1'] ?? '') . ' ' . ($r['APELLIDO2'] ?? '')),
                            'GRUPO' => !empty($r['GRUPO']) ? $r['GRUPO'] : '',
                            'CARGO_DESCUENTO' => $r['CARGO_DESCUENTO'] ?? 0,
                            'TIPO_PRECIO' => $r['TIPO_PRECIO'] ?? 0,
                        ];
                    }
                }


                $conteo_clientes = count($clientes);

                $resultado = $results[0];

                $lineas = $pdo->query("select l.linea FROM lineagru l
            inner join grulinea g on g.id = l.id_grulinea
            where g.descripcion = 'COLISION';")->fetchAll();

                $array_lineas_colision = array_map(function ($linea) {
                    return $linea['LINEA'];
                }, $lineas);

                // Determinar si la pieza pertenece a una línea del grupo COLISIÓN
                $lineaPieza = $resultado['LINEA'] ?? '';
                $enLineaColision = in_array($lineaPieza, $array_lineas_colision);

                // Función auxiliar para calcular importe
                // El cliente tiene 2 registros con mismo TIPO_PRECIO pero diferente GRUPO:
                //   GRUPO = 'COLISION' → descuento específico para piezas de colisión
                //   GRUPO = ''         → descuento general
                // Se selecciona el registro correcto según $enLineaColision
                $calcularImporte = function () use ($resultado, $clientes, $conteo_clientes, $enLineaColision) {
                    if ($conteo_clientes === 0) return 0;

                    if ($enLineaColision) {
                        // Pieza en línea COLISIÓN → tomar el registro con GRUPO = 'COLISION'
                        $clienteColision = array_values(array_filter($clientes, fn($f) => strtoupper(trim($f['GRUPO'])) === 'COLISION'));
                        $porcentaje = count($clienteColision) > 0 ? ($clienteColision[0]['CARGO_DESCUENTO'] ?? 0) : 0;
                    } else {
                        // Pieza fuera de línea COLISIÓN → tomar el registro SIN grupo (general)
                        $clienteGeneral = array_values(array_filter($clientes, fn($f) => empty(trim($f['GRUPO']))));
                        $porcentaje = count($clienteGeneral) > 0 ? ($clienteGeneral[0]['CARGO_DESCUENTO'] ?? 0) : 0;
                    }

                    $base = $resultado['PRECIO_CONCESIONARIO'] ?? 0;
                    return round($base * (1 + ($porcentaje / 100)), 2);
                };

                $resultado['IMPORTE']          = $calcularImporte();
                $resultado['EN_LINEA_COLISION'] = $enLineaColision;

                // No hay registros PVP para este cliente (ambos son 'Precio concesionario')
                // Se mantiene la estructura por compatibilidad con el frontend
                $tipos_precio = array_column($clientes, 'TIPO_PRECIO');
                $tiene_pvp = in_array('PVP', $tipos_precio);

                $resultado['TIENE_PVP']    = $tiene_pvp;
                $resultado['IMPORTE_PVP']  = $resultado['IMPORTE'];

                return response()->json($this->toUtf8($resultado));
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la pieza en la base de datos'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar datos en Firebird: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Convierte recursivamente todos los strings de un array de Firebird
     * (ISO-8859-1 / WIN-1252) a UTF-8 para que json() no falle.
     */
    private function toUtf8(mixed $valor): mixed
    {
        if (is_array($valor)) {
            return array_map([$this, 'toUtf8'], $valor);
        }

        if (is_string($valor) && !mb_check_encoding($valor, 'UTF-8')) {
            return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
        }

        return $valor;
    }
}
