<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;


class VehiculoController extends Controller
{
    public function getVehiculos()
    {
        return Vehiculo::all();
    }

    public function crear(Request $request)
    {
        try {
            $request->validate([
                'nombreVehiculo' => 'required|string|max:255|unique:vehiculos,nombre',
            ], [
                'nombreVehiculo.required' => 'El nombre del vehículo es obligatorio.',
                'nombreVehiculo.unique'   => "El vehículo \"{$request->nombreVehiculo}\" ya está registrado.",
                'nombreVehiculo.max'      => 'El nombre no puede superar los 255 caracteres.',
            ]);

            $vehiculo = Vehiculo::create([
                'nombre' => $request->nombreVehiculo,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Vehículo \"{$vehiculo->nombre}\" creado exitosamente.",
                'data'    => $vehiculo
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
                'message' => 'Error al crear el vehículo: ' . $e->getMessage()
            ], 500);
        }
    }
}
