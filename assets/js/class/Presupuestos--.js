import {
  UrlProyecto,
  URLactual,
  showLoading,
  hideLoading,
  DT,
  token,
, swalAlert} from "../functions/const.js";
import General from "./General.js";

class Presupuestos extends General {
  constructor() {
    super();
    this.table = null;
  }
  DTable(permisosUsuario = null) {
    const self = this;

    // 1. Obtener permisos
    // Si se pasan como argumento se usan, si no, se buscan en el DOM
    let permisos = permisosUsuario;
    if (!permisos) {
      const span = document.getElementById("permisosSpan");
      permisos = span ? span.textContent || "" : "";
    }

    // 2. Lógica para Fetch condicional /////////////////////
    // "dependiendo los permisos ves un tipo de fetch o otro"
    let url = "presupuestos/get";
    if (permisos.includes("ver.talleres.chevrolet")) {
      url = "presupuestos/getPresupuestosTalleresChevrolet";
    } else if (permisos.includes("ver.talleres.externos")) {
      url = "presupuestos/getPresupuestosTalleresExternos";
    }

    const obj2 = {
      columns: [
        {
          title: "N° Orden",
          render: function (data, type, row) {
            return row.siniestros && row.siniestros.numero_orden
              ? row.siniestros.numero_orden
              : "sin número de orden";
          },
        },
        {
          title: "VIN",
          render: function (data, type, row) {
            return row.siniestros && row.siniestros.vehiculo_info.vin
              ? row.siniestros.vehiculo_info.vin
              : "sin VIN";
          },
        },
        {
          title: "Folio",
          render: function (data, type, row) {
            return row.numero_presupuesto
              ? row.numero_presupuesto
              : "Sin folio";
          },
        },
        {
          title: "N° Productos",
          render: function (data, type, row) {
            return row.piezas && row.piezas.length > 0
              ? row.numero_productos
              : "0";
          },
        },
        {
          title: "Proveedor",
          render: function (data, type, row) {
            return row.siniestros && row.siniestros.vehiculo_info.taller
              ? row.siniestros.vehiculo_info.taller
              : "sin proveedor";
          },
        },
        { title: "Proveedor", data: "proveedor" },

        { title: "Registrado por", data: "id_usuario_creacion" },
        {
          title: "Registrado por",
          render: function (data, type, row) {
            return row.usuario_creacion[0].username;
          },
        },
        { title: "Fecha registro", data: "fecha_registro" },
        {
          title: "Estado",
          render: function (data, type, row) {
            const estados = {
              Cotizado: "bg-green-300 text-green-800",
              SinCotizar: "bg-yellow-300 text-yellow-800",
              Pendiente: "bg-blue-300 text-blue-800",
              Cancelado: "bg-red-300 text-red-800",
            };
            const badgetEstado = document.createElement("span");
            const clasesEstado =
              estados[row.estado] || "bg-gray-100 text-gray-800";
            badgetEstado.className = `${clasesEstado} text-sm font-normal px-1.5 py-0.5 rounded-full`;
            badgetEstado.textContent = row.estado;
            return badgetEstado;
          },
        },
        {
          title: "Opciones",
          data: null,
          render: function (data, type, row) {
            // Obtenemos el contenido del span de permisos
            // Usamos la variable 'permisos' del ámbito superior (closure)
            // let permisos = "";
            // const span = document.getElementById("permisosSpan");
            // ... (código eliminado por optimización)

            // Funciones para verificar permisos
            const puedeCotizar = permisos.includes("presupuestos.cotizar");
            const puedeAbrir = permisos.includes("presupuestos.read");
            const puedeNotificar = permisos.includes("evidencias.notify");
            const puedeCrearVale = permisos.includes("vales.write");
            const cotizarExternos = permisos.includes(
              "cotizar.presupuestos.externos"
            );
            const cotizarChevrolet = permisos.includes(
              "cotizar.presupuestos.chevrolet"
            );

            let botones = `
                     <div class="flex items-center space-x-2">
                  `;

            // Botón de abrir siniestro solo si tiene permisos de read
            if (puedeAbrir) {
              botones += `
                        <div class="relative inline-block">
                            <button type="button" onclick="window.location.href='${URLactual}/ver?folio=${data.numero_presupuesto}'" class="abrirSiniestroButton tooltip-trigger flex items-center text-white-700 hover:text-white border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-white-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-white-500 dark:text-white-500 dark:hover:text-white dark:hover:bg-white-600 dark:focus:ring-white-900" data-tooltip-content="Abrir siniestro">
                                <i class="fa-solid fa-door-open"></i>
                            </button>
                            <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
                                Abrir presupuesto
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                     `;
            }

            const esChevrolet = data.proveedor === "CHEVROLET";
            const permisoCotizar = esChevrolet ? cotizarChevrolet : cotizarExternos;

            if (
              // puedeCotizar &&
              data.estado === "SinCotizar" &&
              permisoCotizar
            ) {
              //BOTON COTIZAR
              botones += `
                        <div class="relative inline-block">
                            <button type="button" onclick="window.location.href='${URLactual}/cotizar?folio=${data.numero_presupuesto}'" class="cotizarSiniestroButton tooltip-trigger flex items-center text-yellow-700 hover:text-white border border-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-yellow-500 dark:text-yellow-500 dark:hover:text-white dark:hover:bg-yellow-600 dark:focus:ring-yellow-900" data-tooltip-content="Cotizar siniestro">
                                <i class="fa-solid fa-money-check-dollar"></i>
                            </button>
                            <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
                                Cotizar Presupuesto
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                     `;
            }

            if (
              puedeNotificar &&
              (data.estado === "SinCotizar" || data.estado === "Pendiente") &&
              data.evidencias !== null &&
              permisoCotizar === cotizarChevrolet
            ) {
              botones += `
                        <div class="relative inline-block">
                            <button type="button" data-folio="${data.numero_presupuesto}" class="subirEvidencias tooltip-trigger flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900" data-tooltip-content="Notificar evidencia">
                                 <i class="fa-solid fa-bell"></i>
                            </button>
                            <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
                                Notificar evidencia
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                     `;
            }

            // Botón de asignar vale (sin cambios, ya que depende solo del estado)
            if (puedeCrearVale && data.estado === "Cotizado") {
              botones += `
                        <div class="relative inline-block">
                            <button type="button" onclick="window.location.href='${UrlProyecto}/vales/asignar?folio=${data.numero_presupuesto}'" class="asignarValeButton tooltip-trigger flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900" data-tooltip-content="Agregar vale">
                                <i class="fa-solid fa-file-invoice"></i><i class="fa-solid fa-plus fa-2xs pl-px"></i>
                            </button>
                            <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
                                Agregar vale
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                     `;
            }

            // Botón de descargar Excel (siempre visible)
            // Ahora el botón de descarga es un enlace <a> para mejor semántica y funcionalidad.
            botones += `
                        <div class="relative inline-block">
                            <a type="button" data-id="${data.id}" href="${UrlProyecto}/presupuestos/exportPresupuesto/${data.id}?folio=${data.numero_presupuesto}&taller=${data.siniestros?.vehiculo_info?.taller || ''}" class="descargarExcelButton tooltip-trigger flex items-center text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900" data-tooltip-content="Descargar Excel">
                                <i class="fa-solid fa-file-excel"></i>
                                <i class="fa-solid fa-download p-0.5"></i>   
                            </a>
                            <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
                                Descargar Excel
                                <div class="tooltip-arrow" data-popper-arrow></div>
                            </div>
                        </div>
                  `;

            botones += `</div>`;

            return botones;
          },
        },
      ],
    };

    this.table = DT({
      idTable: "presupuestosTable",
      obj2: obj2,
      url: url,
    });
    // $('input[name="filtroEstado"]').on('change', function () {
    //    let estado = $(this).val();

    //    if (estado === 'recibido') {
    //       this.table.order([[8, 'desc']]).draw(); // columna fecha DESC
    //    } else if (estado === 'Cotizado') {
    //       this.table.order([[8, 'asc']]).draw(); // columna fecha ASC
    //    } else {
    //       this.table.order([[8, 'asc']]).draw(); // ejemplo: ordenar por ID
    //    }
    // });
    return this.table;
  }

  async agregarPresupuesto(formData) {
    try {
      const response = await fetch(`${UrlProyecto}/presupuestos`, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": token,
        },
        body: formData,
      });

      if (!response.ok) {
        throw new Error("Error en la respuesta del servidor");
      }

      const { title, message, icon } = await response.json();
      console.log(title, message, icon);
      return swalAlert({
        title,
        text: message,
        icon,
        confirmButtonText: "Aceptar",
        theme: "auto",
      });
    } catch (error) {
      console.error("Error al cargar los datos:", error);
      return swalAlert({
        title: "Error",
        text: "No se pudo conectar con el servidor. Intenta nuevamente.",
        icon: "error",
        confirmButtonText: "Aceptar",
        theme: "auto",
      });
    }
  }
}

export default Presupuestos;
