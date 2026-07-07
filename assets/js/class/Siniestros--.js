import {
  UrlProyecto,
  showLoading,
  hideLoading,
  token,
  DT,
, swalAlert} from "../functions/const.js";
import General from "./General.js";

class Siniestros extends General {
  constructor() {
    super();
    this.table = null;
    this.url = `${UrlProyecto}/siniestros`;
  }

  DTable() {
    const obj2 = {
      columns: [
        { title: "Número de Orden", data: "numero_orden" },
        { title: "Vin", data: "vehiculo_info.vin" },
        { title: "Número de Siniestro", data: "numero_siniestro" },
        { title: "Aseguradora", data: "vehiculo_info.aseguradora" },
        {
          title: "Tot. Presupuestos",
          createdCell: function (td, cellData, rowData, row, col) {
            td.classList.add("text-center");
          },
          data: function (row) {
            return `<a href="#" class="open-modal text-blue-700 dark:text-green-500 dark:hover:text-blue-500 hover:underline" data-numOrden="${row.numero_orden}" data-tipo-modal="total-presupuestos" data-modal-target="siniestros-modal">${row.num_presupuestos}</a>`;
          },
        },
        {
          title: "Tot. Vales",
          createdCell: function (td, cellData, rowData, row, col) {
            td.classList.add("text-center");
          },
          data: function (row) {
            return `<a class=" text-blue-700 dark:text-green-500 select-none" data-numOrden="${row.numero_orden}" data-modal-target="siniestros-modal">${row.num_vales}</a>`;
          },
        },
        {
          title: "Tot. Entradas",
          createdCell: function (td, cellData, rowData, row, col) {
            td.classList.add("text-center");
          },
          data: function (row) {
            return `<a class=" text-blue-700 dark:text-green-500 select-none" data-numOrden="${row.numero_orden}" data-modal-target="siniestros-modal">${row.num_entradas}</a>`;
          },
        },
        {
          title: "Pzs Autorizadas",
          createdCell: function (td, cellData, rowData, row, col) {
            td.classList.add("text-center");
          },
          data: function (row) {
            return `<a href="#" class="open-modal text-blue-700 dark:text-green-500 dark:hover:text-blue-500 hover:underline" data-numOrden="${row.numero_orden}" data-tipo-modal="pzs-autorizadas" data-modal-target="siniestros-modal">${row.pzs_autorizadas}</a>`;
          },
        },
        {
          title: "Pzs surtidas",
          createdCell: function (td, cellData, rowData, row, col) {
            td.classList.add("text-center");
          },
          data: function (row) {
            return `<a href="#" class="open-modal text-blue-700 dark:text-green-500 dark:hover:text-blue-500 hover:underline" data-numOrden="${row.numero_orden
              }" data-tipo-modal="pzs-surtidas" data-modal-target="siniestros-modal">${row.pzs_surtidas ? row.pzs_surtidas : "Sin datos"
              }</a>`;
          },
        },
        {
          title: "Pzs Recibidas",
          createdCell: function (td, cellData, rowData, row, col) {
            td.classList.add("text-center");
          },
          data: function (row) {

            return `<a href="#" class="open-modal text-blue-700 dark:text-green-500 dark:hover:text-blue-500 hover:underline" data-numOrden="${row.numero_orden}"data-tipo-modal="pzs-recibidas" data-modal-target="siniestros-modal">${row.pzs_recibidas}</a>`;
          },
        },
        {
          title: "% Recibidas",
          createdCell: function (td, cellData, rowData, row, col) {
            td.classList.add("text-center");
          },
          data: function (row) {
            return `<a class=" text-blue-700 dark:text-green-500 select-none" data-numOrden="${row.numero_orden}%" data-modal-target="siniestros-modal">${row.porcentaje_pzs_recibidas}%</a>`;
          },
        },
        {
          title: "Pzs Faltantes",
          createdCell: function (td, cellData, rowData, row, col) {
            td.classList.add("text-center");
          },
          data: function (row) {
            let tiempos = [];
            try {
              tiempos = JSON.parse(row.tiempo_surtido);
            } catch (e) {
              console.error("Error parsing tiempo_surtido", e);
            }

            if (!tiempos || tiempos.length === 0) {
            }
            let colorTextPzaRecibida = "text-green-500";
            let hasBackOrder = false;
            let hasMediumDelay = false;

            tiempos.forEach((tiempo) => {
              const status = String(tiempo).replace(/\s+/g, "").toLowerCase();
              if (status === "1a3dias") {
              } else if (status === "4a10dias") {
                hasMediumDelay = true;
              } else {
                hasBackOrder = true;
              }
            });

            if (hasBackOrder) {
              colorTextPzaRecibida = "text-orange-500";
            } else if (hasMediumDelay) {
              colorTextPzaRecibida = "text-yellow-500";
            }
            return `<a href="#" class="open-modal ${colorTextPzaRecibida} dark:${colorTextPzaRecibida} hover:underline" data-numOrden="${row.numero_orden}" data-tipo-modal="pzs-faltantes" data-modal-target="siniestros-modal">${row.pzs_faltantes}</a>`;
          },
        },
        {
          title: "Opciones",
          data: function (data) {
            const permisosSpan = document.getElementById("permisosSpan");
            //PENDIENTE -- ESTE BOTON JAMAS LO HE VISTO
            if (data.estado === "Cerrado") {
              return `
                          <div class="flex items-center space-x-4">
                              <button type="button" data-id="${data.id}" class="reabrirSiniestroButton flex items-center text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 ">
                                  Reabrir
                              </button>
                          </div>
                      `;
            } else {
              return `
                          <div class="flex items-center space-x-4">
                            <button type="button" data-id="${data.id}" class="cerrarSiniestroButton py-2 px-3 flex items-center text-sm font-semibold text-center text-gray-800 bg-yellow-100 hover:bg-yellow-600 dark:bg-yellow-300 dark:hover:bg-yellow-600 rounded-lg transition duration-300 ease-in-out">
                                 Cerrar 
                              </button>
                              <button type="button" data-id="${data.id}" class="cancelSiniestroButton py-2 px-3 flex items-center text-sm font-semibold text-center text-gray-800 bg-red-300 hover:bg-red-600 rounded-lg dark:hover:bg-red-600 transition duration-300 ease-in-out">
                                  Cancelar
                              </button>
                          </div>
                           
                      `;
            }
          },
          visible: permisosSpan.textContent.includes("siniestros.update"),
        },
      ],
    };

    // <div class="flex items-center space-x-4">
    //                         <button type="button" data-id="${data.id}" class=" cerrarSiniestroButton py-2 px-3 flex items-center text-sm font-medium text-center text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 rounded-lg dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900" style="transition: background-color 0.2s;">
    //                             Cerrar
    //                         </button>
    //                         <button type="button" data-id="${data.id}" class="cancelSiniestroButton flex items-center text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
    //                             Cancelar
    //                         </button>
    //                     </div>

    this.table = DT({
      idTable: "siniestrosTable",
      // obj2: {},
      obj2: obj2,
      url: "siniestros/getSiniestros",
    });

    return this.table;
  }

