@extends('layouts.app')

@section('content')
<link href="{{ asset('assets/css/dataTables.tailwindcss.css') }}" rel="stylesheet">
<script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/dataTables.tailwindcss.js') }}"></script>
{{-- SheetJS para Excel --}}
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<div class="mx-auto w-full max-w-[1600px] px-4 lg:px-10 pb-12">
   <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <div>
         <div class="mb-2 inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
            <i class="fa-solid fa-gears"></i>
            Refacciones
         </div>
         <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
            Reporte de Refacciones
         </h1>
         <p id="dashboardSummary" class="mt-1 max-w-3xl text-sm text-gray-600 dark:text-gray-400">
            Detalle de cotizaciones y autorizaciones agrupadas por vale y presupuesto.
         </p>
      </div>

      <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
         <span id="lastUpdated" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white/70 px-3 py-2 text-xs font-medium text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-400">
            Pendiente de actualizar
         </span>
         <button id="btnExcelExport" type="button"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-900/10 transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-gray-100 active:scale-[0.99] dark:focus:ring-offset-gray-950">
            <i class="fa-solid fa-file-excel"></i>
            Exportar Excel
         </button>
      </div>
   </div>

   <section class="mb-5 rounded-lg border border-gray-200/80 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-gray-700/70 dark:bg-gray-900/60">
      <div class="mb-4 flex items-center justify-between gap-3">
         <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
            <i class="fa-solid fa-sliders text-blue-500"></i>
            Filtros
         </h2>
      </div>

      <form id="filtrosForm" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-[1.5fr_1fr_1fr_auto] xl:items-end">
         <div>
            <label for="taller" class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Taller</label>
            <select id="taller" name="taller[]" multiple="multiple"
               class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
               @foreach($perfiles as $perfil)
               <option value="{{ $perfil->nombre }}">{{ $perfil->nombre }}</option>
               @endforeach
            </select>
         </div>

         <div>
            <label for="fecha_inicio" class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Desde</label>
            <div class="relative">
               <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                  <i class="fa-solid fa-calendar-days text-sm"></i>
               </span>
               <input id="fecha_inicio" name="fecha_inicio" type="date"
                  class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 pl-9 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
         </div>

         <div>
            <label for="fecha_final" class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Hasta</label>
            <div class="relative">
               <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                  <i class="fa-solid fa-calendar-days text-sm"></i>
               </span>
               <input id="fecha_final" name="fecha_final" type="date"
                  class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 pl-9 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
         </div>

         <button type="button" id="btnBuscar"
            class="inline-flex h-[42px] items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-lg shadow-blue-900/10 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-gray-100 active:scale-[0.99] dark:focus:ring-offset-gray-950">
            <i class="fa-solid fa-magnifying-glass"></i>
            Buscar
         </button>
      </form>
   </section>

   <section class="mt-5 rounded-lg border border-gray-200/80 bg-white/85 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/60">
      <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
         <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
               <i class="fa-solid fa-table mr-1 text-blue-500"></i>
               Detalle de Refacciones
            </h2>
         </div>
      </div>
      <div class="relative overflow-x-auto">
         <table id="refaccionesTable" class="w-full text-sm"></table>
      </div>
   </section>
</div>
@push('scripts')
   <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
   <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
   <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
   <script type="module" src="{{ asset('assets/js/functions/reporteRefacciones.js') }}"></script>
@endpush
@endsection
