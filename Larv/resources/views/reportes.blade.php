@extends('layouts.app')

@section('content')
<script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
<link href="{{ asset('assets/css/dataTables.tailwindcss.css') }}" rel="stylesheet">
<script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/dataTables.tailwindcss.js') }}"></script>
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
{{-- SheetJS para Excel --}}
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<div class="mx-auto w-full max-w-[1600px] px-4 lg:px-10 pb-12">
   <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <div>
         <div class="mb-2 inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
            <i class="fa-solid fa-chart-line"></i>
            Reportes
         </div>
         <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
            Panel operativo
         </h1>
         <p id="dashboardSummary" class="mt-1 max-w-3xl text-sm text-gray-600 dark:text-gray-400">
            Resumen general por periodo y taller, con detalle filtrable por entidad.
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
         <span class="text-xs text-gray-500 dark:text-gray-400">Los indicadores usan periodo y taller; el detalle tambien usa entidad y estado.</span>
      </div>

      <form id="filtrosForm" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-[1.1fr_1fr_1.1fr_0.9fr_0.9fr_auto_auto] xl:items-end">
         <div>
            <label for="entidad" class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Entidad</label>
            <select id="entidad" name="entidad"
               class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
               <option selected value="siniestros">Siniestros</option>
               <option value="presupuestos">Presupuestos</option>
               <option value="vales">Vales</option>
               <option value="entradas">Entradas</option>
               <option value="albaranes">Albaranes</option>
            </select>
         </div>

         <div>
            <label for="estado" class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Estado</label>
            <select id="estado" name="estado"
               class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
               <option selected>Todos</option>
               <option value="Abierto">Abierto</option>
               <option value="Cerrado">Cerrado</option>
            </select>
         </div>

         <div>
            <label for="taller" class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Taller</label>
            <select id="taller" name="taller"
               class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
               <option selected value="Todos">Todos</option>
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

         <div class="flex items-center h-[42px] mb-1">
            <input id="ignorar_fechas" name="ignorar_fechas" type="checkbox"
               class="h-4.5 w-4.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:focus:ring-blue-600 cursor-pointer">
            <label for="ignorar_fechas" class="ml-2 text-xs font-semibold text-gray-600 dark:text-gray-400 cursor-pointer select-none">
               Todos los existentes
            </label>
         </div>

         <button type="button" id="btnBuscar"
            class="inline-flex h-[42px] items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-lg shadow-blue-900/10 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-gray-100 active:scale-[0.99] dark:focus:ring-offset-gray-950">
            <i class="fa-solid fa-magnifying-glass"></i>
            Buscar
         </button>
      </form>
   </section>

   <section id="dashboardSection" class="space-y-5" aria-live="polite">
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
         <article class="rounded-lg border border-gray-200/80 bg-white/85 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/60">
            <div class="flex items-start justify-between gap-3">
               <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Siniestros</p>
                  <p id="kpi-siniestros" class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">-</p>
               </div>
               <span class="rounded-lg bg-blue-100 p-2 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                  <i class="fa-solid fa-car-burst"></i>
               </span>
            </div>
            <p id="kpi-siniestros-help" class="mt-3 text-xs text-gray-500 dark:text-gray-400">Casos registrados en el periodo.</p>
         </article>

         <article class="rounded-lg border border-gray-200/80 bg-white/85 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/60">
            <div class="flex items-start justify-between gap-3">
               <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Vales</p>
                  <p id="kpi-vales" class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">-</p>
               </div>
               <span class="rounded-lg bg-violet-100 p-2 text-violet-700 dark:bg-violet-950 dark:text-violet-300">
                  <i class="fa-solid fa-ticket"></i>
               </span>
            </div>
            <p id="kpi-vales-help" class="mt-3 text-xs text-gray-500 dark:text-gray-400">Solicitudes emitidas.</p>
         </article>

         <article class="rounded-lg border border-gray-200/80 bg-white/85 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/60">
            <div class="flex items-start justify-between gap-3">
               <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Entradas</p>
                  <p id="kpi-entradas" class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">-</p>
               </div>
               <span class="rounded-lg bg-emerald-100 p-2 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                  <i class="fa-solid fa-boxes-stacked"></i>
               </span>
            </div>
            <p id="kpi-entradas-help" class="mt-3 text-xs text-gray-500 dark:text-gray-400">Recepciones registradas.</p>
         </article>

         <article class="rounded-lg border border-gray-200/80 bg-white/85 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/60">
            <div class="flex items-start justify-between gap-3">
               <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Albaranes</p>
                  <p id="kpi-albaranes" class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">-</p>
               </div>
               <span class="rounded-lg bg-amber-100 p-2 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                  <i class="fa-solid fa-file-invoice"></i>
               </span>
            </div>
            <p id="kpi-albaranes-help" class="mt-3 text-xs text-gray-500 dark:text-gray-400">Salidas documentadas.</p>
         </article>

         <article class="rounded-lg border border-blue-200 bg-blue-600 p-4 text-white shadow-sm dark:border-blue-500/40 dark:bg-blue-700">
            <div class="flex items-start justify-between gap-3">
               <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-blue-100">Importe total</p>
                  <p id="kpi-importe" class="mt-2 text-2xl font-bold">-</p>
               </div>
               <span class="rounded-lg bg-white/15 p-2 text-white">
                  <i class="fa-solid fa-dollar-sign"></i>
               </span>
            </div>
            <p id="kpi-importe-help" class="mt-3 text-xs text-blue-100">Total de vales del periodo.</p>
         </article>
      </div>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
         <article class="rounded-lg border border-gray-200/80 bg-white/70 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/50">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Promedio por vale</p>
            <p id="insight-promedio-vale" class="mt-2 text-xl font-bold text-gray-900 dark:text-white">-</p>
         </article>
         <article class="rounded-lg border border-gray-200/80 bg-white/70 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/50">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Vales con entrada</p>
            <p id="insight-ratio-entradas" class="mt-2 text-xl font-bold text-gray-900 dark:text-white">-</p>
         </article>
         <article class="rounded-lg border border-gray-200/80 bg-white/70 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/50">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado dominante</p>
            <p id="insight-estado-dominante" class="mt-2 text-xl font-bold text-gray-900 dark:text-white">-</p>
         </article>
         <article class="rounded-lg border border-gray-200/80 bg-white/70 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/50">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Taller con mayor carga</p>
            <p id="insight-taller-top" class="mt-2 truncate text-xl font-bold text-gray-900 dark:text-white">-</p>
         </article>
      </div>

      <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
         <article class="rounded-lg border border-gray-200/80 bg-white/85 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/60 xl:col-span-4">
            <div class="mb-3 flex items-center justify-between gap-3">
               <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                  <i class="fa-solid fa-circle-half-stroke mr-1 text-blue-500"></i>
                  Siniestros por estado
               </h3>
            </div>
            <div class="relative h-64">
               <canvas id="chartEstados"></canvas>
               <div id="emptyEstados" class="hidden absolute inset-0 place-items-center rounded-lg border border-dashed border-gray-300 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">Sin datos para graficar</div>
            </div>
         </article>

         <article class="rounded-lg border border-gray-200/80 bg-white/85 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/60 xl:col-span-4">
            <div class="mb-3 flex items-center justify-between gap-3">
               <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                  <i class="fa-solid fa-warehouse mr-1 text-violet-500"></i>
                  Siniestros por taller
               </h3>
            </div>
            <div class="relative h-64">
               <canvas id="chartTalleres"></canvas>
               <div id="emptyTalleres" class="hidden absolute inset-0 place-items-center rounded-lg border border-dashed border-gray-300 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">Sin datos para graficar</div>
            </div>
         </article>

         <article class="rounded-lg border border-gray-200/80 bg-white/85 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/60 xl:col-span-4">
            <div class="mb-3 flex items-center justify-between gap-3">
               <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                  <i class="fa-solid fa-chart-line mr-1 text-emerald-500"></i>
                  Vales por mes
               </h3>
               <span class="text-xs text-gray-500 dark:text-gray-400">Ultimos 6 meses</span>
            </div>
            <div class="relative h-64">
               <canvas id="chartMeses"></canvas>
               <div id="emptyMeses" class="hidden absolute inset-0 place-items-center rounded-lg border border-dashed border-gray-300 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">Sin datos para graficar</div>
            </div>
         </article>
      </div>
   </section>

   <section class="mt-5 rounded-lg border border-gray-200/80 bg-white/85 p-4 shadow-sm dark:border-gray-700/70 dark:bg-gray-900/60">
      <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
         <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
               <i class="fa-solid fa-table mr-1 text-blue-500"></i>
               Detalle
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Registros filtrados para consulta o exportacion.</p>
         </div>
      </div>
      <div class="relative overflow-x-auto">
         <table id="reportesTable" class="w-full text-sm"></table>
      </div>
   </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
   const hoy = new Date();
   const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
   const fmt = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
   
   const fechaInicioInput = document.getElementById('fecha_inicio');
   const fechaFinalInput = document.getElementById('fecha_final');
   
   if (fechaInicioInput) fechaInicioInput.value = fmt(inicioMes);
   if (fechaFinalInput) fechaFinalInput.value = fmt(hoy);

   const ignorarFechasCheckbox = document.getElementById('ignorar_fechas');
   if (ignorarFechasCheckbox && fechaInicioInput && fechaFinalInput) {
      ignorarFechasCheckbox.addEventListener('change', function () {
         const checked = this.checked;
         fechaInicioInput.disabled = checked;
         fechaFinalInput.disabled = checked;
         if (checked) {
            fechaInicioInput.classList.add('opacity-50', 'cursor-not-allowed');
            fechaFinalInput.classList.add('opacity-50', 'cursor-not-allowed');
         } else {
            fechaInicioInput.classList.remove('opacity-50', 'cursor-not-allowed');
            fechaFinalInput.classList.remove('opacity-50', 'cursor-not-allowed');
         }
      });
   }
});
</script>
@endsection
