@extends('layouts.app')
@push('scripts')
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    {{-- <script type="module" src="{{asset('assets/js/albaranes/verAlbaranes.js')}}"></script> --}}
@endpush

@section('content')
    <section class="p-3 sm:p-5 antialiased">
        <div class="mx-auto w-full px-4 lg:px-12">
            <div
                class="bg-white/50 dark:bg-gray-800/40 backdrop-blur-lg border border-gray-200/50 dark:border-gray-600/50 relative shadow-md sm:rounded-lg overflow-hidden p-6">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
                    <div class="flex-1">
                        <h2 class="text-xl font-bold tracking-tight text-gray-800 dark:text-white">
                            <span class="texto-albaran">
                                <!-- Dinámico -->
                            </span>
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Detalle del albarán y piezas registradas en el sistema.
                        </p>
                    </div>
                </div>

                <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700 mb-6">

                <form id="verAlbaranForm" class="space-y-6">
                    <input id="id_albaran" name="id_albaran" type="hidden">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Num Siniestro
                            </span>
                            <input type="text" id="numero_siniestro" readonly
                                class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Aseguradora
                            </span>
                            <input type="text" id="aseguradora" readonly
                                class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Importe
                            </span>
                            <input type="text" id="importe" readonly
                                class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 font-semibold dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Vehículo
                            </span>
                            <input type="text" id="vehiculo" readonly
                                class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Modelo
                            </span>
                            <input type="text" id="modelo" readonly
                                class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 text-xs text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                Estado
                            </span>
                            <input type="text" id="estado" readonly
                                class="rounded-none rounded-e-lg bg-white/50 dark:bg-gray-700/50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 font-semibold dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-2xl border border-white/20 dark:border-gray-700/70 bg-white/30 dark:bg-gray-900/30 shadow-lg backdrop-blur-sm">
                        <div
                            class="px-5 py-4 border-b border-gray-200/70 dark:border-gray-700/70 bg-white/40 dark:bg-gray-800/30">
                            <h3 class="text-sm font-semibold tracking-wide text-gray-700 dark:text-gray-200 uppercase">
                                Piezas del albarán
                            </h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table id="verAlbaranTable" class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                                <thead
                                    class="text-[11px] font-semibold uppercase tracking-[0.08em] text-sky-800 dark:text-sky-300 bg-slate-100/80 dark:bg-slate-800/80">
                                    <tr>
                                        <th scope="col" class="px-5 py-4">N° Parte</th>
                                        <th scope="col" class="px-5 py-4">Descripción</th>
                                        <th scope="col" class="px-5 py-4">Piezas</th>
                                        <th scope="col" class="px-5 py-4">Importe unitario</th>
                                        <th scope="col" class="px-5 py-4">Importe total</th>
                                        @can('liberarPartes', Auth::user())
                                            <th scope="col" class="px-5 py-4 text-center">Liberar</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200/70 dark:divide-gray-700/70">
                                    <!-- Dinámico -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
@endsection
