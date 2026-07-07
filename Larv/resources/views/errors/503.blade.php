@extends('layouts.app')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[calc(100vh-100px)] px-6 py-12">
        <div
            class="relative w-full max-w-lg text-center bg-white/40 dark:bg-gray-800/40 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 shadow-2xl rounded-3xl p-10 overflow-hidden">

            {{-- Efecto de luz interno --}}
            <div
                class="absolute top-0 left-1/2 transform -translate-x-1/2 w-3/4 h-32 bg-gray-500/20 blur-[50px] pointer-events-none">
            </div>

            <h1
                class="text-9xl font-bold font-['Outfit'] text-transparent bg-clip-text bg-gradient-to-r from-gray-500 to-slate-400 drop-shadow-sm relative z-10">
                503
            </h1>
            <h2 class="text-3xl font-semibold text-gray-800 dark:text-gray-100 mt-4 font-['Outfit'] relative z-10">
                Servicio no disponible
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg relative z-10">
                El sistema se encuentra en mantenimiento en este momento. Por favor vuelve a intentarlo en unos minutos.
            </p>

            <div class="mt-8 relative z-10">
                <button onclick="window.location.reload()"
                    class="inline-flex items-center justify-center px-6 py-3 text-base font-medium text-white transition-all duration-300 bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-800 shadow-lg shadow-gray-500/30 rounded-xl hover:-translate-y-1">
                    <i class="fa-solid fa-rotate-right mr-2"></i>
                    Recargar página
                </button>
            </div>
        </div>
    </div>
@endsection
