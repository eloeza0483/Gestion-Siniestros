@extends('layouts.app')
@push('scripts')
<script src="{{asset('assets/js/lib/jquery-3.7.1.min.js')}}"></script>
{{-- <script type="module" src="{{asset('assets/js/entradas/verEntradas.js')}}"></script> --}}
@endpush

@section('content')
<input type="hidden" id="id_entrada" value="{{ $entrada->id??0 }}">
<!-- Start block -->
<section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5 antialiased">
   <div class="mx-auto max-w-screen-2xl px-4 lg:px-12">
      <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
         <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">

            <div class="flex-1 flex items-center space-x-2">
               <h1>
                  <span class="text-white font-bold texto-entrada text-2xl">
                     Detalle de la entrada <span class="numero-entrada inline-flex items-center rounded-md bg-blue-400/10 px-2 py-1 text-xs font-medium text-gray-400 inset-ring inset-ring-blue-400/20">{{ $entrada->numero_entrada ?? '' }}</span>
                  </span>
               </h1>
            </div>
         </div>
         <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700">



         <div class="relative shadow-md p-2 ">


            <form id="verEntradaForm">
               <div class="grid grid-cols-3 m-2">
                  <div class="flex m-2">
                     <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                        Num Siniestro
                     </span>
                     <input type="text" id="numero_siniestro" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        value="{{ $entrada->siniestros->numero_siniestro ?? ''}}">
                  </div>
                  <div class="flex m-2">
                     <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                        Aseguradora
                     </span>
                     <input type="text" id="aseguradora" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="{{ $entrada->siniestros->vehiculoInfo->aseguradora ?? '' }}">
                  </div>
                  <div class="flex m-2">
                     <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                        Importe
                     </span>
                     <input type="number" id="importe" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="{{ $entrada->importe ?? ''}}">
                  </div>
                  <input id="id_entrada" name="id_entrada" type="hidden">
               </div>

               <div class="grid grid-cols-3 m-2">
                  <div class="flex m-2">
                     <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                        Vehiculo
                     </span>
                     <input type="text" value="{{ $entrada->siniestros->vehiculoInfo->vehiculo ?? '' }}" id="vehiculo" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                  </div>
                  <div class="flex m-2">
                     <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                        Modelo
                     </span>
                     <input type="text" value="{{ $entrada->siniestros->vehiculoInfo->marca ?? '' }}" id="modelo" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                  </div>
                  <div class="flex m-2">
                     <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                        Estado
                     </span>
                     <input type="text" id="estado" value="{{ $entrada->estado ?? '' }}" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                     <input id="id_vale" name="id_vale" type="hidden">
                  </div>
               </div>

               <div class="flex flex-col overflow-x-auto shadow-md rounded-lg">
                  <div class="flex items-start justify-center">
                     <table id="verEntradaTable" class="overflow-y-auto w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-white uppercase bg-black dark:bg-gray-700 dark:text-gray-400">
                           <tr>
                              <th scope="col" class="px-6 py-3">
                                 N° Parte
                              </th>
                              <th scope="col" class="px-6 py-3">
                                 Descripción
                              </th>
                              <th scope="col" class="px-6 py-3">
                                 Piezas
                              </th>
                              <th scope="col" class="px-6 py-3">
                                 Importe unitario
                              </th>
                              <th scope="col" class="px-6 py-3">
                                 Importe total
                              </th>
                              @can('liberarPartes',Auth::user())
                              <th scope="col" class="px-6 py-3">
                                 Liberar
                              </th>
                              @endcan
                           </tr>
                        </thead>
                        <tbody>
                           @isset($partes)
                           @foreach ($partes as $index => $p)
                           @php($pieza = $p->piezas)
                           <tr>
                              <td class="px-2 py-1">
                                 <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">{{$pieza->numero_parte??''}}</span>
                              </td>
                              <td class="px-2 py-1">
                                 <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">{{$pieza->descripcion_w32??''}}</span>
                              </td>
                              <td class="px-2 py-1">
                                 <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">{{$p->cantidad??0}}</span>
                              </td>
                              <td class="px-2 py-1">
                                 <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">{{ $pieza->importe_unitario }}</span>
                              </td>
                              <td class="px-2 py-1">
                                 <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">{{$pieza->importe_unitario??0 * $pieza->cantidad??0}}</span>
                              </td>
                              <td class="px-2 py-1">
                                 @can('liberarPartes',Auth::user())
                                 <button
                                    type="button"
                                    class="liberarParteButton bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded"
                                    title="Liberar parte"
                                    data-index="{{ $index }}"
                                    data-id="{{$pieza->id ?? ''}}"
                                    data-numpartes="{{$pieza->numero_pzas_presupuesto ?? ''}}"
                                    data-nparte="{{$pieza->numero_parte}}">
                                    <i class="fa-solid fa-unlock"></i>
                                 </button>
                                 @endcan
                              </td>
                           </tr>

                           @endforeach
                           @endisset
                        </tbody>
                     </table>
                  </div>
               </div>
            </form>

         </div>
      </div>
</section>
<!-- End block -->



<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
@endsection