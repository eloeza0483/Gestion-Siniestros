@extends('layouts.app')
@push('scripts')
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/jquery-ui.min.js') }}"></script>
    {{-- <script type="module" src="{{asset('assets/js/vales/verVale.js')}}"></script> --}}
@endpush

@section('content')
    <!-- Start block -->
    <section class="p-3 sm:p-5 antialiased">
        <div class="mx-auto w-full px-4 lg:px-12">
            <div
                class="bg-white/50 dark:bg-gray-800/40 backdrop-blur-lg border border-gray-200/50 dark:border-gray-600/50 relative shadow-md sm:rounded-lg overflow-hidden p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                    <div class="flex-1 flex items-center space-x-2">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                            <span class="texto-vale">
                                <!-- Dinámico -->
                            </span>
                        </h2>
                    </div>
                    <div class="flex items-center space-x-2">
                        @can("pedirModificacion$perfil", [Auth::user()])
                            <button type="button" id="pedirModificacionButton" data-tooltip-target="tooltip-pedir-modificacion"
                                data-tooltip-placement="top"
                                class="pedirModificacionButton flex items-center text-white bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-3 py-2 text-center transition-all hover:scale-105">
                                <i class="fa-solid fa-bell"></i>
                            </button>
                            <div id="tooltip-pedir-modificacion" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700 border border-gray-900">
                                Pedir modificación
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        @endcan
                        @can("writeAlbaranes$perfil", \App\Models\Albaran::class)
                            <button type="button" id="asignarAlbaranButton" data-modal-target="asignarAlbaran-modal"
                                data-modal-toggle="asignarAlbaran-modal" data-tooltip-target="tooltip-asignar-albaran"
                                data-tooltip-placement="top"
                                class="flex items-center text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:ring-yellow-200 font-medium rounded-lg text-sm px-3 py-2 text-center transition-all hover:scale-105">
                                <i class="fa-solid fa-file-invoice"></i><i class="fa-solid fa-plus fa-2xs pl-px"></i>
                            </button>
                            <div id="tooltip-asignar-albaran" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700 border border-gray-900">
                                Asignar albarán
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        @endcan

                        @can("writeEntradas$perfil", \App\Models\Entrada::class)
                            <button type="button" id="asignarEntradaButton" data-modal-target="asignarEntrada-modal"
                                data-modal-toggle="asignarEntrada-modal" data-tooltip-target="tooltip-asignar-entrada"
                                data-tooltip-placement="top"
                                class="flex items-center text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center transition-all hover:scale-105">
                                <i class="fa-solid fa-file-invoice"></i><i class="fa-solid fa-plus fa-2xs pl-px"></i>
                            </button>
                            <div id="tooltip-asignar-entrada" role="tooltip"
                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700 border border-gray-900">
                                Asignar entrada
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        @endcan
                        {{-- @can("viewEntradas$perfil", \App\Models\Entrada::class)
                                <button type="button" id="verEntrada" data-modal-target="modalVales-ver-entradas"
                                    data-modal-toggle="modalVales-ver-entradas" data-tooltip-target="tooltip-ver-entrada"
                                    data-tooltip-placement="top"
                                    class="verEntrada flex items-center text-white bg-amber-500 hover:bg-amber-600 focus:ring-4 focus:ring-amber-300 font-medium rounded-lg text-sm px-3 py-2 text-center transition-all hover:scale-105">
                                    <i class="fa-solid fa-truck-arrow-right"></i>
                                </button>
                                <div id="tooltip-ver-entrada" role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700 border border-gray-900">
                                    Ver entradas
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            @endcan --}}
                    </div>
                </div>
                <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700 mb-6">

                <div class="relative">
                    <form id="verValeForm" class="verValeForm space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Num Orden
                                </span>
                                <input type="text" id="numero_orden" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Num Siniestro
                                </span>
                                <input type="text" id="numero_siniestro" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Num Presupuesto
                                </span>
                                <input type="text" id="numero_presupuesto" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span id="label_cliente_aseguradora"
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Aseguradora
                                </span>
                                <input type="text" id="aseguradora" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <input id="id_vale" name="id_vale" type="hidden">
                            <input id="codigo" name="codigo" type="hidden">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    VIN
                                </span>
                                <input type="text" id="vin" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Vehiculo
                                </span>
                                <input type="text" id="vehiculo" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Marca
                                </span>
                                <input type="text" id="marca" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Modelo
                                </span>
                                <input type="text" id="modelo" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Proveedor
                                </span>
                                <input type="text" id="proveedor" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Estado
                                </span>
                                <input type="text" id="estado" readonly
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Vale
                                </span>
                                <input type="date" id="fecha_vale"
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Promesa
                                </span>
                                <input type="date" id="fecha_promesa"
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                        </div>

                        <div class="overflow-x-auto shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                            <table id="verValeTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-white uppercase bg-black dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-3 py-3">N° Parte</th>
                                        <th scope="col" class="px-3 py-3">Descripción</th>
                                        <th scope="col" class="px-3 py-3">Piezas Presupuestadas</th>
                                        <th scope="col" class="px-3 py-3">Piezas Restantes</th>
                                        <th scope="col" class="px-3 py-3">Importe unitario</th>
                                        <th scope="col" class="px-3 py-3">Importe total</th>
                                        @can("updatePartes$perfil", [Auth::user(), $perfil])
                                            <th scope="col" class="px-3 py-3 text-center">Editar</th>
                                        @else
                                            <th scope="col" class="px-3 py-3 text-center"></th>
                                        @endcan
                                        @can("deletePartes$perfil", [Auth::user(), $perfil])
                                            <th scope="col" class="px-3 py-3 text-center">Eliminar</th>
                                        @else
                                            <th scope="col" class="px-3 py-3 text-center"></th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <!--  -->
                                </tbody>
                            </table>
                        </div>
                        @can("updatePartes$perfil", [Auth::user(), $perfil])
                            <div class="flex justify-end space-x-2 mt-4">
                                <button id="cancelarModificacionPartesButton" type="button"
                                    class="hidden bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                                    Cancelar
                                </button>
                                <button id="modificarPartesButton" type="submit"
                                    class="hidden bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Enviar modificaciones
                                </button>
                            </div>
                        @endcan
                    </form>

                </div>
            </div>
    </section>
    <!-- End block -->


    <!-- Modal toggle -->
    <!-- <button data-modal-target="modalVales-ver-entradas" data-modal-toggle="modalVales-ver-entradas" class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
                                                                                                                                                                                                                                                                                                                                                                               Ver             entradas
                                                                                                                                                                                                                                                                                                                                                                                    </button> -->

    <!-- Main modal -->
    @can("readEntradas$perfil", \App\Models\Entrada::class)
        <div id="modalVales-ver-entradas" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-10 right-10 z-50 justify-center items-center">
            <div class="relative p-4 w-full max-w-2xl max-h-full ">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                    <!-- Modal header -->
                    <div
                        class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Entradas
                        </h3>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center    dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="modalVales-ver-entradas">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <div class="content-modal-entradas p-4 md:p-5 space-y-4">
                    </div>
                </div>
            </div>
        </div>
    @endcan


    <div>

    </div>



    <!-- Entrada Modal -->
    @can("writeEntradas$perfil", \App\Models\Entrada::class)
        <div id="asignarEntrada-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full"
            style="background: none !important;">
            <div class="relative p-4 w-full max-w-xl max-h-full">
                <!-- Modal content -->
                <div
                    class="relative bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                    <!-- Modal header -->
                    <div
                        class="modal-header flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                        <h3 class="modal-title text-lg font-semibold text-gray-900 dark:text-white">
                            Asignar entrada
                        </h3>
                        <button type="button" id="cerrarModalButton"
                            class="cerrarModalEntrada text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="asignarEntrada-modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Cerrar</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <form id="asignarEntradaForm" class="p-4 md:p-6 space-y-6">
                        <!-- Info Section (Read-only) -->
                        <div
                            class="grid grid-cols-3 gap-4 p-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-200/50 dark:border-gray-700/50">
                            <div class="col-span-1">
                                <label
                                    class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">N°
                                    Orden</label>
                                <input type="text" id="numero_orden_asignacion" readonly
                                    class="bg-transparent border-0 p-0 text-sm font-semibold text-gray-900 dark:text-white focus:ring-0 w-full cursor-default"
                                    value="Cargando...">
                            </div>
                            <div class="col-span-1">
                                <label
                                    class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">N°
                                    Siniestro</label>
                                <input type="text" id="numero_siniestro_asignacion" name="numero_siniestro_asignacion"
                                    readonly
                                    class="bg-transparent border-0 p-0 text-sm font-semibold text-gray-900 dark:text-white focus:ring-0 w-full cursor-default"
                                    value="Cargando...">
                            </div>
                            <div class="col-span-1">
                                <label
                                    class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">N°
                                    Presupuesto</label>
                                <input type="text" id="numero_presupuesto_asignacion" readonly
                                    class="bg-transparent border-0 p-0 text-sm font-semibold text-gray-900 dark:text-white focus:ring-0 w-full cursor-default"
                                    value="Cargando...">
                            </div>
                        </div>

                        <!-- Form Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="col-span-1">
                                <label for="entrada"
                                    class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Entrada</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-file-import text-xs"></i>
                                    </div>
                                    <input type="text" name="entrada" id="entrada"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 dark:placeholder-gray-400 dark:text-white backdrop-blur-sm transition-all outline-none"
                                        placeholder="Número de entrada" required="">
                                </div>
                            </div>
                            <div class="col-span-1">
                                <label for="importe"
                                    class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Importe</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-dollar-sign text-xs"></i>
                                    </div>
                                    <input type="text" name="importe" id="importe"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 dark:placeholder-gray-400 dark:text-white backdrop-blur-sm transition-all outline-none"
                                        placeholder="0.00" required="">
                                </div>
                            </div>
                            <div class="col-span-1">
                                <label for="num_partes"
                                    class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">N° Partes</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-cubes text-xs"></i>
                                    </div>
                                    <input type="number" name="num_partes" id="num_partes"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 dark:placeholder-gray-400 dark:text-white backdrop-blur-sm transition-all outline-none"
                                        placeholder="0" required="">
                                </div>
                            </div>
                        </div>
                        <table id="datosEntradaTable"
                            class="hidden w-full text-sm text-left text-gray-500 dark:text-gray-400 mb-4">
                            <thead
                                class="text-[10px] font-bold uppercase tracking-widest text-white bg-gray-800 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 py-3 rounded-tl-xl">N° Parte</th>
                                    <th scope="col" class="px-4 py-3">Descripción</th>
                                    <th scope="col" class="px-4 py-3 text-center">Pzas</th>
                                    <th scope="col" class="px-4 py-3 text-right rounded-tr-xl">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="spinner-entrada" class="hidden">
                                    <td colspan="4" class="py-4 text-center">
                                        <i class="fa-solid fa-spinner fa-spin fa-2xl text-blue-500"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="flex justify-end mt-4">
                            <button id="submitAsignacionButton" type="submit"
                                class="inline-flex items-center px-10 py-3 text-sm font-bold text-center text-white bg-blue-700 rounded-xl hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-all duration-300 transform hover:scale-105 shadow-xl shadow-blue-500/20">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Asignar entrada
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endcan
    <!-- Albaran Modal -->
    @can("writeAlbaranes$perfil", \App\Models\Albaran::class)
        <div id="asignarAlbaran-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
            class="hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-[calc(100%-1rem)] max-h-full"
            style="background: none !important;">
            <div class="relative p-4 w-full max-w-xl max-h-full">
                <!-- Modal content -->
                <div
                    class="relative bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                    <!-- Modal header -->
                    <div
                        class="modal-header cursor-move flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white pointer-events-none">
                            Asignar albaran
                        </h3>
                        <button type="button" id="cerrarModalAlbaran"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="asignarAlbaran-modal" id="cerrarModalButton">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Cerrar</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <form id="asignarAlbaranForm" class="p-4 md:p-6 space-y-6">
                        <!-- Info Section (Read-only) -->
                        <div
                            class="grid grid-cols-3 gap-4 p-4 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-200/50 dark:border-gray-700/50">
                            <div class="col-span-1">
                                <label
                                    class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">N°
                                    Orden</label>
                                <input type="text" id="numero_orden_asignacion" readonly
                                    class="bg-transparent border-0 p-0 text-sm font-semibold text-gray-900 dark:text-white focus:ring-0 w-full cursor-default"
                                    value="Cargando...">
                            </div>
                            <div class="col-span-1">
                                <label
                                    class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">N°
                                    Siniestro</label>
                                <input type="text" id="numero_siniestro_asignacion" name="numero_siniestro_asignacion"
                                    readonly
                                    class="bg-transparent border-0 p-0 text-sm font-semibold text-gray-900 dark:text-white focus:ring-0 w-full cursor-default"
                                    value="Cargando...">
                            </div>
                            <div class="col-span-1">
                                <label
                                    class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">N°
                                    Presupuesto</label>
                                <input type="text" id="numero_presupuesto_asignacion" readonly
                                    class="bg-transparent border-0 p-0 text-sm font-semibold text-gray-900 dark:text-white focus:ring-0 w-full cursor-default"
                                    value="Cargando...">
                            </div>
                        </div>

                        <!-- Form Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="col-span-1">
                                <label for="albaran"
                                    class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Albarán</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-file-invoice text-xs"></i>
                                    </div>
                                    <input type="text" name="albaran" id="albaran"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 dark:placeholder-gray-400 dark:text-white backdrop-blur-sm transition-all outline-none"
                                        placeholder="Número de albarán" required="">
                                </div>
                            </div>
                            <div class="col-span-1">
                                <label for="importe"
                                    class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Importe</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-dollar-sign text-xs"></i>
                                    </div>
                                    <input type="text" name="importe" id="importe"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 dark:placeholder-gray-400 dark:text-white backdrop-blur-sm transition-all outline-none"
                                        placeholder="0.00" required="">
                                </div>
                            </div>
                            <div class="col-span-1">
                                <label for="num_partes"
                                    class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">N° Partes</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-cubes text-xs"></i>
                                    </div>
                                    <input type="number" name="num_partes" id="num_partes"
                                        class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 dark:placeholder-gray-400 dark:text-white backdrop-blur-sm transition-all outline-none"
                                        placeholder="0" required="">
                                </div>
                            </div>
                        </div>
                        <!-- Tabla de selección (cuando hay varios albaranes) -->
                        <table id="selectAlbaranTable"
                            class="hidden w-full text-sm text-left text-gray-500 dark:text-gray-400 mb-6">
                            <thead
                                class="text-[10px] font-bold uppercase tracking-widest text-white bg-gray-800 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 py-3 rounded-tl-xl">N° albarán</th>
                                    <th scope="col" class="px-4 py-3">Fecha</th>
                                    <th scope="col" class="px-4 py-3 text-center rounded-tr-xl">Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <!-- Tabla de piezas del albarán -->
                        <table id="datosAlbaranTable"
                            class="hidden w-full text-sm text-left text-gray-500 dark:text-gray-400 mb-4">
                            <thead
                                class="text-[10px] font-bold uppercase tracking-widest text-white bg-gray-800 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 py-3 rounded-tl-xl">N° Parte</th>
                                    <th scope="col" class="px-4 py-3">Descripción</th>
                                    <th scope="col" class="px-4 py-3 text-center">Pzas</th>
                                    <th scope="col" class="px-4 py-3 text-right rounded-tr-xl">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="spinner-albaran" class="hidden">
                                    <td colspan="4" class="py-4 text-center">
                                        <i class="fa-solid fa-spinner fa-spin fa-2xl text-blue-500"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="flex justify-end mt-4">
                            <button id="submitAsignacionButton" type="submit"
                                class="inline-flex items-center px-10 py-3 text-sm font-bold text-center text-white bg-blue-700 rounded-xl hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-all duration-300 transform hover:scale-105 shadow-xl shadow-blue-500/20">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Asignar albarán
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endcan
    <!-- Complemento Modal -->


    {{-- @can('writePartes', [Auth::user(), $perfil])
    <div id="addComplemento-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full" style="background: none !important;">
        <div class="relative p-4 w-full max-w-xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                <!-- Modal header -->
                <div class="modal-header flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="modal-title text-lg font-semibold text-gray-900 dark:text-white">
                        Añadir complemento
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="addComplemento-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Cerrar</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form id="addComplementoForm" class="addComplementoForm p-4 md:p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="col-span-1">
                            <label for="numero_parte" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">N° Parte</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-hashtag text-xs"></i>
                                </div>
                                <input type="number" required id="numero_parte" name="numero_parte"
                                    class="numeroParte bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 dark:placeholder-gray-400 dark:text-white backdrop-blur-sm transition-all outline-none"
                                    placeholder="Nº de parte">
                            </div>
                        </div>
                        <div class="col-span-1">
                            <label for="cantidad" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Num piezas</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-cubes text-xs"></i>
                                </div>
                                <input type="text" required id="cantidad" name="cantidad"
                                    class="numeroPiezas bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 dark:placeholder-gray-400 dark:text-white backdrop-blur-sm transition-all outline-none"
                                    placeholder="0">
                                <input type="text" required readonly id="existencia" name="existencia" class="hidden">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1">
                        <label for="descripcion" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Descripción</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-start pt-3 pl-3 pointer-events-none text-gray-400">
                                <i class="fa-solid fa-align-left text-xs"></i>
                            </div>
                            <textarea id="descripcion" name="descripcion" rows="3" required
                                class="bg-gray-50/50 dark:bg-gray-700/50 border border-gray-300/50 dark:border-gray-600/50 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2.5 dark:placeholder-gray-400 dark:text-white backdrop-blur-sm transition-all outline-none"
                                placeholder="Descripción del complemento..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                            class="inline-flex items-center px-10 py-3 text-sm font-bold text-center text-white bg-blue-700 rounded-xl hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-all duration-300 transform hover:scale-105 shadow-xl shadow-blue-500/20">
                            <i class="fa-solid fa-plus mr-2"></i> Añadir complemento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
                            <span
                                class="inline-flex items-center text-gray-900 bg-gray-200 border border-gray-300 rounded-t-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 w-full">
                                Descripción
                            </span>
                            <input type="text" required id="descripcion" name="descripcion"
                                class="rounded-none rounded-b-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block w-full text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <input type="hidden" required readonly id="descripcion_w32" name="descripcion_w32">
                        </div>
                        <div class="grid gap-4  grid-cols-2">
                            <div class="col-span-1">
                                <span
                                    class="inline-flex items-center text-gray-900 bg-gray-200 border border-gray-300 rounded-t-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 w-full">
                                    Importe Unitario
                                </span>
                                <input type="text" readonly required id="importe" name="importe_unitario"
                                    class="rounded-none rounded-b-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block w-full text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="col-span-1">
                                <span
                                    class="inline-flex items-center px-3 text-gray-900 bg-gray-200 border border-gray-300 rounded-t-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 w-full">
                                    Total
                                </span>
                                <input type="text" readonly required id="importe_total" name="importe_total"
                                    class="rounded-none rounded-b-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block w-full text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                        </div>
                        <div class="flex justify-end mt-2">
                            <button id="guardarComplementoButton" type="submit"
                                class="complementoButton text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-1.5 mt-2 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                Guardar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endcan --}}

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
@endsection
