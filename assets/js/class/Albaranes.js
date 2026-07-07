import {
   Perfil,
   UrlProyecto,
   URLactual,
   showLoading,
   hideLoading,
   token,
   DT,
} from "../functions/const.js";
import General from "./General.js";

class Albaranes extends General {
   constructor() {
      super();
      this.table = null;
      this.url = `${UrlProyecto}/${Perfil}/albaranes`
   }

   DTable() {
      const obj2 = {
         columns: [
            { title: 'N° Albarán', data: 'numero_albaran' },
            { title: 'Importe', data: 'importe' },
            { title: 'VIN', data: 'siniestros.vehiculo_info.vin' },
            { title: 'N° Siniestro', data: 'siniestros.numero_siniestro' },
            { title: 'N° Presupuesto', data: 'vales.presupuestos.numero_presupuesto' },
            { title: 'N° Orden', data: 'siniestros.numero_orden' },
            { title: 'N° Vale', data: 'vales.numero_vale' },
            { title: 'Fecha de surtido', data: 'fecha_surtido' },
            {
               title: 'Opciones',
               data: null,
               render: function (data, type, row) {
                  return `
                        <div class="flex items-center space-x-4">
                               <button type="button" onclick="window.open('${UrlProyecto}/${Perfil}/albaranes/ver?numAlbaran=${data.numero_albaran}', '_blank')" class="flex items-center text-white-700 hover:text-white border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-white-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-white-500 dark:text-white-500 dark:hover:text-white dark:hover:bg-white-600 dark:focus:ring-white-900" data-tooltip-target="tooltip-ver">
                                 <i class="fa-solid fa-door-open"></i>
                              </button>
                              <button type="button" data-numero_albaran="${data.numero_albaran}'" class="flex items-center text-white-700 hover:text-white border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-white-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-white-500 dark:text-white-500 dark:hover:text-white dark:hover:bg-white-600 dark:focus:ring-white-900" data-tooltip-target="tooltip-ver">
                                <i class="fa-solid fa-ban"></i>
                              </button>
                        </div>
                     `;
               }
            }
         ],
      };

      this.table = DT({
         idTable: "albaranesTable",
         obj2: obj2,
         url: "albaranes/get",
      });

      return this.table;
   }

}

export default Albaranes;
