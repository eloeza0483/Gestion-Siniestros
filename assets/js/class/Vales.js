import {
  Perfil,
  UrlProyecto,
  URLactual,
  showLoading,
  hideLoading,
  token,
  DT, swalAlert
} from "../functions/const.js";
import General from "./General.js";

class Vales extends General {
  constructor() {
    super();
    this.table = null;
    this.url = `${UrlProyecto}/${Perfil}/vales`;
  }

  DTable() {
    // Obtener permisos del usuario
    let permisos = "";
    const span = document.getElementById("permisosSpan");
    if (span) {
      permisos = span.textContent || "";
    }

    // Determinar la URL según los permisos
    let fetchUrl = "vales/get";
    if (permisos.includes("ver.vales.chevrolet")) {
      fetchUrl = "vales/get?chevrolet=true";
    }

    const valesUrl = this.url;
    const obj2 = {
      columns: [
        { title: "N° Vale", data: "numero_vale" },
        { title: "VIN", data: "presupuestos.siniestros.vehiculo_info.vin" },
        { title: "Importe", data: "total" },
        {
          title: "Número de Siniestro",
          data: "presupuestos.siniestros.numero_siniestro",
        },
        { title: "N° Orden", data: "presupuestos.siniestros.numero_orden" },
        {
          title: "Estado",
          render: function (data, type, row) {
            const estados = {
              Completado: "bg-green-300 text-green-800",
              Abierto: "bg-blue-300 text-blue-800",
              Cancelado: "bg-red-300 text-red-800",
            };

            // Verificar si alguna pieza tiene solicitud_eliminacion
            const tieneSolicitudEliminacion = row.piezas?.some(pieza =>
              pieza.pivot?.solicitud_eliminacion === true || pieza.pivot?.solicitud_eliminacion === 1
            );

            // Crear badge de estado
            const badgetEstado = document.createElement("span");
            const clasesEstado = estados[row.estado] || "bg-gray-100 text-gray-800";

            // Agregar borde rojo y animación sutil si tiene solicitud de eliminación
            const bordeRojo = tieneSolicitudEliminacion ? "border-2 border-red-500 animate-pulse" : "";

            badgetEstado.className = `${clasesEstado} ${bordeRojo} text-sm font-normal px-1.5 py-0.5 rounded-full`;
            badgetEstado.textContent = row.estado;

            // Agregar tooltip si tiene solicitud de eliminación
            if (tieneSolicitudEliminacion) {
              badgetEstado.title = "Tiene solicitudes de eliminación pendientes";
            }

            return badgetEstado;
          },
        },
        {
          title: "Opciones",
          data: null,
          render: function (data, type, row) {
            // Obtenemos el contenido del span de permisos
            let permisos = "";
            const span = document.getElementById("permisosSpan");
            if (span) {
              permisos = span.textContent || "";
            }

            // Funciones para verificar permisos
            const puedeAbrir = permisos.includes("vales.view");
            const puedeaActualizarVale = permisos.includes("vales.update");
            const puedeCancelarVale = permisos.includes("vales.cancel");

            let botones = `
                          <div class="flex items-center space-x-2">
                       `;

            if (puedeAbrir) {
              botones += `<div class="flex items-center space-x-4">
                        <button type="button" data-num-vale="${data.numero_vale}" data-id-vale="${data.id}" class="btnAbrirVale flex items-center bg-gray-600 text-white hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 transition-transform duration-200 hover:scale-105">
                           Abrir
                        </button>`;
            }

            if (data.estado != "Cancelado" && puedeCancelarVale) {
              botones += `<button type="button" data-id="${data.id}" class="cancelValeButton flex items-center bg-red-500 text-white hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 transition-transform duration-200 hover:scale-105">
                                 Cancelar
                        </button>`;
            }

            botones += `
                        <a href="${valesUrl}/exportVale/${data.id}?folio=${data.numero_vale}" class="descargarExcelButton flex items-center bg-green-500 text-white hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 transition-transform duration-200 hover:scale-105">
                           <i class="fa-solid fa-file-excel"></i>
                           <i class="fa-solid fa-download p-0.5"></i>
                        </a>
                     </div>
                     `;

            botones += `</div>`;

            return botones;
          },
        },
      ],
    };

    this.table = DT({
      idTable: "valesTable",
      obj2: obj2,
      url: fetchUrl,
    });

    return this.table;
  }

