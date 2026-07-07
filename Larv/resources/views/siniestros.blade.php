@extends('layouts.app')

@section('content')
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <link href="{{ asset('assets/css/dataTables.tailwindcss.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/dataTables.tailwindcss.js') }}"></script>
    <!-- Safelist para Tailwind CSS: Asegura que las clases inyectadas dinámicamente en JS estén compiladas -->
    <div
        class="hidden !text-[9px] !text-[10px] !px-1 !py-1 !leading-none !tracking-tighter uppercase !leading-tight tracking-tight text-[10px] sm:text-[11px] text-[11px]">
    </div>

    <!-- Start block -->
    <div class="mx-auto w-full px-4 lg:px-12 mt-5">
        <div
            class="bg-white/50 dark:bg-gray-800/40 backdrop-blur-lg border border-gray-200/50 dark:border-gray-600/50 relative shadow-2xl sm:rounded-2xl overflow-hidden ">
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                <div class="flex-1 flex items-center space-x-2">
                    <h1>
                        <span class="text-gray-800 dark:text-white siniestrosSpan">Siniestros de
                            abiertos</span>
                    </h1>
                </div>
            </div>
            <div
                class="flex flex-col md:flex-row items-stretch md:items-center md:space-x-3 space-y-3 md:space-y-0 justify-between mx-4 py-4 border-t dark:border-gray-700">
                <div class="w-full md:w-1/2 flex flex-row gap-5">

                    <form class="flex items-center gap-5">
                        {{-- <label for="simple-search" class="sr-only">Buscar</label>
                        <div class="relative flex w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-200" fill="currentColor"
                                    viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" />
                                </svg>
                            </div>
                            <input type="text" id="simple-search" placeholder="Buscar" required=""
                                class="bg-transparent border border-gray-300 dark:border-gray-100 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-transparent dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        </div> --}}
                    </form>
                    {{-- <div class="flex w-full">
                        @if ($perfiles->count() > 1)
                            <select id="talleres" name="talleres"
                                class="bg-transparent border border-gray-300 dark:border-gray-100 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-transparent dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                @foreach ($perfiles as $perfil)
                                    <option class="bg-white dark:bg-gray-700 text-black dark:text-white"
                                        value="{{ $perfil->id }}"><a href="#">{{ $perfil->nombre }}</a></option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" value="{{ $perfiles[0]->id }}" id="id_perfil">
                            <input type="text" readonly value="{{ $perfiles[0]->nombre }}"
                                data-id="{{ $perfiles[0]->id }}"
                                class="bg-transparent border border-gray-300 dark:border-gray-100 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-transparent dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        @endif
                    </div> --}}
                    <div class="flex ">

                        <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown"
                            class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 bg-white rounded-lg border border-primary-600 hover:bg-gray-100 hover:text-primary-700 dark:bg-gray-800 dark:text-white dark:border-primary-600 dark:hover:text-white dark:hover:bg-gray-700"
                            type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                class="h-4 w-4 mr-1.5 -ml-1 text-gray-400" viewbox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                    clip-rule="evenodd" />
                            </svg>
                            Filtros
                            <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </button>
                        <div id="filterDropdown"
                            class="z-10 hidden px-3 pt-1 bg-white rounded-lg shadow w-80 dark:bg-gray-700 right-0">
                            <div class="flex items-center justify-between pt-2">
                                <h6 class="text-sm font-medium text-black dark:text-white">Filtros</h6>
                                <div class="flex items-center space-x-3">
                                    <a href="#" id="limpiarEstado"
                                        class="flex items-center text-sm font-medium text-primary-600 dark:text-primary-500 hover:underline">Limpiar</a>
                                </div>
                            </div>

                            <div id="accordion-flush" data-accordion="collapse"
                                data-active-classes="text-black dark:text-white"
                                data-inactive-classes="text-gray-500 dark:text-gray-400">
                                <!-- Estado -->
                                <h2 id="category-heading">
                                    <button type="button"
                                        class="flex items-center justify-between w-full py-2 px-1.5 text-sm font-medium text-left text-gray-500 border-b border-gray-200 dark:border-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                                        data-accordion-target="#category-body" aria-expanded="true"
                                        aria-controls="category-body">
                                        <span>Estado</span>
                                        <svg aria-hidden="true" data-accordion-icon="" class="w-5 h-5 rotate-180 shrink-0"
                                            fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                        </svg>
                                    </button>
                                </h2>
                                <div id="category-body" class="hidden" aria-labelledby="category-heading">
                                    <div class="py-2 font-light border-b border-gray-200 dark:border-gray-600">
                                        <ul class="space-y-2">
                                            <li class="flex items-center">
                                                <input id="abiertos" type="radio" name="filtroEstado" value="Abierto"
                                                    class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500 "
                                                    checked>
                                                <label for="abiertos"
                                                    class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100 ">Abiertos</label>
                                            </li>
                                            <li class="flex items-center">
                                                <input id="completados" type="radio" name="filtroEstado"
                                                    value="Completado"
                                                    class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                                <label for="completados"
                                                    class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Completados</label>
                                            </li>
                                            <li class="flex items-center">
                                                <input id="cerrados" type="radio" name="filtroEstado" value="Cerrado"
                                                    class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                                <label for="cerrados"
                                                    class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Cerrados</label>
                                            </li>
                                            <li class="flex items-center">
                                                <input id="cancelados" type="radio" name="filtroEstado" value="Cancelado"
                                                    class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                                <label for="cancelados"
                                                    class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Cancelados</label>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Switch estilo Apple -->
                    {{-- <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" id="switchVerTodos">
                        <div
                            class="border border-red-500 relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                        </div>
                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300 whitespace-nowrap">Ver
                            todos</span>
                    </label> --}}

                </div>
                <div
                    class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                    @can("writeSiniestros$nombre_perfil", $siniestro)
                        <button type="button" id="createProductButton" data-modal-target="createSiniestroModal"
                            data-modal-toggle="createSiniestroModal"
                            class="inline-flex items-center px-5 py-2.5 text-sm font-bold text-center text-white bg-blue-700 rounded-xl hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-blue-500/20">
                            <i class="fa-solid fa-plus mr-2 text-xs"></i>
                            Crear siniestros
                        </button>
                        <button type="button" data-modal-target="createMarcaModal" data-modal-toggle="createMarcaModal"
                            class="inline-flex items-center px-5 py-2.5 text-sm font-bold text-center text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800 transition-all duration-300 transform hover:scale-105 shadow-lg shadow-indigo-500/20">
                            <i class="fa-solid fa-shapes mr-2 text-xs"></i>
                            Configuración
                        </button>
                        <!-- Modal toggle -->
                    @endcan


                </div>
            </div>
            <div class="relative shadow-md p-2 ">
                <div class="flex items-center justify-end space-x-2 pb-3">

                    <span
                        class="dark:bg-success-soft bg-green-500 dark:text-white text-xs font-medium px-1.5 py-0.5 rounded-full">1
                        a 3 dias</span>
                    <span
                        class="dark:bg-warning-soft bg-yellow-500 dark:text-white text-xs font-medium px-1.5 py-0.5 rounded-full">4
                        a 10 dias</span>
                    <span
                        class="dark:bg-danger-soft bg-red-500 dark:text-white text-xs font-medium px-1.5 py-0.5 rounded-full">Back
                        Order</span>
                </div>
                <table id="siniestrosTable">
                </table>

            </div>
        </div>
    </div>

    <!-- End block -->

    <button class="hidden" id="openModalDetallePartes"></button>

    @can("writeSiniestros$nombre_perfil", $siniestro)
        <!-- Modal: Configuración (Agregar Datos) -->
        <div id="createMarcaModal" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-full max-h-full"
            style="background: none !important;">
            <div class="relative p-4 w-full max-w-2xl max-h-full">
                <div
                    class="relative bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                    <div
                        class="modal-header flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                        <h3 class="modal-title text-lg font-semibold text-gray-900 dark:text-white">
                            Agregar Datos de Configuración
                        </h3>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="createMarcaModal">
                            <i class="fa-solid fa-xmark"></i>
                            <span class="sr-only">Cerrar</span>
                        </button>
                    </div>
                    <div class="p-4 md:p-6 space-y-8">
                        <!-- Cliente/Aseguradora -->
                        @if ($nombre_perfil == 'Refacciones')
                            <form id="formCliente" class="space-y-2">
                                @csrf
                                <label class="block text-xs font-bold uppercase tracking-widest text-gray-500">Cliente</label>
                                <div class="flex gap-3">
                                    <div class="relative flex-1">
                                        <div
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                            <i class="fa-solid fa-building text-xs"></i>
                                        </div>
                                        <input type="text" name="nombreCliente" id="nombreCliente"
                                            class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all"
                                            placeholder="Nombre del cliente" required>
                                    </div>
                                    <div class="relative w-1/3">
                                        <div
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                            <i class="fa-solid fa-barcode text-xs"></i>
                                        </div>
                                        <input type="text" name="codigoCliente" id="codigoCliente"
                                            class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all"
                                            placeholder="Código" required>
                                    </div>
                                    <button type="submit"
                                        class="px-6 py-2.5 bg-blue-700 text-white font-bold text-xs rounded-xl hover:bg-blue-800 transition-all transform hover:scale-105 shadow-lg shadow-blue-500/20">
                                        Agregar
                                    </button>
                                </div>
                            </form>
                        @else
                            <form id="formAseguradora" class="space-y-2">
                                @csrf
                                <label
                                    class="block text-xs font-bold uppercase tracking-widest text-gray-500">Aseguradora</label>
                                <div class="flex gap-3">
                                    <div class="relative flex-1">
                                        <div
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                            <i class="fa-solid fa-建 text-xs"></i>
                                        </div>
                                        <input type="text" name="nombreAseguradora" id="nombreAseguradora"
                                            class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all"
                                            placeholder="Nombre de la aseguradora">
                                    </div>
                                    <button type="submit"
                                        class="px-6 py-2.5 bg-blue-700 text-white font-bold text-xs rounded-xl hover:bg-blue-800 transition-all transform hover:scale-105 shadow-lg shadow-blue-500/20">
                                        Agregar
                                    </button>
                                </div>
                            </form>
                        @endif

                        <!-- Vehículo -->
                        <form id="formVehiculo" class="space-y-2">
                            @csrf
                            <label class="block text-xs font-bold uppercase tracking-widest text-gray-500">Vehículo</label>
                            <div class="flex gap-3">
                                <div class="relative flex-1">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-car text-xs"></i>
                                    </div>
                                    <input type="text" name="nombreVehiculo" id="nombreVehiculo"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all"
                                        placeholder="Nombre del vehículo">
                                </div>
                                <button type="submit"
                                    class="px-6 py-2.5 bg-blue-700 text-white font-bold text-xs rounded-xl hover:bg-blue-800 transition-all transform hover:scale-105 shadow-lg shadow-blue-500/20">
                                    Agregar
                                </button>
                            </div>
                        </form>

                        <!-- Marca -->
                        <form id="formMarca" class="space-y-2">
                            @csrf
                            <label class="block text-xs font-bold uppercase tracking-widest text-gray-500">Marca</label>
                            <div class="flex gap-3">
                                <div class="relative flex-1">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-tag text-xs"></i>
                                    </div>
                                    <input type="text" name="nombreMarca" id="nombreMarca"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all"
                                        placeholder="Nombre de la marca">
                                </div>
                                <button type="submit"
                                    class="px-6 py-2.5 bg-blue-700 text-white font-bold text-xs rounded-xl hover:bg-blue-800 transition-all transform hover:scale-105 shadow-lg shadow-blue-500/20">
                                    Agregar
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="flex justify-end p-4 md:p-6 border-t border-gray-200 dark:border-gray-700">
                        <button data-modal-hide="createMarcaModal" type="button"
                            class="px-6 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Añadir Siniestro -->
        <div id="createSiniestroModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-full max-h-full"
            style="background: none !important;">
            <div class="relative p-4 w-full max-w-2xl max-h-full">
                <div
                    class="relative bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                    <div
                        class="modal-header flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                        <h3 class="modal-title text-lg font-semibold text-gray-900 dark:text-white">
                            Añadir Nuevo Siniestro
                        </h3>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="createSiniestroModal">
                            <i class="fa-solid fa-xmark"></i>
                            <span class="sr-only">Cerrar</span>
                        </button>
                    </div>
                    <form action="#" id="agregarSiniestroForm">
                        @csrf
                        <input type="hidden" value="{{ $id_perfil }}" name="perfil_id" id="idPerfil">

                        <div class="p-4 md:p-6 grid gap-5 grid-cols-1 md:grid-cols-2">
                            <!-- N° Orden -->
                            <div class="space-y-2">
                                <label for="numeroOrden"
                                    class="block text-xs font-bold uppercase tracking-widest text-gray-500">N° Orden</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-hashtag text-xs"></i>
                                    </div>
                                    <input type="number" name="numero_orden" id="numeroOrden"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all"
                                        placeholder="Ej: 12345" required>
                                </div>
                            </div>
                            <!-- N° Siniestro -->
                            <div class="space-y-2">
                                <label for="numeroSiniestro"
                                    class="block text-xs font-bold uppercase tracking-widest text-gray-500">N°
                                    Siniestro</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-shield-halved text-xs"></i>
                                    </div>
                                    <input type="text" name="numero_siniestro" id="numeroSiniestro"
                                        class="bg-gray-50/50 uppercase dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all"
                                        placeholder="Ej: N98765" required>
                                </div>
                            </div>
                            <!-- Aseguradora -->
                            <div class="space-y-2">
                                @if ($nombre_perfil == 'Refacciones')
                                    <label for="id_cliente"
                                        class="block text-xs font-bold uppercase tracking-widest text-gray-500">Cliente</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                            <i class="fa-solid fa-building text-xs"></i>
                                        </div>

                                        <select id="id_cliente" name="id_cliente"
                                            class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all appearance-none cursor-pointer">
                                            @foreach ($clientes as $cliente)
                                                <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <label for="id_aseguradora"
                                            class="block text-xs font-bold uppercase tracking-widest text-gray-500">Aseguradora</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                                <i class="fa-solid fa-building text-xs"></i>
                                            </div>

                                            <select id="id_aseguradora" name="aseguradora"
                                                class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all appearance-none cursor-pointer">
                                                @foreach ($aseguradoras as $aseguradora)
                                                    <option value="{{ $aseguradora->nombre }}">{{ $aseguradora->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                @endif


                            </div>
                        </div>
                        <!-- VIN -->
                        <div class="space-y-2">
                            <label for="vin"
                                class="block text-xs font-bold uppercase tracking-widest text-gray-500">VIN</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-fingerprint text-xs"></i>
                                </div>
                                <input type="text" name="vin" id="vin"
                                    class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all"
                                    placeholder="Número de Serie (17 caracteres)" required minlength="17" maxlength="17">
                                <p class="mt-1 text-xs text-gray-400">Debe tener exactamente 17 caracteres.</p>
                            </div>
                        </div>
                        <!-- Marca -->
                        <div class="space-y-2">
                            <label for="marca"
                                class="block text-xs font-bold uppercase tracking-widest text-gray-500">Marca</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-tag text-xs"></i>
                                </div>
                                <select id="marca" name="marca"
                                    class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all appearance-none cursor-pointer">
                                    @foreach ($marcas as $marca)
                                        <option value="{{ $marca->nombre }}">{{ $marca->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Vehículo -->
                        <div class="space-y-2">
                            <label for="vehiculo"
                                class="block text-xs font-bold uppercase tracking-widest text-gray-500">Vehículo</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-car text-xs"></i>
                                </div>
                                <select id="vehiculo" name="vehiculo"
                                    class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all appearance-none cursor-pointer">
                                    @foreach ($vehiculos as $vehiculo)
                                        <option value="{{ $vehiculo->nombre }}">{{ $vehiculo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Modelo -->
                        <div class="space-y-2">
                            <label for="modelo"
                                class="block text-xs font-bold uppercase tracking-widest text-gray-500">Modelo
                                (Año)</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-calendar text-xs"></i>
                                </div>
                                <input type="text" name="modelo" id="modelo"
                                    class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all"
                                    placeholder="Ej: 2024" required>
                            </div>
                        </div>
                        <!-- Taller -->
                        <div class="space-y-2">
                            <label for="taller"
                                class="block text-xs font-bold uppercase tracking-widest text-gray-500">Taller</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-wrench text-xs"></i>
                                </div>
                                @if (count($talleres) == 1)
                                    <input type="text" id="taller" name="taller"
                                        value="{{ $talleres->first()->nombre }}" readonly
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all cursor-not-allowed">
                                @else
                                    <select id="taller" name="taller"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 dark:text-white dark:placeholder-gray-400 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 outline-none transition-all appearance-none cursor-pointer">
                                        @foreach ($talleres as $taller)
                                            <option value="{{ $taller->nombre }}">{{ $taller->nombre }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                </div>
                <div class="p-4 md:p-6 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
                    <button data-modal-toggle="createSiniestroModal" type="button"
                        class="px-6 py-2.5 text-sm font-medium text-gray-500 hover:text-red-600 transition-colors">
                        Descartar
                    </button>
                    <button type="submit" id="anadirSiniestroButton"
                        class="inline-flex items-center px-10 py-3 text-sm font-bold text-center text-white bg-blue-700 rounded-xl hover:bg-blue-800 transition-all transform hover:scale-105 shadow-xl shadow-blue-500/20">
                        <i class="fa-solid fa-plus mr-2"></i> Añadir siniestro
                    </button>
                </div>
                </form>
            </div>
        </div>
        </div>

        <!-- Modal: Detalle de Partes -->
        <div id="modalDetallePartes" tabindex="-1" aria-hidden="true"
            class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full bg-black/20 backdrop-blur-sm"
            style="background: none !important;">
            <div class="relative p-4 w-full max-w-4xl max-h-full">
                <div
                    class="relative bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                    <!-- Modal header -->
                    <div
                        class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200 bg-gray-50/50 dark:bg-gray-700/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight">
                            Piezas autorizadas de la orden
                        </h3>
                        <button type="button"
                            class="close-modal text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                            <i class="fa-solid fa-xmark"></i>
                            <span class="sr-only">Cerrar</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-4 md:p-6 overflow-x-auto max-h-[70vh] overflow-y-auto">
                        <table id="tableDetalleModalPartes" class="w-full text-sm text-left">
                            <thead
                                class="text-[10px] font-bold uppercase tracking-widest text-white bg-gray-800 dark:bg-gray-700">
                                <tr id="detalleModalTableHead">
                                    <!-- Dinámico JS -->
                                </tr>
                            </thead>
                            <tbody id="detalleModalTableBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Dinámico JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
@endsection
