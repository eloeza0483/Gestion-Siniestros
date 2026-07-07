<!DOCTYPE html>
<html lang="es" class="dark">
{{-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> --}}

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <meta name="api-active-url" content="{{ env('API_ACTIVE') }}"> --}}

    <title>Gestión siniestros</title>
    {{-- <title>{{ config('app.name', 'Gestión siniestros') }}</title> --}}

    <!-- Scripts -->
    {{-- <!-- <script src="{{asset('assets/')}}"></script> PLANTILLA--> --}}
    {{-- <!-- <script src="{{asset('assets/js/lib/tailwind.js')}}"></script> --> --}}
    <!-- <script src="Larv/resources/js/lib/darkModeToggle.js"></script> -->
    <script src="{{ asset('assets/js/lib/sweetalert2.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module" src="{{ asset('assets/js/functions/app.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/lib/darkModeToggle.js') }}"></script>
    @stack('scripts')
</head>


<body
    class="font-['Inter'] antialiased text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-[#0b1120] relative min-h-screen selection:bg-blue-500 selection:text-white">
    {{-- Luces de ambiente (Efecto Mesh/Gradient) detrás del vidrio --}}
    <div class="fixed inset-0 z-[-1] w-full h-full overflow-hidden pointer-events-none">
        <div
            class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-500/20 dark:bg-blue-600/20 blur-[100px]">
        </div>
        <div
            class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500/20 dark:bg-cyan-600/15 blur-[100px]">
        </div>
    </div>

    {{-- Capa principal de vidrio esmerilado (Glassmorphism) fondo transparente --}}
    <div class="fixed inset-0 z-[-1] bg-white/30 dark:bg-gray-900/40 backdrop-blur-md pointer-events-none"></div>

    {{-- Contenedor de contenido sin filtros (Soluciona bug de z-index de los Modales Flowbite) --}}
    <div class="min-h-screen border-b border-transparent">
        @if (!in_array(Route::currentRouteName(), ['login']))
            <x-navbar />
        @endif
        <!-- Page Content -->
        <main>
            @if (session('error'))
                <div class="bg-red-500 py-3  text-center text-white text-lg">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('message'))
                <div class="bg-orange-500 py-3  text-center text-white text-lg">
                    {{ session('message') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>

</html>
