<?php

namespace App\Http\Controllers;

use App\Models\PermisosD;
use App\Models\Proyectos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validación de la API externa
        $url = env('API_ACTIVE');
        $response = Http::withoutVerifying()->acceptJson()->post($url, [
            'username' => $request->usuario,
            'password' => $request->password,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos.',
                'icon'    => 'warning'
            ]);
        }

        $userData = $response->json('user');

        if ($this->tieneAccesoAlProyecto($userData['id'])) {
            if (Auth::loginUsingId($userData['id'])) {
                $user = Auth::user();
                if ($user) {
                    $nombInicial = substr($user->name, 0, 1);
                    $apeInicial = substr($user->first_name, 0, 1);
                    $iniciales = $nombInicial . $apeInicial;
                    session(['user_initials' => $iniciales]);
                }

                return response()->json([
                    'success'     => true,
                    'message'     => "Bienvenido, {$userData['fullname']}",
                    'icon'        => 'success',
                    'urlintended' => session('url.intended', route('home'))
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No tienes permisos para acceder a GestionSiniestros.',
            'icon'    => 'info'
        ]);
    }

    private function tieneAccesoAlProyecto($userId)
    {
        return DB::connection('mysqlGTI')
            ->table('permiso_desarrollos as PD')
            ->join('proyectos as P', 'P.id', '=', 'PD.id_proyecto')
            ->where('P.nombre', 'GestionSiniestros')
            ->where('PD.id_usuario', $userId)
            ->where('PD.activo', 1)
            ->exists();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
