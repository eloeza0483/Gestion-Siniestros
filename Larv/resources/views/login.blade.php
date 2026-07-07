@extends('layouts.app')
@push('scripts')
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <!-- <script src="{{ asset('assets/js/lib/tailwind.js') }}"></script> -->
    <script src="{{ asset('assets/js/lib/sweetalert2.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/lib/darkModeToggle.js') }}"></script>
@endpush

@section('content')
    <section
        class="relative min-h-screen flex items-center justify-center bg-transparent overflow-hidden transition-colors duration-300">

        {{-- Fondo ambiental exclusivo para Login --}}
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden bg-gray-100 dark:bg-[#0b1120]">
            <div
                class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-500/20 dark:bg-blue-600/20 blur-[100px]">
            </div>
            <div
                class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500/20 dark:bg-cyan-600/15 blur-[100px]">
            </div>
            <div class="absolute inset-0 bg-white/30 dark:bg-gray-900/40 backdrop-blur-md"></div>
        </div>

        <div class="relative z-10 w-full max-w-lg px-6 py-12 lg:px-8">
            <div class="mb-10 text-center">
                <a href="#" class="inline-block transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('assets/img/portada.png') }}" alt="logo"
                        class="w-auto h-auto max-w-[320px] mx-auto drop-shadow-lg">
                </a>
            </div>

            <!-- Tarjeta principal con efecto "Glassmorphism" suave y esquinas muy redondeadas -->
            <div
                class="bg-white/50 dark:bg-gray-800/40 backdrop-blur-lg rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-gray-200/50 dark:border-gray-600/50 overflow-hidden transform transition-all">
                <div class="p-8 sm:p-14">
                    <div class="mb-10 text-center">
                        <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-gray-900 dark:text-white">
                            Bienvenido
                        </h1>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 font-medium">
                            Ingresa tus credenciales para acceder al sistema
                        </p>
                    </div>

                    <form id="loginForm" class="space-y-8" action="#">
                        <div class="space-y-3">
                            <label for="usuario"
                                class="block text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 ml-1">Usuario</label>
                            <div class="relative group">
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none transition-colors group-focus-within:text-blue-500">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-focus-within:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <input type="text" name="usuario" id="usuario"
                                    class="w-full pl-11 pr-4 py-4 bg-white/50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:bg-white/80 dark:hover:bg-gray-800/60 font-medium shadow-sm placeholder-gray-400"
                                    placeholder="Escribe tu usuario..." required="">
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label for="password"
                                class="block text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 ml-1">Contraseña</label>
                            <div class="relative group">
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none transition-colors group-focus-within:text-blue-500">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-focus-within:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                    </svg>
                                </div>
                                <input type="password" name="password" id="password" placeholder="••••••••"
                                    class="w-full pl-11 pr-4 py-4 bg-white/50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:bg-white/80 dark:hover:bg-gray-800/60 font-medium tracking-widest shadow-sm placeholder-gray-400"
                                    required="">
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" id="loginButton"
                                class="group relative flex w-full justify-center items-center gap-3 rounded-2xl bg-blue-600 px-4 py-4.5 text-base font-bold text-white shadow-lg shadow-blue-500/30 transition-all duration-300 hover:bg-blue-500  hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 active:scale-[0.98]">
                                Acceder al sistema
                                <svg class="h-6 w-6 text-white/70 group-hover:text-white transition-all group-hover:translate-x-1"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="mt-8 text-center text-xs text-gray-400 dark:text-gray-500 font-medium">
                &copy; 2026 Gestión de Siniestros. Todos los derechos reservados.
            </p>
        </div>
    </section>
@endsection
