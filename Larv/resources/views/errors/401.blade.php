@extends('layouts.app')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[calc(100vh-100px)] px-6 py-12">
        <div
            class="relative w-full max-w-lg text-center bg-white/40 dark:bg-gray-800/40 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 shadow-2xl rounded-3xl p-10 overflow-hidden">

            {{-- Efecto de luz interno --}}
            <div
                class="absolute top-0 left-1/2 transform -translate-x-1/2 w-3/4 h-32 bg-blue-500/20 blur-[50px] pointer-events-none">
            </div>

            <h1
                class="text-9xl font-bold font-['Outfit'] text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 drop-shadow-sm relative z-10">
                401
            </h1>
            <h2 class="text-3xl font-semibold text-gray-800 dark:text-gray-100 mt-4 font-['Outfit'] relative z-10">
                No autorizado
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg relative z-10">
                Debes iniciar sesión para poder acceder a este recurso.
            </p>

            <div class="mt-8 relative z-10">
                <a href="{{ url('/') }}"
                    class="inline-flex items-center justify-center px-6 py-3 text-base font-medium text-white transition-all duration-300 bg-blue-600 hover:bg-blue-500 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 shadow-lg shadow-blue-500/30 rounded-xl hover:-translate-y-1">
                    <i class="fa-solid fa-house mr-2"></i>
                    Volver al Inicio
                </a>
            </div>
        </div>
    </div>
@endsection
