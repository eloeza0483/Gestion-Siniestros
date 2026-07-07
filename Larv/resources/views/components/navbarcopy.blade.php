<div>
    {{-- {{ $perfil }} --}}
    @php
        $siniestroService = new App\Services\SiniestroService();
        $rr = $siniestroService->acceso_perfiles();
    @endphp
    @php $authUser = Auth::user(); @endphp

    <nav class="bg-gray-200 border-gray-200 dark:bg-gray-900 max-w-full">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            <a class="flex items-center space-x-3 rtl:space-x-reverse">
                <object data="{{ asset('assets/img/grupodc.svg') }}" type="image/svg+xml"
                    class="h-8 text-gray-800 dark:text-green">
                </object>
                </svg>
                <span
                    class="self-center text-2xl font-semibold whitespace-nowrap text-gray-800 dark:text-white">Siniestros</span>
            </a>
            <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
                <button type="button"
                    class="flex text-sm bg-gray-800 rounded-full md:me-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                    id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown"
                    data-dropdown-placement="bottom">
                    <span class="sr-only">Abrir menú de usuario</span>
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-semibold text-gray-700">{{ session('user_initials') }}</span>
                    </div>
                </button>

                <!-- Dropdown menu -->
                <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm dark:bg-gray-700 dark:divide-gray-600"
                    id="user-dropdown">
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between w-full">
                            <span class="block text-sm text-gray-900 dark:text-white">{{ $authUser->username }}</span>
                            <span id="departamentoSpan"
                                class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300 ml-auto">{{ $authUser->departamento->nombre }}</span>
                            <span id="permisosSpan"
                                class="hidden bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300 ml-auto">{{ $authUser->accionespermitidas() }}</span>
                        </div>

                        <span
                            class="block text-sm  text-gray-500 truncate dark:text-gray-400">{{ $authUser->email }}</span>
                    </div>
                    <ul class="py-2" aria-labelledby="user-menu-button">
                        @if ($rr->success && $rr->perfiles->count() > 1)
                            @foreach ($rr->perfiles as $perfilNav)
                                @php $nombre_url = str_replace(' ', '_', strtolower($perfilNav->nombre ?? '')); @endphp
                                <li>
                                    <a href="{{ route('siniestros.view', $nombre_url) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                        {{ $perfilNav->nombre }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                        <li class="border-t pt-1">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                @csrf
                                <button type="submit" class="btn btn-link " style="text-decoration: none;">
                                    Cerrar sesión
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <button data-collapse-toggle="navbar-user" type="button"
                    class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                    aria-controls="navbar-user" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 17 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 1h15M1 7h15M1 13h15" />
                    </svg>
                </button>
            </div>

            <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-user">
                <ul
                    class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-gray-200 dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                    @if ($rr->success)
                        <li>
                            @if ($rr->success && $rr->perfiles->count() > 1)
                                <button id="dropdownSiniestrosLink" data-dropdown-toggle="dropdownSiniestros"
                                    data-dropdown-trigger="hover"
                                    class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                                    Siniestros
                                    <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 4 4 4-4" />
                                    </svg>
                                </button>
                                <div id="dropdownSiniestros"
                                    class="z-10 hidden font-normal bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-48 dark:bg-gray-700 dark:divide-gray-600">
                                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                        aria-labelledby="dropdownSiniestrosLink">
                                        @foreach ($rr->perfiles as $perfilNav)
                                            @php $nombre_url = str_replace(' ', '_', strtolower($perfilNav->nombre ?? '')); @endphp
                                            <li>
                                                <a href="{{ route('siniestros.view', $nombre_url) }}"
                                                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white transition duration-200 ease-in-out">
                                                    {{ $perfilNav->nombre }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif ($rr->success)
                                @php $nombre_url = str_replace(' ', '_', strtolower($rr->perfiles->first()->nombre ?? '')); @endphp
                                <a href="{{ route('siniestros.view', $nombre_url) }}"
                                    class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                                    Siniestros
                                </a>
                            @endif
                        </li>
                    @endcan
                    @can("readPresupuestos$nombre_perfil", $authUser)
                        <li>
                            <button id="dropdownPresupuestosLink" data-dropdown-toggle="dropdownPresupuestos"
                                class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent"
                                data-dropdown-trigger="hover">Presupuestos
                                <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg></button>
                            <!-- Dropdown menu -->
                            <div id="dropdownPresupuestos"
                                class="z-10 hidden font-normal bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600">
                                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                    aria-labelledby="dropdownLargeButton">
                                    @can('writePresupuestos', $authUser)
                                        <li>
                                            <a href="{{ route('presupuestos.create') }}"
                                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white transform hover:scale-101 transition duration-300 ease-in-out">Crear</a>
                                        </li>
                                    @endcan
                                    <li>
                                        <a href="{{ route('presupuestos.list') }}"
                                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white transform hover:scale-101 transition duration-300 ease-in-out">Ver</a>
                                    </li>
                                </ul>

                            </div>
                        </li>
                    @endcan
                    @can("readVales$nombre_perfil", $authUser)
                        <li>
                            <a href="{{ route('vales.view') }}"
                                class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                                Vales
                            </a>
                        </li>
                    @endcan
                    @can("readEntradas$nombre_perfil", $authUser)
                        <li>
                            <a href="{{ route('entradas.view') }}"
                                class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                                Entradas
                            </a>
                        </li>
                    @endcan
                    @can("readAlbaranes$nombre_perfil", $authUser)
                        <li>
                            <a href="{{ route('albaranes.view') }}"
                                class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                                Albaranes
                            </a>
                        </li>
                    @endcan
                    @can('readFacturas', $authUser)
                        <li>
                            <a href="{{ route('facturas.view') }}"
                                class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                                Facturas
                            </a>
                        </li>
                    @endcan
                    @can('readReportes', $authUser)
                        <li>
                            <a href="{{ route('reportes.view') }}"
                                class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                                Reportes
                            </a>
                        </li>
                    @endcan
                    @can('viewProcesoVehiculos', $authUser)
                        <li>
                            <a href="{{ route('procesosVehiculos.view') }}"
                                class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                                Proceso de vehículos
                            </a>
                        </li>
                    @endcan
                    @can('viewSeguimientoTrabajos', $authUser)
                        <li>
                            <a href="{{ route('seguimientoTrabajos.view') }}"
                                class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-blue-500 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                                Seguimiento de trabajos
                            </a>
                        </li>
                    @endcan
                    <li>
                        <button id="themeToggleButton"
                            class="h-12 w-12 rounded-full p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="fill-violet-700 block dark:hidden" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <svg class="fill-yellow-500 hidden dark:block" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                    fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </li>
            </ul>
        </div>
    </div>
</nav>
