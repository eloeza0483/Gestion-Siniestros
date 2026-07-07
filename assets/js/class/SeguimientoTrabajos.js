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

class SeguimientoTrabajos extends General {
   constructor() {
      super();
      this.table = null;
      this.url = `${UrlProyecto}/${Perfil}/seguimiento-trabajos`
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
            {
               title: 'Estado',
               data: 'estado',
               render: function (data, type, row) {
                  let colorDot = '';
                  let textColor = '';
                  switch (data) {
                     case 'EnProceso':
                        colorDot = 'bg-green-500';
                        textColor = 'text-green-800 dark:text-green-300';
                        break;
                     case 'Pendiente':
                        colorDot = 'bg-yellow-500';
                        textColor = 'text-yellow-800 dark:text-yellow-300';
                        break;
                     case 'EnTaller':
                        colorDot = 'bg-blue-500';
                        textColor = 'text-blue-800 dark:text-blue-300';
                        break;
                     case 'Pausado':
                        colorDot = 'bg-orange-500';
                        textColor = 'text-orange-800 dark:text-orange-300';
                        break;
                     case 'Finalizado':
                        colorDot = 'bg-gray-500';
                        textColor = 'text-gray-800 dark:text-gray-300';
                        break;
                  }
                  return `<span class="inline-flex items-center ${textColor} text-xs font-medium">
                              <span class="w-2 h-2 me-1 ${colorDot} rounded-full"></span>
                              ${data}
                          </span>`;
               }
            },
         ],
      };

      this.table = DT({
         idTable: "seguimientoTrabajosTable",
         obj2: obj2,
         url: "seguimiento-trabajos/get",
      });

      return this.table;
   }

}

export default SeguimientoTrabajos;
