@extends('layouts.app')
@push('scripts')
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    {{-- <script type="module" src="{{asset('assets/js/vales/addVale.js')}}"></script> --}}
@endpush

@section('content')
    <!-- Start block -->
    <section class=" p-3 sm:p-5 antialiased ">
        <div class="mx-auto w-full px-4 lg:px-12 ">
            <div class="grid grid-cols-2 gap-4 ">
                <!-- Primera sección -->
                <div
                    class="bg-white/50 dark:bg-gray-800/40 backdrop-blur-lg border border-gray-200/50 dark:border-gray-600/50 relative shadow-md sm:rounded-lg overflow-hidden p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                        <div class="flex-1 flex items-center space-x-2">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                                <span class="numPresupuestoSpan">
                                    <!-- Dinámico -->
                                </span>
                            </h2>
                        </div>
                    </div>
                    <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700 mb-6">
                    <div class="relative">
                        <form id="presupuestoForm" class="space-y-3">
                            <!-- Fila 1: Metadatos técnicos cortos -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 font-bold uppercase">
                                        # Orden
                                    </span>
                                    <input type="text" id="numero_orden" required
                                        class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="Orden">
                                </div>
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 font-bold uppercase">
                                        # Siniestro
                                    </span>
                                    <input type="text" id="numero_siniestro"
                                        class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                </div>
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 font-bold uppercase">
                                        Vin
                                    </span>
                                    <input type="text" id="vin"
                                        class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Fila 2: Datos del vehículo -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 font-bold uppercase">
                                        Vehiculo
                                    </span>
                                    <input type="text" id="vehiculo"
                                        class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                </div>
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 font-bold uppercase">
                                        Marca
                                    </span>
                                    <input type="text" id="marca"
                                        class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                </div>
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 font-bold uppercase">
                                        Modelo
                                    </span>
                                    <input type="text" id="modelo"
                                        class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                </div>
                                <input id="id_siniestro" name="id_siniestro" type="hidden">
                                <input id="codigo" name="codigo" type="hidden">
                            </div>

                            <!-- Fila 3: Campos de texto medio (Proveedor y Taller) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 font-bold uppercase">
                                        Proveedor
                                    </span>
                                    <select id="proveedor" name="proveedor" required disabled
                                        class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 text-gray-500 cursor-not-allowed">
                                        <option value="" disabled selected>Proveedor</option>
                                        <option value="CHEVROLET">CHEVROLET</option>
                                        <option value="KIA">KIA</option>
                                        <option value="HONDA">HONDA</option>
                                        <option value="OTRO">OTRO</option>
                                    </select>
                                </div>
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 font-bold uppercase">
                                        # Taller
                                    </span>
                                    <input type="text" id="taller" name="taller" readonly
                                        class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Fila 4: Campo de texto largo (Cliente/Aseguradora) -->
                            <div class="grid grid-cols-1">
                                <div class="flex">
                                    <span id="label_cliente_aseguradora"
                                        class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600 font-bold uppercase">
                                        Cliente
                                    </span>
                                    <input type="text" id="aseguradora"
                                        class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 font-bold">
                                </div>
                            </div>

                            <div class="overflow-x-auto shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                                <table id="verPresupuestoTable"
                                    class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead
                                        class="text-xs text-white uppercase bg-black dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th class="px-3 py-3">#</th>
                                            <th class="px-3 py-3">N° Parte</th>
                                            <th class="px-3 py-3">Descripción</th>
                                            <th class="px-3 py-3">Cantidad</th>
                                            <th class="px-3 py-3">Imp. Unitario</th>
                                            <th class="px-3 py-3">Imp. Total</th>
                                            <th class="px-3 py-3">Existencia</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <!-- Dinámico -->
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Segunda sección -->
                <form id="valeForm" class="h-full">
                    <div
                        class="bg-white/50 dark:bg-gray-800/40 backdrop-blur-lg border border-gray-200/50 dark:border-gray-600/50 relative shadow-md sm:rounded-lg overflow-hidden p-6 h-full flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                            <div class="flex-1 flex items-center space-x-2">
                                <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                                    <span># Vale</span>
                                </h2>
                            </div>
                        </div>
                        <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700 mb-6">

                        <div class="mb-6">
                            <label for="numero_vale"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Número de Vale</label>
                            <input id="numero_vale" required name="numero_vale" type="text"
                                placeholder="Ingrese el número del vale"
                                class="w-full p-2.5 bg-white/50 dark:bg-gray-700/50 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Vale
                                </span>
                                <input type="date" id="fecha_vale" name="fecha_vale" required
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Promesa
                                </span>
                                <input type="date" id="fecha_promesa" name="fecha_promesa" required
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                                    class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="flex flex-col">
                                <span
                                    class="inline-flex items-center px-3 py-1.5 text-xs text-gray-900 bg-gray-200 border border-gray-300 rounded-t-lg dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    SubTotal
                                </span>
                                <input type="text" id="subtotal" name="subtotal" readonly
                                    class="rounded-none rounded-b-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block w-full text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="inline-flex items-center px-3 py-1.5 text-xs text-gray-900 bg-gray-200 border border-gray-300 rounded-t-lg dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    IVA 16%
                                </span>
                                <input type="text" id="iva" name="iva" readonly
                                    class="rounded-none rounded-b-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block w-full text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="inline-flex items-center px-3 py-1.5 text-xs text-gray-900 bg-gray-200 border border-gray-300 rounded-t-lg dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Total
                                </span>
                                <input type="text" id="total" name="total" readonly
                                    class="rounded-none rounded-b-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block w-full text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                        </div>

                        <div
                            class="flex-grow overflow-x-auto shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
                            <table id="piezasDisponiblesTable"
                                class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-[10px] font-bold uppercase tracking-widest text-white bg-gray-800 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 rounded-tl-xl">N° Parte</th>
                                        <th class="px-4 py-3">Descripción</th>
                                        <th class="px-4 py-3 text-center">Piezas</th>
                                        <th class="px-4 py-3 text-right">Imp. Unitario</th>
                                        <th class="px-4 py-3 text-right">Total (no IVA)</th>
                                        <th class="px-4 py-3 text-center">Sel.</th>
                                        <th class="px-4 py-3 text-center rounded-tr-xl">Falt.</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <!-- Dinámico -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-auto pt-4 flex justify-end">
                            <button type="submit" id="submitVale"
                                class="inline-flex items-center px-10 py-3 text-sm font-bold text-center text-white bg-blue-700 rounded-xl hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-all duration-300 transform hover:scale-105 shadow-xl shadow-blue-500/20">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Enviar Vale
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
@endsection
