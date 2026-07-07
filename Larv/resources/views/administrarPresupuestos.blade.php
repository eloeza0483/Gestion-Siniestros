@extends('layouts.app')
@push('scripts')
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    @if ($modo === 'añadir')
        {{-- <script type="module" src="{{asset('assets/js/presupuestos/addPresupuestos.js')}}"></script> --}}
    @elseif($modo === 'ver')
        {{-- <script type="module" src="{{asset('assets/js/presupuestos/verPresupuestos.js')}}"></script> --}}
    @endif
@endpush

@section('content')
    <!-- Start block -->

    <div class="mx-auto max-w-screen-2xl px-4 lg:px-12 ">
        <div
            class="bg-white/50 dark:bg-gray-800/40 backdrop-blur-lg border border-gray-200/50 dark:border-gray-600/50 relative shadow-2xl sm:rounded-2xl overflow-hidden">
            <div class="flex flex-row md:items-center md:justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                <div class="flex-1 flex items-center justify-between space-x-2">
                    <h2>
                        <!-- Aqui va el texto dependiendo si es ver, cotizar o agregar -->
                        <span class="text-white-500 texto-presupuesto">
                            @if ($modo === 'añadir')
                                Añadir presupuesto
                            @endif
                        </span>
                        <span
                            class="bg-blue-700 text-white text-sm font-medium me-2 ml-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-white">
                            {{ $estado }}
                        </span>
                    </h2>
                </div>
            </div>
            <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700">

            <div class="relative shadow-md p-2 ">

                @if ($modo === 'añadir')
                    <form id="nuevoPresupuestoForm">
                    @elseif($modo === 'cotizar')
                        <form id="cotizarPresupuestoForm">
                        @elseif($modo === 'ver')
                            <form id="verPresupuestoForm" action="{{ route('evidencias.subir') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                @endif

                <div class="grid grid-cols-3 m-2">
                    <div class="flex m-2">
                        <span
                            class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                            Num orden
                        </span>
                        <input type="text" id="numero_orden" required
                            class="rounded-none bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Escribe el número de orden">
                        <input type="text" id="taller" name="taller" value="{{ $talleres->first()->nombre }}"
                            readonly
                            class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-not-allowed">
                    </div>
                    <div class="flex m-2">
                        <span
                            class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                            Num Siniestro
                        </span>
                        <input type="text" id="numero_siniestro" readonly
                            class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-not-allowed">
                    </div>
                    <div class="flex m-2">

                        @if ($nombre_perfil == 'Refacciones')
                            <span id="label_cliente_aseguradora"
                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Cliente
                            </span>
                            <input type="text" id="nombre_cliente" readonly
                                class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-not-allowed">
                        @else
                            <span
                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Aseguradora
                            </span>
                            <input type="text" id="aseguradora" readonly
                                class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-not-allowed">
                        @endif
                    </div>
                    <input id="id_siniestro" name="id_siniestro" type="hidden">
                    <input id="id_cliente" name="id_cliente" type="hidden">
                    <input id="codigo" name="codigo" type="hidden">
                    @can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class)
                        <input id="cotizarDirectamente" name="cotizarDirectamente" value="true" type="hidden">
                    @endcan
                    @if ($modo === 'cotizar')
                        <input id="canUpdatePresupuesto" type="hidden"
                            value="{{ Auth::user()->can('updatePresupuestos', \App\Models\Presupuesto::class) ? 'true' : 'false' }}">
                    @endif
                </div>

                <div class="grid grid-cols-4 m-2">

                    <div class="flex m-2">
                        <span
                            class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                            Vin
                        </span>
                        <input type="text" id="vin" readonly
                            class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-not-allowed">
                    </div>
                    <div class="flex m-2">
                        <span
                            class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                            Vehiculo
                        </span>
                        <input type="text" id="vehiculo" readonly
                            class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-not-allowed">
                    </div>
                    <div class="flex m-2">
                        <span
                            class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                            Marca
                        </span>
                        <input type="text" id="marca" readonly
                            class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-not-allowed">
                    </div>
                    <div class="flex m-2">
                        <span
                            class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                            Modelo
                        </span>
                        <input type="text" id="modelo" readonly
                            class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-not-allowed">
                    </div>
                </div>


                <div class="grid {{ $estado === 'Pendiente' ? 'grid-cols-3' : 'grid-cols-4' }} m-2 ">
                    <div
                        class="flex m-2 {{ Auth::user()->can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class) ? 'hidden' : '' }}">
                        <span
                            class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                            Proveedor
                        </span>
                        <select id="proveedor" name="proveedor"
                            {{ Auth::user()->can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class) ? '' : 'required' }}
                            class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="" disabled
                                {{ Auth::user()->can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class) ? '' : 'selected' }}>
                                Selecciona un proveedor</option>
                            <option value="CHEVROLET"
                                {{ Auth::user()->can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class) ? 'selected' : '' }}>
                                CHEVROLET</option>
                            <option value="KIA">KIA</option>
                            <option value="HONDA">HONDA</option>
                            <option value="MG">MG</option>
                            <option value="GAC">GAC</option>
                            <option value="DONGFENG">DONGFENG</option>
                            <option value="OTRO">OTRO</option>
                        </select>
                    </div>
                    @if ($modo != 'añadir')
                        <div class="hidden flex m-2">
                            <span
                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Estado
                            </span>
                            <input type="text" id="estado"
                                class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>
                        {{-- <div class="flex m-2 ">
                            <span
                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Fecha cotizado
                            </span>
                            <input type="date" id="fecha_cotizado"
                                class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div> --}}
                    @endif
                    @if ($modo === 'cotizar')
                        <div class="flex m-2" id="codigoClienteContainer">
                            <span
                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Código Cliente
                            </span>
                            <input type="text" id="codigoAutocar" name="codigoCliente"
                                class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>
                        <div id="checkPVPContainer" class="hidden items-center m-2">
                            <label for="checkPVP" class="inline-flex items-center cursor-pointer gap-2">
                                <input type="checkbox" id="checkPVP"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Usar PVP</span>
                            </label>
                        </div>
                    @endif
                    @if ($modo === 'ver' && ($estado === 'Pendiente' || $estado === 'SinCotizar'))
                        @can('writeEvidencias', Auth::user())
                            <div class="flex m-2">
                                <button type="button"
                                    class=" subirEvidencias inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg cursor-pointer hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-700">
                                    Enviar evidencias
                                </button>
                            </div>
                        @endcan
                    @endif
                    @if ($modo === 'añadir')
                        @can("writePresupuestos$nombre_perfil", \App\Models\Presupuesto::class)
                            <div class="flex m-2">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    SubTotal
                                </span>
                                <input type="text" name="subtotal"
                                    class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex m-2">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    IVA 16%
                                </span>
                                <input type="text" name="iva"
                                    class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex m-2">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Total
                                </span>
                                <input type="text" name="total"
                                    class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                        @else
                            <input type="hidden" name="subtotal" value="0.00">
                            <input type="hidden" name="iva" value="0.00">
                            <input type="hidden" name="total" value="0.00">
                        @endcan
                    @endif
                </div>

                @if ($modo === 'cotizar')
                    @if (
                        $nombre_perfil === 'Refacciones' ||
                            Auth::user()->can("writePresupuestos$nombre_perfil", \App\Models\Presupuesto::class))
                        <div class="grid grid-cols-3 m-2">
                            <div class="flex m-2">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    SubTotal
                                </span>
                                <input type="text" name="subtotal"
                                    class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex m-2">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    IVA 16%
                                </span>
                                <input type="text" name="iva"
                                    class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div class="flex m-2">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    Total
                                </span>
                                <input type="text" name="total"
                                    class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="subtotal" value="0.00">
                        <input type="hidden" name="iva" value="0.00">
                        <input type="hidden" name="total" value="0.00">
                    @endif
                @endif

                <div class="flex flex-col overflow-x-auto shadow-md sm:rounded-lg">
                    <div class="flex items-start">
                        @if ($modo === 'añadir')
                            <table id="agregarPresupuestoTable"
                                class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            @elseif($modo === 'cotizar')
                                <table id="cotizarPresupuestoTable"
                                    class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                @elseif($modo === 'ver')
                                    <table id="verPresupuestoTable"
                                        class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400 ">
                        @endif
                        <thead class="text-xs text-white uppercase bg-black dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    #
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    N° Parte
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Descripción
                                </th>
                                @can('readDescripcionW32', Auth::user())
                                    @if ($modo === 'ver')
                                        <th scope="col" class="px-6 py-3">
                                            Descripción W32
                                        </th>
                                    @endif
                                @endcan
                                <th scope="col" class="px-6 py-3">
                                    Cantidad
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Importe unitario
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Importe total
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Existencia
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Tiempo de entrega
                                </th>
                                @if (
                                    $modo != 'ver' &&
                                        ($modo != 'cotizar' ||
                                            ($modo === 'cotizar' && Auth::user()->can('updatePresupuestos', \App\Models\Presupuesto::class))))
                                    <th scope="col" class="px-6 py-3 flex flex-row ">
                                        <button type="button" id="agregarColumnaPresupuesto"
                                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm p-2.5 text-center inline-flex me-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 {{ $modo === 'ver' ? 'opacity-0' : ' ' }}">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                        <button type="button" id="eliminarColumnaPresupuesto"
                                            class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm p-2.5 text-center inline-flex items-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 opacity-0">
                                            <i class="fa-solid fa-minus"></i>
                                        </button>
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if ($modo === 'añadir')
                                <tr>
                                    <td class="px-4 py-1">
                                        <span
                                            class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">1</span>
                                    </td>
                                    <td class="px-2 py-1">
                                        @can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class)
                                            <input type="text" name="numero_parte_1" id="numero_parte_1" data-conteo="1"
                                                placeholder="Número de parte" required
                                                class="numero-parte bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        @else
                                            <input type="text" name="numero_parte" id="numero_parte" disabled
                                                placeholder="Número de parte"
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50">
                                        @endcan
                                    </td>
                                    <td class="px-2 py-1">
                                        @can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class)
                                            <input type="text" name="descripcion_1" id="descripcion_1" data-conteo="1"
                                                placeholder="Descripción de parte" required
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                            <input type="hidden" name="descripcion_w32_1" id="descripcion_w32_1"
                                                data-conteo="1" placeholder="Descripción de w32">
                                        @else
                                            <input type="text" name="descripcion" id="descripcion" data-conteo="1"
                                                placeholder="Descripción de parte" required
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        @endcan
                                    </td>
                                    <td class="px-2 py-1">
                                        @can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class)
                                            <input type="text" name="cantidad_1" id="cantidad_1" data-conteo="1"
                                                placeholder="0" required
                                                class="cantidad bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        @else
                                            <input type="text" name="cantidad" id="cantidad" placeholder="0" required
                                                class=" bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        @endcan
                                    </td>
                                    <td class="px-2 py-1">
                                        @can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class)
                                            <input type="text" name="precio_unitario_1" id="precio_unitario_1"
                                                data-conteo="1" placeholder=" $0.00" required
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        @else
                                            <input type="text" name="precio_unitario" id="precio_unitario" disabled
                                                placeholder="$0.00"
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50">
                                        @endcan
                                    </td>
                                    <td class="px-2 py-1">
                                        @can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class)
                                            <input type="text" name="importe_total_1" id="importe_total_1"
                                                data-conteo="1" placeholder=" $0.00" required
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        @else
                                            <input type="text" name="importe_total" id="importe_total" disabled
                                                placeholder="$0.00"
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50">
                                        @endcan
                                    </td>
                                    <td class="px-2 py-1">
                                        @can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class)
                                            <input type="number" name="existencia_1" id="existencia_1" data-conteo="1"
                                                placeholder="0" required
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        @else
                                            <input type="number" name="existencia" id="existencia" disabled
                                                placeholderagregarColumnaPresupuesto="0"
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50"
                                                placeholder="0">
                                        @endcan
                                    </td>
                                    <td class="px-2 py-1">
                                        @can("cotizarDirectamente$nombre_perfil", \App\Models\Presupuesto::class)
                                            <select name="tiempoentrega" id="tiempoentrega" data-conteo="1" placeholder=" 0"
                                                required
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                                <option value="1a3dias">1 a 3 dias</option>
                                                <option value="4a10dias">4 a 10 dias</option>
                                                <option value="BackOrder">Back Order </option>
                                            </select>
                                        @else
                                            <select name="tiempoentrega" id="tiempoentrega"
                                                class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50"
                                                disabled>
                                                <option value="1a3dias">1 a 3 dias</option>
                                                <option value="4a10dias">4 a 10 dias</option>
                                                <option value="BackOrder">Back Order </option>
                                            </select>
                                        @endcan
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        </table>

                    </div>
                    @if ($modo != 'ver')
                        <div class="flex justify-end mt-4  mb-2 mr-2">
                            <button type="submit" id="submitPresupuesto"
                                class="flex text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm p-2.5 me-1 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                Enviar
                            </button>
                        </div>
                    @endif
                </div>
                </form>

            </div>
        </div>
    </div>


    <!-- End block -->



    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
@endsection
