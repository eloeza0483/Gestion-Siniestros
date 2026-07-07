<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PDO;

class W32 extends Model
{
    public static function coneccionMontecristo()
    {
        $pdo = new PDO("firebird:dbname=192.168.15.210:MMW32;charset=UTF-8", 'SYSDBA', 'masterkey');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    public static function coneccionAutocar()
    {
        $pdo = new PDO("firebird:dbname=192.168.15.234:MMW32_AUT;charset=UTF-8", 'SYSDBA', 'masterkey');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    private static function cleanUtf8($result)
    {
        if (!$result || !is_array($result)) return $result;
        foreach ($result as $key => $value) {
            if (is_string($value)) {
                $result[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
            }
        }
        return $result;
    }

    /**
     * Verifica si una orden está facturada en Firebird (W32).
     * @param string|int $num_orden  Número de orden (CODIGOHT)
     * @param int        $id_w32    MINEMPRESAS del perfil (ej. 5815609)
     * @return array|false|null      Fila con datos de factura, false si no existe, null si hay error
     */
    public static function isFacturadoByNumOrdenAC($num_orden, $id_w32)
    {
        try {
            $pdo = self::coneccionAutocar();

            $sql = "SELECT ORD.CODIGOHT as NUMERO_ORDEN, ORD.FECFAC as FECHA_FACTURA,
                           CB.SERIE, CB.NUMERO as NUMERO_FACTURA, CB.NOMFIS as ASEGURADORA,
                           CB.FECHA, CB.BRUTO as IMP_BRUTO, CB.TOTFAC as IMP_TOTAL, CB.IMPIVA1
                    FROM HTRACABE ORD
                    INNER JOIN CABFACTU CB ON ORD.id = COALESCE(CB.jefeventas, CB.ctacte)
                        AND ORD.minempresas = CB.minempresas
                    INNER JOIN HTRACAB2 ht2 ON ht2.mi1htracabe = ORD.id
                    WHERE ORD.CODIGOHT = $num_orden AND cb.facht = 'T'
                    AND ORD.minempresas = $id_w32";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            return self::cleanUtf8($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error en isFacturado W32: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica si un albarán (Refacciones) está facturado en Firebird (W32) Montecristo.
     * @param string|int $num_albaran
     * @param int        $id_w32
     * @return array|false|null
     */
    public static function isFacturadoByAlbaranRef($num_albaran)
    {
        try {
            $pdo = self::coneccionMontecristo();

            $sql = "SELECT cf.numero, cf.numfact, cf2.numero as numero_factura, cf2.numfact as numfact2
                    FROM cabfactu as cf
                    INNER JOIN cabfactu cf2 on cf.numfact = CAST(cf2.id AS VARCHAR(20))
                    WHERE cf.numero = :num_albaran
                    AND cf.minempresas = 5815609";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':num_albaran' => $num_albaran
            ]);

            return self::cleanUtf8($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error en isFacturadoByAlbaranRef W32: " . $e->getMessage());
            return null;
        }
    }
}
