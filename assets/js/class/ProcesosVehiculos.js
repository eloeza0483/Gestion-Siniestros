import {
   Perfil,
   UrlProyecto,
   URLactual,
   showLoading,
   hideLoading,
   token,
   DT,
   swalAlert
} from "../functions/const.js";
import General from "./General.js";

class ProcesosVehiculos extends General {
   constructor() {
      super();
      this.table = null;
      this.url = `${UrlProyecto}/${Perfil}/procesos-vehiculos`
   }

   DTable() {
      const obj2 = {
         columns: [
            {
               title: 'N° Orden',
               data: 'siniestros',
               render: function(data) {
                  return data && data.length > 0 ? data.map(s => s.numero_orden).join(', ') : 'Sin datos';
               }
            },
            { title: 'VIN', data: 'vin' },
            { title: 'Aseguradora', data: 'aseguradora' },
            { title: 'Taller', data: 'taller' },
            { title: 'Marca', data: 'marca' },
            { title: 'Modelo', data: 'modelo' },
            { title: 'Vehiculo', data: 'vehiculo' },
            { title: 'Estado', data: 'estado' },
            {
               title: 'Opciones',
               data: null,
               render: function (data, type, row) {

                  let botones = `<div class="flex items-center space-x-2">`;

                  // // Botón para abrir/ver, siempre presente
                  // botones += `
                  //    <button type="button" onclick="window.location.href='${URLactual}/ver?numEntrada=${data.id}'" class="flex items-center text-gray-700 hover:text-white border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-gray-500 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                  //          <i class="fa-solid fa-door-open"></i>
                  //    </button>
                  // `;

                  switch (data.estado) {
                     case 'Pendiente':
                        botones += `
                           <button type="button" data-estado="EnProceso" data-id='${data.id}'  class="cambiar-estado flex items-center text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                                 <i class="fa-solid fa-play mr-1"></i>
                                 Iniciar
                           </button>
                        `;
                        break;
                     case 'EnProceso':
                        botones += `
                           <button type="button" data-estado="Pausado" data-id='${data.id}''  class="cambiar-estado flex items-center text-yellow-400 hover:text-white border border-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-yellow-300 dark:text-yellow-300 dark:hover:text-white dark:hover:bg-yellow-400 dark:focus:ring-yellow-900">
                                 <i class="fa-solid fa-pause mr-1"></i>
                                 Pausar
                           </button>
                           <button type="button" data-estado="Finalizado" data-id='${data.id}'  class="cambiar-estado flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                                 <i class="fa-solid fa-flag-checkered mr-1"></i>
                                 Finalizar
                           </button>
                        `;
                        break;
                     case 'Pausado':
                        botones += `
                           <button type="button" data-estado="EnProceso" data-id='${data.id}'  class="cambiar-estado flex items-center text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                                 <i class="fa-solid fa-backward-step mr-1"></i>
                                 Reanudar
                           </button>
                        `;
                        break;
                  }

                  botones += `</div>`;

                  return botones;
               }
            }
         ],
      };

      this.table = DT({
         idTable: "procesosVehiculosTable",
         obj2: obj2,
         url: "procesos-vehiculos/get",
      });

      return this.table;
   }



   async cambiarEstadoProceso(id, estado, adelanto = 0, motivo_pausa = null) {

      try {
         const response = await fetch(`${this.url}/cambiar-estado/${id}`, {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ estado, adelanto, motivo_pausa })
         });
         if (!response.ok) {
            console.error()
            throw new Error('Error en la respuesta del servidor');
         }

         const data = await response.json();
         if (data.success) {
            $("#procesosVehiculosTable").DataTable().ajax.reload();
         }

         return data;
      } catch (error) {
         console.error('Error al cargar los datos:', error);
         swalAlert({
            title: 'Error',
            text: 'No se pudo conectar con el servidor. Intenta nuevamente.',
            icon: 'error',
            confirmButtonText: 'Aceptar',
            theme: 'auto',
         });
      }
   }

}

export default ProcesosVehiculos;
