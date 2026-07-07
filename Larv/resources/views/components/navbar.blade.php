<div>
    {{-- {{ $perfilActivo }} --}}
    <nav
        class="bg-gray-200/60 dark:bg-gray-900/60 backdrop-blur-xl border-b border-gray-300/30 dark:border-gray-700/40 shadow-sm max-w-full sticky top-0 z-50 transition-all duration-300 mb-5">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            <input type="hidden" name="perfil_id" id="perfil_id" value="{{ $perfil_id }}">
            <input type="hidden" name="perfil_activo" value="{{ $perfilActivo }}">

            {{-- Logo + Título + Perfil activo --}}
            <a class="flex items-center space-x-3 rtl:space-x-reverse" href="{{ route('home') }}">
                <img src="{{ asset('assets/img/grupodc.svg') }}" type="image/svg+xml"
                    class="h-8 text-gray-800 dark:text-green">
                <div class="flex flex-col leading-tight font-['Outfit']">
                    <span class="text-2xl font-bold tracking-tight text-gray-800 dark:text-white">Gestion
                        Siniestros</span>
                    @if ($perfilLabel)
                        <span class="text-gray-500 dark:text-gray-400 font-medium tracking-wider text-sm">
                            {{ $perfilLabel }}
                        </span>
                    @endif
                </div>
            </a>

            {{-- Menú derecho: avatar + toggle mobile --}}
            <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">

                {{-- Toggle tema --}}
                <button id="themeToggleButton"
                    class="h-10 w-10 rounded-full p-2 hover:bg-gray-100 dark:hover:bg-gray-700 mr-2">
                    <svg class="fill-violet-700 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <svg class="fill-yellow-500 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z">
                        </path>
                    </svg>
                </button>

                {{-- Botón avatar --}}
                <button type="button"
                    class="flex text-sm bg-gray-800 rounded-full md:me-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                    id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown"
                    data-dropdown-placement="bottom">
                    <span class="sr-only">Abrir menú de usuario</span>
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-semibold text-gray-700">{{ session('user_initials') }}</span>
                    </div>
                </button>

                {{-- Dropdown usuario --}}
                <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm dark:bg-gray-700 dark:divide-gray-600"
                    id="user-dropdown">
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between w-full gap-2">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                {{ $authUser?->username ?? 'Invitado' }}
                            </span>
                            <span id="departamentoSpan"
                                class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">
                                {{ $authUser?->departamento?->nombre ?? 'N/A' }}
                            </span>
                            <span id="permisosSpan"
                                class="hidden bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">
                                {{ $authUser ? $authUser->accionespermitidas() : '' }}
                            </span>
                        </div>
                        <span class="block text-sm text-gray-500 truncate dark:text-gray-400 mt-1">
                            {{ $authUser?->email ?? '' }}
                        </span>
                    </div>

                    <ul class="py-2" aria-labelledby="user-menu-button">
                        {{-- Cambiar perfil (solo si hay más de uno) --}}
                        @if ($perfiles->count() > 1)
                            <li class="px-4 py-1">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cambiar
                                    perfil</span>
                            </li>
                            @foreach ($perfiles as $perfilNav)
                                @php $nombre_url = str_replace(' ', '_', strtolower($perfilNav->nombre ?? '')); @endphp
                                <li>
                                    <a href="{{ route(request()->route()->getName(), array_merge(request()->route()->parameters(), ['perfil' => $nombre_url])) }}"
                                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white
                                            {{ $perfilActivo === $nombre_url ? 'font-semibold text-blue-600 dark:text-blue-400' : '' }}">
                                        @if ($perfilActivo === $nombre_url)
                                            <svg class="w-3 h-3 fill-current text-blue-600 dark:text-blue-400 shrink-0"
                                                viewBox="0 0 20 20">
                                                <circle cx="10" cy="10" r="6" />
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 fill-current text-gray-300 dark:text-gray-500 shrink-0"
                                                viewBox="0 0 20 20">
                                                <circle cx="10" cy="10" r="6" />
                                            </svg>
                                        @endif
                                        {{ $perfilNav->nombre }}
                                    </a>
                                </li>
                            @endforeach
                            <li class="border-t mt-1"></li>
                        @endif

                        {{-- Cerrar sesión --}}
                        <li>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 w-full">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    Cerrar sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

                {{-- Toggle mobile --}}
                <button data-collapse-toggle="navbar-user" type="button"
                    class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                    aria-controls="navbar-user" aria-expanded="false">
                    <span class="sr-only">Abrir menú</span>
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 17 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 1h15M1 7h15M1 13h15" />
                    </svg>
                </button>
            </div>

            {{-- Menú principal --}}
            <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-user">
                <ul
                    class="flex flex-col font-medium font-['Outfit'] tracking-wide p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50
                    md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-transparent
                    dark:bg-gray-800 md:dark:bg-transparent dark:border-gray-700">

                    @accesoSiniestro
                        <li>
                            <a href="{{ route('siniestros.view', $perfilActivo) }}"
                                class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                Siniestros
                            </a>
                        </li>
                    @endaccesoSiniestro



                    @accesoPresupuesto
                        @can("writePresupuestos$perfilPermiso", \App\Models\Presupuesto::class)
                            <li>
                                <button id="dropdownPresupuestosLink" data-dropdown-toggle="dropdownPresupuestos"
                                    data-dropdown-trigger="hover"
                                    class="flex items-center gap-1 py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                    Presupuestos
                                    <svg class="w-2.5 h-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 4 4 4-4" />
                                    </svg>
                                </button>
                                <div id="dropdownPresupuestos"
                                    class="z-10 hidden font-normal bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600">
                                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                                        <li>
                                            <a href="{{ route('presupuestos.create', $perfilActivo) }}"
                                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white transition duration-200">
                                                Crear
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('presupuestos.list', $perfilActivo) }}"
                                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white transition duration-200">
                                                Ver
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('presupuestos.list', $perfilActivo) }}"
                                    class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                    Presupuestos
                                </a>
                            </li>
                        @endcan
                    @endaccesoPresupuesto

                    @accesoVales
                        <li>
                            <a href="{{ route('vales.view', $perfilActivo) }}"
                                class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                Vales
                            </a>
                        </li>
                    @endaccesoVales

                    @accesoEntradas
                        <li>
                            <a href="{{ route('entradas.view', $perfilActivo) }}"
                                class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                Entradas
                            </a>
                        </li>
                    @endaccesoEntradas

                    @accesoAlbaranes
                        <li>
                            <a href="{{ route('albaranes.view', $perfilActivo) }}"
                                class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                Albaranes
                            </a>
                        </li>
                    @endaccesoAlbaranes

                    @accesoFacturas
                        <li>
                            <a href="{{ route('facturas.view', $perfilActivo) }}"
                                class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                Facturas
                            </a>
                        </li>
                    @endaccesoFacturas
                    @accesoReportes
                        @if($authUser && $authUser->tienePermiso('reporteRefacciones'))
                            <li>
                                <button id="dropdownReportesLink" data-dropdown-toggle="dropdownReportes"
                                    data-dropdown-trigger="hover"
                                    class="flex items-center gap-1 py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                    Reportes
                                    <svg class="w-2.5 h-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 4 4 4-4" />
                                    </svg>
                                </button>
                                <div id="dropdownReportes"
                                    class="z-10 hidden font-normal bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600">
                                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                                        <li>
                                            <a href="{{ route('reportes.view', $perfilActivo) }}"
                                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white transition duration-200">
                                                Reportes Actuales
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('reportes.refaccionesView', $perfilActivo) }}"
                                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white transition duration-200">
                                                Reporte Refacciones
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('reportes.view', $perfilActivo) }}"
                                    class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                    Reportes
                                </a>
                            </li>
                        @endif
                    @endaccesoReportes

                    @accesoProcesosVehiculo
                        <li>
                            <a href="{{ route('procesosVehiculos.view', $perfilActivo) }}"
                                class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                Proceso de vehículos
                            </a>
                        </li>
                    @endaccesoProcesosVehiculo

                    @accesoSeguimientoTrabajo
                        <li>
                            <a href="{{ route('seguimientoTrabajos.view', $perfilActivo) }}"
                                class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                Seguimiento de trabajos
                            </a>
                        </li>
                    @endaccesoSeguimientoTrabajo
                    @accesoAdminSiniestros
                        <li>
                            <a href="{{ route('administracion.permisos.view', $perfilActivo) }}"
                                class="flex items-center py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500">
                                Administracion
                            </a>
                        </li>
                    @endaccesoAdminSiniestros
                </ul>
            </div>
        </div>
    </nav>
</div>
