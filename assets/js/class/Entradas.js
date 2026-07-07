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

class Entradas extends General {
   constructor() {
      super();
      this.table = null;
      this.url = `${UrlProyecto}/${Perfil}/entradas`
   }

   DTable() {
      const obj2 = {
         columns: [
            { title: 'N° Entrada', data: 'numero_entrada' },
            { title: 'Importe', data: 'importe' },
            { title: 'VIN', data: 'siniestros.vehiculo_info.vin' },
            { title: 'N° Siniestro', data: 'siniestros.numero_siniestro' },
            { title: 'N° Orden', data: 'siniestros.numero_orden' },
            { title: 'N° Presupuesto', data: 'vales.presupuestos.numero_presupuesto' },
            { title: 'N° Vale', data: 'vales.numero_vale' },
            { title: 'Fecha de recepcion', data: 'fecha_recepcion' },
            {
               title: 'Opciones',
               data: null,
               className: 'text-center',
               render: function (data, type, row) {
                  return `<a href="${UrlProyecto}/${Perfil}/entradas/detalle/${data.id}" title="Ver detalle entrada ${data.numero_entrada}" target="_entrada_${data.id}" class=" text-white-700 hover:text-white border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-white-300 font-medium rounded-lg text-sm px-3  text-center dark:border-white-500 dark:text-white-500 dark:hover:text-white dark:hover:bg-white-600 dark:focus:ring-white-900" data-tooltip-target="tooltip-ver"><i class="fa-solid fa-door-open"></i></a>
                  
               `
                  // return `
                  //       <div class="flex items-center space-x-4">
                  //              <button type="button" onclick="window.location.href='${URLactual}/ver?numEntrada=${data.numero_entrada}'" class="flex items-center text-white-700 hover:text-white border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-white-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-white-500 dark:text-white-500 dark:hover:text-white dark:hover:bg-white-600 dark:focus:ring-white-900" data-tooltip-target="tooltip-ver">
                  //                <i class="fa-solid fa-door-open"></i>
                  //             </button>
                  //       </div>
                  //    `;
               }
            }
         ],
      };

      this.table = DT({
         idTable: "entradasTable",
         obj2: obj2,
         url: "entradas/get",
      });

      return this.table;
   }

   async crear() {
      try {
         const response = await fetch(`${this.url}/crear`, {
            method: 'POST',
            body: this.form
         });
         if (!response.ok) {
            console.error()
            throw new Error('Error en la respuesta del servidor');
         }

         return await response.json();
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

   // async cancelar(id) {
   //    try {
   //       const response = await fetch(`${this.url}/${id}/cancelar`, {
   //          method: 'POST',
   //          headers: {
   //             'Content-Type': 'application/json',
   //             'X-CSRF-TOKEN': token
   //          },
   //       });
   //       if (!response.ok) {
   //          console.error()
   //          throw new Error('Error en la respuesta del servidor');
   //       }

   //       return await response.json();
   //    } catch (error) {
   //       console.error('Error al cargar los datos:', error);
   //       swalAlert({
   //          title: 'Error',
   //          text: 'No se pudo conectar con el servidor. Intenta nuevamente.',
   //          icon: 'error',
   //          confirmButtonText: 'Aceptar',
   //          theme: 'auto',
   //       });
   //    }
   // }
}

export default Entradas;
