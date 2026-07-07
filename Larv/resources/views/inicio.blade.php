@extends('layouts.app')

@section('content')
    {{-- Fondo ambiental exclusivo para Inicio --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden bg-gray-100 dark:bg-[#0b1120]">
        <div
            class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-500/20 dark:bg-blue-600/20 blur-[100px]">
        </div>
        <div
            class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500/20 dark:bg-cyan-600/15 blur-[100px]">
        </div>
        <div class="absolute inset-0 bg-white/30 dark:bg-gray-900/40 backdrop-blur-md"></div>
    </div>

    <div class="relative z-10 flex flex-col justify-center items-center h-[calc(100vh-64px)] overflow-hidden px-6">

        @if (isset($perfiles) && count($perfiles) > 0)
            {{-- Subtítulo --}}
            <p class="text-gray-400 dark:text-gray-400 text-lg mb-8 tracking-wide">
                Selecciona el perfil con el que deseas trabajar
            </p>

            <div class="flex flex-row flex-wrap justify-center gap-6 w-full max-w-5xl">
                @foreach ($perfiles as $perfilNav)
                    @php $nombre_url = str_replace(' ', '_', strtolower($perfilNav->nombre ?? '')); @endphp

                    <a href="{{ route('siniestros.view', $nombre_url) }}"
                        class="group flex flex-col items-center justify-center gap-4
                              w-full sm:w-56
                              bg-white/50 dark:bg-gray-800/40 backdrop-blur-lg
                              border border-gray-200/50 dark:border-gray-600/50
                              rounded-2xl p-10
                              hover:border-blue-500/50 dark:hover:border-blue-500/50
                              hover:bg-white/70 dark:hover:bg-gray-800/60
                              hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-500/10
                              transition-all duration-300 ease-in-out">

                        {{-- Ícono --}}
                        <div
                            class="w-16 h-16 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center
                                    group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors duration-300">
                            @if ($nombre_url === 'refacciones')
                                <i class="fa-solid fa-wrench text-blue-600 dark:text-blue-400 text-3xl"></i>
                            @else
                                <i class="fa-solid fa-car text-blue-600 dark:text-blue-400 text-3xl"></i>
                            @endif
                        </div>

                        {{-- Nombre --}}
                        <span
                            class="text-sm font-bold text-gray-700 dark:text-gray-200 text-center leading-snug
                                     group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300 uppercase tracking-widest">
                            {{ $perfilNav->nombre }}
                        </span>

                        {{-- Flecha --}}
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-all duration-300 translate-x-0 group-hover:translate-x-1"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center">
                <p class="text-gray-600 text-md">Sin perfiles asignados.</p>
                <p class="text-gray-500 text-sm mt-1">Contacta a sistemas.</p>
            </div>
        @endif

    </div>

    @if (session('error'))
        <script type="module">
            import {
                swalAlert
            } from '{{ asset('assets/js/functions/const.js') }}';
            document.addEventListener('DOMContentLoaded', function() {
                swalAlert({
                    icon: 'error',
                    title: 'Acceso Denegado',
                    html: '{!! session('error') !!}'
                });
            });
        </script>
    @endif

    @if (session('success'))
        <script type="module">
            import {
                swalAlert
            } from '{{ asset('assets/js/functions/const.js') }}';
            document.addEventListener('DOMContentLoaded', function() {
                swalAlert({
                    icon: 'success',
                    title: '¡Éxito!',
                    html: '{!! session('success') !!}'
                });
            });
        </script>
    @endif
@endsection
