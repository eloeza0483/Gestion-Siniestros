<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use Exception;

class ClienteController extends Controller
{
    public function crear(Request $request)
    {
        try {
            // El formulario frontend envía nombreCliente y codigoCliente
            $nombre = strtoupper($request->nombreCliente ?? '');
            $codigo = $request->codigoCliente ?? '';

            if (empty($nombre) || empty($codigo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nombre y el código del cliente son requeridos.'
                ], 422);
            }

            // Validar si el cliente ya existe por el código 
            $clienteExistente = Cliente::where('codigo', $codigo)->first();
            if ($clienteExistente) {
                return response()->json([
                    'success' => false,
                    'message' => "El cliente con el código {$codigo} ya se encuentra registrado."
                ], 422);
            }

            Cliente::create([
                'nombre' => $nombre,
                'codigo' => $codigo
            ]);

            return response()->json([
                'success' => true,
                'message' => "Cliente {$nombre} guardado exitosamente."
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al guardar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }
}
