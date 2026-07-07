@extends('layouts.app')

@section('content')
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <link href="{{ asset('assets/css/dataTables.tailwindcss.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/dataTables.tailwindcss.js') }}"></script>
    <!-- Start block -->
    <div class="mx-auto w-full px-4 lg:px-12 mt-5">
        <div
            class="bg-white/50 dark:bg-gray-800/40 backdrop-blur-lg border border-gray-200/50 dark:border-gray-600/50 relative shadow-2xl sm:rounded-2xl overflow-hidden ">
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                <div class="flex-1 flex items-center space-x-2">
                    <h1>
                        <span class="text-gray-800 dark:text-white seguimientoTrabajosSpan">Seguimiento de trabajos</span>
                    </h1>
                </div>
            </div>
            <div
                class="flex flex-col md:flex-row items-stretch md:items-center md:space-x-3 space-y-3 md:space-y-0 justify-between mx-4 py-4 border-t dark:border-gray-700">
                <div class="w-full md:w-1/2">
                    <form class="flex items-center">
                        <label for="simple-search" class="sr-only">Buscar</label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                    viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" />
                                </svg>
                            </div>
                            <input type="text" id="simple-search" placeholder="Buscar" required=""
                                class="bg-transparent border border-gray-300 dark:border-gray-100 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-transparent dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        </div>
                    </form>
                </div>
                <div
                    class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
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

                        <div id="accordion-flush" data-accordion="collapse" data-active-classes="text-black dark:text-white"
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
                                            <input id="pendientes" type="radio" name="filtroEstado" value="Pendiente"
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="pendientes"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Pendiente</label>
                                        </li>
                                        <li class="flex items-center">
                                            <input id="enProceso" type="radio" name="filtroEstado" value="EnProceso"
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="enProceso"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">En
                                                Proceso</label>
                                        </li>
                                        <li class="flex items-center">
                                            <input id="pausados" type="radio" name="filtroEstado" value="Pausado"
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="pausados"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Pausado</label>
                                        </li>
                                        <li class="flex items-center">
                                            <input id="finalizados" type="radio" name="filtroEstado"
                                                value="Finalizado"
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="finalizados"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Finalizado</label>
                                        </li>
                                        <li class="flex items-center">
                                            <input id="especiales" type="radio" name="filtroEstado"
                                                value="CasoEspecial"
                                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="especiales"
                                                class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Casos
                                                Especiales</label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative shadow-md p-2 ">

                <table id="seguimientoTrabajosTable">
                </table>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
@endsection
