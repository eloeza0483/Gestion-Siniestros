<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marcas;


class MarcasController extends Controller
{
    public function getMarcas()
    {
        return Marcas::all();
    }

    public function crear(Request $request)
    {
        try {
            $request->validate([
                'nombreMarca' => 'required|string|max:255|unique:marcas,nombre',
            ]);

            $marca = Marcas::create([
                'nombre' => $request->nombreMarca,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Marca creada exitosamente',
                'data' => $marca
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La marca ya existe o los datos son inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la marca: ' . $e->getMessage()
            ], 500);
        }
    }
}