  async crear() {
    try {
      const response = await fetch(`${this.url}/crear`, {
        method: "POST",
        body: this.form,
      });
      if (!response.ok) {
        console.error();
        throw new Error("Error en la respuesta del servidor");
      }

      return await response.json();
    } catch (error) {
      console.error("Error al cargar los datos:", error);
      swalAlert({
        title: "Error",
        text: "No se pudo conectar con el servidor. Intenta nuevamente.",
        icon: "error",
        confirmButtonText: "Aceptar",
        theme: "auto",
      });
    }
  }

  async cancelar(id) {
    try {
      const response = await fetch(`${this.url}/${id}/cancelar`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": token,
        },
      });
      if (!response.ok) {
        console.error();
        throw new Error("Error en la respuesta del servidor");
      }

      return await response.json();
    } catch (error) {
      console.error("Error al cargar los datos:", error);
      swalAlert({
        title: "Error",
        text: "No se pudo conectar con el servidor. Intenta nuevamente.",
        icon: "error",
        confirmButtonText: "Aceptar",
        theme: "auto",
      });
    }
  }

  async reabrir(id) {
    try {
      const response = await fetch(`${this.url}/${id}/cancelar`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": token,
        },
      });
      if (!response.ok) {
        console.error();
        throw new Error("Error en la respuesta del servidor");
      }

      return await response.json();
    } catch (error) {
      console.error("Error al cargar los datos:", error);
      swalAlert({
        title: "Error",
        text: "No se pudo conectar con el servidor. Intenta nuevamente.",
        icon: "error",
        confirmButtonText: "Aceptar",
        theme: "auto",
      });
    }
  }

  async cerrar(id) {
    try {
      const response = await fetch(`${this.url}/${id}/cerrar`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": token,
        },
      });
      if (!response.ok) {
        console.error();
        throw new Error("Error en la respuesta del servidor");
      }

      return await response.json();
    } catch (error) {
      console.error("Error al cargar los datos:", error);
      swalAlert({
        title: "Error",
        text: "No se pudo conectar con el servidor. Intenta nuevamente.",
        icon: "error",
        confirmButtonText: "Aceptar",
        theme: "auto",
      });
    }
  }
}

export default Siniestros;
