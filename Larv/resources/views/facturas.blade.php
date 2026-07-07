@extends('layouts.app')

@section('content')
<script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
<link href="{{ asset('assets/css/dataTables.tailwindcss.css') }}" rel="stylesheet">
<script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/dataTables.tailwindcss.js') }}"></script>
<script type="module" src="{{ asset('assets/js/facturas/facturas.js') }}"></script>

<!-- Start block -->
   <div class="mx-auto max-w-screen-2xl px-4 lg:px-12">
      <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
         <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            <div class="flex-1 flex items-center space-x-2">
               <h1>
                  <span class="dark:text-white">Facturas</span>
               </h1>
            </div>
         </div>
         <div class="flex flex-col md:flex-row items-stretch md:items-center md:space-x-3 space-y-3 md:space-y-0 justify-between mx-4 py-4 border-t dark:border-gray-700">
            <div class="w-full md:w-1/2">
               <form class="flex items-center">
                  <label for="simple-search" class="sr-only">Buscar</label>
                  <div class="relative w-full">
                     <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                           <path fill-rule="evenodd" clip-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" />
                        </svg>
                     </div>
                     <input type="text" id="simple-search" placeholder="Buscar" required="" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                  </div>
               </form>
            </div>
         </div>
         <div class="relative shadow-md p-2 ">

            <table id="facturasTable">
            </table>

         </div>
      </div>
   </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
@endsection