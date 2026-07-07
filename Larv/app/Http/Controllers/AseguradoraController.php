<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aseguradora;

class AseguradoraController extends Controller
{
    public function getAseguradoras()
    {
        return Aseguradora::all();
    }

    public function crear(Request $request)
    {
        try {
            $request->validate([
                'nombreAseguradora' => 'required|string|max:255|unique:aseguradoras,nombre',
            ], [
                'nombreAseguradora.required' => 'El nombre de la aseguradora es obligatorio.',
                'nombreAseguradora.unique'   => "La aseguradora \"{$request->nombreAseguradora}\" ya está registrada.",
                'nombreAseguradora.max'      => 'El nombre no puede superar los 255 caracteres.',
            ]);

            $aseguradora = Aseguradora::create([
                'nombre' => $request->nombreAseguradora,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Aseguradora \"{$aseguradora->nombre}\" creada exitosamente.",
                'data'    => $aseguradora
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
                'message' => 'Error al crear la aseguradora: ' . $e->getMessage()
            ], 500);
        }
    }
}