  async cancelar(id, motivo) {
    try {
      const response = await fetch(`${this.url}/${id}/cancelar`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": token,
        },
        body: JSON.stringify({ motivo }),
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

  async getVale(numeroVale, idVale = null) {
    const query = idVale ? `?idVale=${encodeURIComponent(idVale)}` : "";
    const response = await fetch(`${UrlProyecto}/${Perfil}/vales/get/${numeroVale}${query}`);

    if (!response.ok) {
      throw new Error("Error al obtener el presupuesto");
    }
    const fullResult = await response.json();

    // Extraer el vale y los permisos según el nuevo formato del controlador
    const result = fullResult.vale;
    const permisosGlobales = fullResult.permisos || [];


    // Ahora surtidoAlbaranPorParte y surtidoEntradaPorParte vienen del backend
    const surtidoAlbaranPorParte = result.surtidoAlbaranPorParte || {};
    const surtidoEntradaPorParte = result.surtidoEntradaPorParte || {};

    // Obtener piezasAlbaranes y piezasEntradas para otras funcionalidades
    let piezasAlbaranes = [];
    let piezasEntradas = [];

    // Solo obtener albaranes si existen
    if (result.albaranes && result.albaranes.length > 0) {
      piezasAlbaranes = await this.getAllAlbaranes(numeroVale, result);
    }

    // Solo obtener entradas si existen
    if (result.entradas && result.entradas.length > 0) {
      piezasEntradas = await this.getAllEntradas(numeroVale, result);
    }

    // // Verificar si tiene permisos de modificar piezas
    // let permisos = "";
    // const span = document.getElementById("permisosSpan");
    // if (span) {
    //   permisos = span.textContent || "";
    // }

    // const puedeModificar = permisos.includes("partes.update");
    // const puedeEliminar = permisos.includes("partes.delete");

    const {
      id,
      estado,
      fecha_vale,
      fecha_promesa,
      albaranes,
      entradas,
      presupuestos,
    } = result;

    const piezas = result.piezas;

    return {
      id,
      estado,
      fecha_vale,
      fecha_promesa,
      albaranes,
      entradas,
      presupuestos,
      piezas,
      surtidoAlbaranPorParte,
      surtidoEntradaPorParte,
      // puedeModificar,
      // puedeEliminar,
      piezasAlbaranes,
      piezasEntradas,
      permisosGlobales
    };
  }

  async getAllAlbaranes(numeroVale, result) {
    let piezasAlbaranes = [];

    try {
      const response = await fetch(
        `${UrlProyecto}/${Perfil}/albaranes/w-32-all/${numeroVale}`,
        {
          method: "POST",
          body: JSON.stringify(
            result.albaranes.map((albaran) => albaran.numero_albaran),
          ),
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
        },
      );
      const data = await response.json();

      // Aplanar el array de arrays y almacenar las piezas
      data.forEach((albaran) => {
        if (Array.isArray(albaran)) {
          piezasAlbaranes = [...piezasAlbaranes, ...albaran];
        }
      });
    } catch (error) {
      console.error("Error al obtener las piezas:", error);
    }

    return piezasAlbaranes;
  }

  async getAllEntradas(numeroVale, result) {
    let piezasEntradas = [];

    try {
      const response = await fetch(
        `${UrlProyecto}/${Perfil}/entradas/w-32-all/${numeroVale}`,
        {
          method: "POST",
          body: JSON.stringify(
            result.entradas.map((entrada) => entrada.numero_entrada),
          ),
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
        },
      );
      const data = await response.json();

      // Aplanar el array de arrays y almacenar las piezas
      data.forEach((entrada) => {
        if (Array.isArray(entrada)) {
          piezasEntradas = [...piezasEntradas, ...entrada];
        }
      });
    } catch (error) {
      console.error("Error al obtener las piezas de entradas:", error);
    }

    return piezasEntradas;
  }
}

export default Vales;
