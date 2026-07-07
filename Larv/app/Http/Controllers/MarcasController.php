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
            ], [
                'nombreMarca.required' => 'El nombre de la marca es obligatorio.',
                'nombreMarca.unique'   => "La marca \"{$request->nombreMarca}\" ya está registrada.",
                'nombreMarca.max'      => 'El nombre no puede superar los 255 caracteres.',
            ]);

            $marca = Marcas::create([
                'nombre' => $request->nombreMarca,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Marca \"{$marca->nombre}\" creada exitosamente.",
                'data'    => $marca
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la marca: ' . $e->getMessage()
            ], 500);
        }
    }
}
