import General from "./General.js";
import { Perfil, UrlProyecto, token, swalAlert } from "../functions/const.js";

var permisosGlobal = [];
class Presupuestos extends General {

  constructor() {
    super();
    this.table = null;
    this.url = `${UrlProyecto}/${Perfil}/presupuestos`;
  }

  TD() {
    $("#presupuestosTable").DataTable({
      ajax: {
        url: `${this.url}/get`,
        data: function (d) {
          d.estado = document.querySelector('input[name="filtroEstado"]:checked')?.value;
        },
        dataSrc: function (json) {
          permisosGlobal = json.perfil;
          return json.data;
        },
      },
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
          title: "Taller",
          render: function (data, type, row) {
            return row.siniestros?.vehiculo_info?.taller ?? "sin taller";
          },
        },
        { title: "Proveedor", data: "proveedor" },
        {
          title: "Registrado por",
          render: function (data, type, row) {
            return row.usuario_creacion?.[0]?.username ?? row.id_usuario_creacion;
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
            const clasesEstado = estados[row.estado] || "bg-gray-100 text-gray-800";
            return `<span class="${clasesEstado} text-sm font-normal px-1.5 py-0.5 rounded-full">${row.estado}</span>`;
          },
        },
        {
          title: "Opciones",
          data: null,
          render: function (data, type, row) {

            // Variables para verificar permisos básicos
            const puedeCotizarDirectamente = permisosGlobal.includes("presupuestos.cotizardirectamente");
            const cotizarExternos = permisosGlobal.includes("cotizar.presupuestos.externos");
            const cotizarChevrolet = permisosGlobal.includes("cotizar.presupuestos.chevrolet");
            const puedeNotificar = permisosGlobal.includes("evidencias.notify");
            const puedeAbrir = permisosGlobal.includes("presupuestos.view") || permisosGlobal.includes("presupuestos.read");
            const puedeCrearVale = permisosGlobal.includes("vales.write");
            const puedeCotizarGeneral = permisosGlobal.includes("presupuestos.cotizar");

            let botones = `
                           <div class="flex items-center space-x-2">
                        `;

            // Botón de abrir siniestro solo si tiene permisos de read/view
            if (puedeAbrir) {
              botones += `
                              <div class="relative inline-block">
                                  <button type="button" onclick="window.open('${UrlProyecto}/${Perfil}/presupuestos/ver?folio=${data.numero_presupuesto}', '_blank')" class="abrirSiniestroButton tooltip-trigger flex items-center bg-gray-600 text-white hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-gray-500 dark:hover:bg-gray-600 dark:focus:ring-gray-700 transition-transform duration-200 hover:scale-105" data-tooltip-content="Abrir siniestro">
                                      <i class="fa-solid fa-door-open"></i>
                                  </button>
                                  <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
                                      Abrir presupuesto
                                      <div class="tooltip-arrow" data-popper-arrow></div>
                                  </div>
                              </div>
                           `;
            }

            // Validamos si puede cotizar el tipo específico del proveedor, o si tiene permiso libre general

            // const permisoCotizarSegunTaller = esChevrolet ? cotizarChevrolet : cotizarExternos;

            const esChevrolet = data.proveedor === "CHEVROLET";
            console.log(data.proveedor);
            console.log(data.numero_presupuesto);
            console.log(esChevrolet);
            console.log("directamente", puedeCotizarDirectamente);
            console.log("general", puedeCotizarGeneral);
            console.log(Perfil);
            console.log("---------------------------------");


            const permisoParaMostrarBoton = (!esChevrolet && puedeCotizarGeneral) || (esChevrolet && puedeCotizarGeneral && Perfil === "refacciones");

            if (data.estado === "SinCotizar" && permisoParaMostrarBoton) {
              //BOTON COTIZAR
              botones += `
                              <div class="relative inline-block">
                                  <button type="button" onclick="window.open('${UrlProyecto}/${Perfil}/presupuestos/cotizar?folio=${data.numero_presupuesto}', '_blank')" class="cotizarSiniestroButton tooltip-trigger flex items-center bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800 transition-transform duration-200 hover:scale-105" data-tooltip-content="Cotizar siniestro">
                                      <i class="fa-solid fa-money-check-dollar"></i>
                                  </button>
                                  <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
                                      Cotizar Presupuesto
                                      <div class="tooltip-arrow" data-popper-arrow></div>
                                  </div>
                              </div>
                           `;
            }

            // Botón para notificar evidencia
            if (puedeNotificar && (data.estado === "SinCotizar" || data.estado === "Pendiente")) {
              botones += `
                              <div class="relative inline-block">
                                  <button type="button" data-folio="${data.numero_presupuesto}" class="subirEvidencias tooltip-trigger flex items-center bg-blue-500 text-white hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-transform duration-200 hover:scale-105" data-tooltip-content="Notificar evidencia">
                                       <i class="fa-solid fa-bell"></i>
                                  </button>
                                  <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
                                      Notificar evidencia
                                      <div class="tooltip-arrow" data-popper-arrow></div>
                                  </div>
                              </div>
                           `;
            }

            // Botón de asignar vale (sin cambios, ya que depende solo del estado y permisos)
            if ((puedeCrearVale && data.estado === "Cotizado") || (puedeCotizarDirectamente && !["AUTOCAR PENSIONES", "AUTOCAR PERIFERICO"].includes(data.siniestros.vehiculo_info.taller))) {
              botones += `
                              <div class="relative inline-block">
                                  <button type="button" onclick="window.open('${UrlProyecto}/${Perfil}/vales/asignar?folio=${data.numero_presupuesto}', '_blank')" class="asignarValeButton tooltip-trigger flex items-center bg-blue-500 text-white hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-transform duration-200 hover:scale-105" data-tooltip-content="Agregar vale">
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
                                  <a type="button" data-id="${data.id}" href="${UrlProyecto}/${Perfil}/presupuestos/exportPresupuesto/${data.id}?folio=${data.numero_presupuesto}&taller=${data.siniestros?.vehiculo_info?.taller || ''}" class="descargarExcelButton tooltip-trigger flex items-center bg-green-500 text-white hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 transition-transform duration-200 hover:scale-105" data-tooltip-content="Descargar Excel">
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
    });


  }

  /*Columnas al momento de crear presupuestos*/
  agregarColumna = async (accion) => {
    const puedeCotizarDirectamente = document.getElementById("cotizarDirectamente") !== null;

    const tabla = accion === "cotizar" ? "cotizarPresupuestoTable" : "agregarPresupuestoTable";
    const tbody = document.getElementById(tabla).querySelector("tbody");
    const contador = tbody.rows.length + 1;
    const nuevaFila = tbody.insertRow();

    document.getElementById("eliminarColumnaPresupuesto")?.classList.remove("opacity-0");

    const esCrearDirecto = accion === "crear" && puedeCotizarDirectamente;
    const inptCls = "bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500";
    const numBadge = `<span class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">${contador}</span>`;

    // Helper para generar inputs
    const crearInput = (id, placeholder, extraParams = "", tipo = "text", customClass = "") =>
      `<input type="${tipo}" name="${id}" id="${id}" placeholder="${placeholder}" ${extraParams} class="${inptCls} ${customClass} ${extraParams.includes('disabled') ? 'opacity-50' : ''}">`;

    if (accion === "crear") {
      if (puedeCotizarDirectamente) {
        nuevaFila.innerHTML = `
        <td class="px-4 py-1">${numBadge}</td>
        <td class="px-2 py-1">${crearInput(`numero_parte_${contador}`, "Número de parte", `required data-conteo="${contador}"`, "text", "numero-parte")}</td>
        <td class="px-2 py-1">
          ${crearInput(`descripcion_${contador}`, "Descripción de parte", `required data-conteo="${contador}"`)}
          <input type="hidden" name="descripcion_w32_${contador}" id="descripcion_w32_${contador}" data-conteo="${contador}">
        </td>
        <td class="px-2 py-1">${crearInput(`cantidad_${contador}`, "0", `required data-conteo="${contador}"`, "number", "cantidad")}</td>
        <td class="px-2 py-1">${crearInput(`precio_unitario_${contador}`, "0.00", `required step="0.01" data-conteo="${contador}"`, "number")}</td>
        <td class="px-2 py-1">${crearInput(`importe_total_${contador}`, "0.00", `required step="0.01" data-conteo="${contador}"`, "number")}</td>
        <td class="px-2 py-1">${crearInput(`existencia_${contador}`, "0", `required data-conteo="${contador}"`)}</td>
        <td class="px-2 py-1">
          <select name="tiempoentrega_${contador}" id="tiempoentrega_${contador}" data-conteo="${contador}" disabled class="${inptCls} opacity-50">
            <option value="1a3dias">1 a 3 dias</option><option value="4a10dias">4 a 10 dias</option><option value="BackOrder">Back Order</option>
          </select>
        </td>`;
      } else {
        nuevaFila.innerHTML = `
        <td class="px-4 py-1">${numBadge}</td>
        <td class="px-2 py-1">${crearInput("numero_parte", "Número de parte", "disabled")}</td>
        <td class="px-2 py-1">${crearInput("descripcion", "Descripción de parte", "required")}</td>
        <td class="px-2 py-1">${crearInput("cantidad", "0", "required")}</td>
        <td class="px-2 py-1">${crearInput("precio_unitario", "$0.00", "disabled")}</td>
        <td class="px-2 py-1">${crearInput("importe_total", "$0.00", "disabled")}</td>
        <td class="px-2 py-1">${crearInput("existencia", "0", "disabled")}</td>
        <td class="px-2 py-1">
          <select name="tiempoentrega" id="tiempoentrega" disabled class="${inptCls} opacity-50">
            <option value="1a3dias">1 a 3 dias</option><option value="4a10dias">4 a 10 dias</option><option value="BackOrder">Back Order</option>
          </select>
        </td>`;
      }
    } else {
      // Si no es "crear", es presumiblemente "cotizar" — fila NUEVA sin ID
      nuevaFila.dataset.esNueva = "1";
      nuevaFila.innerHTML = `
      <td class="px-4 py-1">${numBadge}</td>
      <td class="hidden px-2 py-1"><input type="hidden" name="pieza_id_${contador}"></td>
      <td class="px-2 py-1">${crearInput(`numero_parte_${contador}`, "Número de parte", `data-conteo="${contador}"`, "text", "numero-parte")}</td>
      <td class="px-2 py-1">${crearInput(`descripcion_${contador}`, "Descripción de parte")}</td>
      <td class="hidden px-2 py-1">${crearInput(`descripcion_w32_${contador}`, "Descripción de parte")}</td>
      <td class="px-2 py-1">${crearInput(`cantidad_${contador}`, "0", `data-conteo="${contador}"`, "number", "cantidad")}</td>
      <td class="px-2 py-1">${crearInput(`precio_unitario_${contador}`, "0.00", `step="0.01" data-conteo="${contador}"`, "number", "precio-unitario")}</td>
      <td class="px-2 py-1">${crearInput(`importe_total_${contador}`, "0.00", `step="0.01"`, "number")}</td>
      <td class="px-2 py-1">${crearInput(`existencia_${contador}`, "0", "disabled", "number")}</td>
      <td class="px-2 py-1">
        <select name="tiempoentrega_${contador}" id="tiempoentrega_${contador}" disabled class="${inptCls} opacity-50">
          <option value="1a3dias">1 a 3 dias</option><option value="4a10dias">4 a 10 dias</option><option value="BackOrder">Back Order</option>
        </select>
      </td>`;
    }
  };

  eliminarColumna = async (accion) => {
    const tabla = accion === "cotizar" ? "cotizarPresupuestoTable" : "agregarPresupuestoTable";
    const tbody = document.getElementById(tabla).getElementsByTagName("tbody")[0];

    if (accion === "cotizar") {
      // Solo eliminar la última fila si es NUEVA (data-es-nueva="1" y sin pieza_id con valor)
      const ultimaFila = tbody.rows[tbody.rows.length - 1];
      if (!ultimaFila) return;

      const esNueva = ultimaFila.dataset.esNueva === "1";
      const idInput = ultimaFila.querySelector("input[name^='pieza_id_']");
      const tieneId = idInput && idInput.value;

      if (!esNueva && tieneId) {
        // Es una pieza existente de BD, no se puede eliminar
        Swal.fire({
          title: "No permitido",
          text: "No puedes eliminar piezas ya registradas en el presupuesto.",
          icon: "warning",
          confirmButtonText: "Entendido",
          theme: "auto",
        });
        return;
      }

      if (tbody.rows.length > 1) {
        tbody.deleteRow(tbody.rows.length - 1);
      }
    } else {
      // Modo crear: eliminar normalmente
      if (tbody.rows.length > 1) {
        tbody.deleteRow(tbody.rows.length - 1);
      }
    }

    // Ocultar botón si queda solo 1 fila
    if (tbody.rows.length <= 1) {
      const btnEliminar = document.getElementById("eliminarColumnaPresupuesto");
      if (btnEliminar) {
        btnEliminar.classList.add("opacity-0");
      }
    }
  };

  //si necesito buscar un presupuesto y es de refacciones
  isRefacciones = async (selectTaller, numeroOrden) => {

    let tallerParaBusqueda = selectTaller.value;
    console.log(tallerParaBusqueda);


    // Si el taller es REFACCIONES, primero obtener el taller real del siniestro
    if (tallerParaBusqueda.toUpperCase() === 'REFACCIONES') {
      try {
        const responseTaller = await fetch(`${UrlProyecto}/${Perfil}/siniestros/numero-orden/${encodeURIComponent(numeroOrden)}`);

        if (!responseTaller.ok) {
          throw new Error(`Error en responseTaller: ${responseTaller.status} ${responseTaller.statusText}`);
        }

        const siniestroData = await responseTaller.json();
        // Obtener el taller real del siniestro
        tallerParaBusqueda = siniestroData.vehiculo_info?.taller || tallerParaBusqueda;
      } catch (error) {
        console.warn('No se pudo obtener el taller real, usando REFACCIONES:', error);
      }
    }

    return tallerParaBusqueda;
  }

  agregarPresupuesto = async (formData) => {
    try {
      const response = await fetch(`${UrlProyecto}/${Perfil}/presupuestos`, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": token,
        },
        body: formData,
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "Error en la respuesta del servidor");
      }
      console.log(data);

      let urlExcel = null;
      const formTaller = formData.get("taller") || "";
      const proveedor = formData.get("proveedor") || data.proveedor || "";
      const isChevrolet = String(proveedor).toUpperCase() === "CHEVROLET";

      if (!isChevrolet && data.presupuesto) {
        urlExcel = `${UrlProyecto}/${Perfil}/presupuestos/exportPresupuesto/${data.presupuesto.id}?folio=${data.presupuesto.numero_presupuesto}&taller=${formTaller}`;
      }

      if (urlExcel) {
        return swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          showCancelButton: false,
          showDenyButton: true,
          confirmButtonText: "Aceptar",
          denyButtonText: `<i class="fa-solid fa-file-excel mr-1"></i> Descargar Excel`,
          denyButtonColor: '#10B981',
          theme: "auto",
        }).then((res) => {
          if (res.isDenied) {
            window.open(urlExcel, '_blank');
          }
          return res;
        });
      }

      return swalAlert(data.title, data.message, data.icon, {
        showCancelButton: false,
        confirmButtonText: "Aceptar"
      });
    } catch (error) {
      console.error("Error al cargar los datos:", error);
      return swalAlert("Error", error.message || "No se pudo conectar con el servidor. Intenta nuevamente.", "error", {
        showCancelButton: false,
        confirmButtonText: "Aceptar"
      });
    }
  }

  calcularTotalPorFila = (indice) => {
    const selectorBase = `input[name$="_${indice}"]`; // Selecciona inputs que terminen con el índice
    const formatValue = (selector) => parseFloat(document.querySelector(selector)?.value) || 0;

    const precioUnitario = formatValue(`input[name="precio_unitario_${indice}"]`);
    const cantidad = formatValue(`input[name="cantidad_${indice}"]`);
    const importeTotalInput = document.querySelector(`input[name="importe_total_${indice}"]`);

    if (importeTotalInput) {
      importeTotalInput.value = (precioUnitario * cantidad).toFixed(2);
      this.calcularTotalCotizacion(); // Llama a la función de la misma clase
    }
  };

  calcularTotalCotizacion = () => {
    // Determinar qué tabla iterar basada en la que exista en el DOM
    const tableId = document.getElementById("agregarPresupuestoTable")
      ? "#agregarPresupuestoTable"
      : "#cotizarPresupuestoTable";

    // Obtener todos los inputs de importe total de la tabla seleccionada en un solo NodeList
    const inputsImporteTotal = document.querySelectorAll(`${tableId} tbody input[name^="importe_total_"]`);

    // Sumar usando reduce (convirtiendo NodeList a Array)
    const subtotal = Array.from(inputsImporteTotal).reduce((acc, input) => {
      return acc + (parseFloat(input.value) || 0);
    }, 0);

    const iva = subtotal * 0.16;
    const total = subtotal + iva;

    // Actualizar los inputs fijos (fuera de la tabla)
    const setValue = (name, val) => {
      const el = document.querySelector(`input[name="${name}"]`);
      if (el) el.value = val.toFixed(2);
    };

    setValue('subtotal', subtotal);
    setValue('iva', iva);
    setValue('total', total);
  };

  consultarDatosByProveedor = (proveedor) => {
    const inputsPrecio = document.querySelectorAll(
      'input[name^="precio_unitario"]',
    );
    const inputsImporteTotal = document.querySelectorAll(
      'input[name^="importe_total"]',
    );
    const inputsExistencia = document.querySelectorAll(
      'input[name^="existencia"]',
    );

    const valor = proveedor.value ? proveedor.value.trim().toUpperCase() : "";

    // Solo liberamos si hay un valor seleccionado Y no es Chevrolet
    const esChevrolet = valor === "CHEVROLET";
    const tieneValor = valor !== "";

    if (tieneValor && !esChevrolet) {
      inputsPrecio.forEach((input) => {
        input.removeAttribute("disabled");
        input.classList.remove("opacity-50");
      });
      inputsImporteTotal.forEach((input) => {
        input.removeAttribute("disabled");
        input.classList.remove("opacity-50");
      });
      inputsExistencia.forEach((input) => {
        input.removeAttribute("disabled");
        input.classList.remove("opacity-50");
      });
    } else {
      inputsPrecio.forEach((input) => {
        input.setAttribute("disabled", "true");
        input.classList.add("opacity-50");
      });
      inputsImporteTotal.forEach((input) => {
        input.setAttribute("disabled", "true");
        input.classList.add("opacity-50");
      });
      inputsExistencia.forEach((input) => {
        input.setAttribute("disabled", "true");
        input.classList.add("opacity-50");
      });
    }
  };

  enviarCorreoCotizacion = async (numeroPresupuesto, linkPresupuesto = null) => {
    // Mostrar el toast de SweetAlert
    swalAlert({
      title: "Enviando correo de cotización realizada",
      theme: "auto",
      timer: 2000,
      timerProgressBar: true,
      didOpen: () => {
        Swal.showLoading();
      },
      willClose: () => {
        Swal.stopTimer();
      },
    });

    try {
      const response = await fetch(`${UrlProyecto}/${Perfil}/presupuestos/mail-cotizacion`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": token,
        },
        body: JSON.stringify({
          numeroPresupuesto: numeroPresupuesto,
          linkPresupuesto,
        }),
      });

      if (!response.ok) {
        throw new Error("Error en la solicitud");
      }

      const data = await response.json();
      swalAlert({
        title: data.title, // Título del objeto data
        text: data.message, // Mensaje del objeto data
        icon: data.icon, // Icono del objeto data
        theme: "auto", // Tema agregado
        timer: 3000,
        timerProgressBar: true,
      }).then(() => {
        location.href = `${UrlProyecto}/${Perfil}/presupuestos`;
      });
    } catch (error) {
      console.error("Error:", error);
      swalAlert({
        title: "Error",
        text: "No se pudo enviar el correo.",
        icon: "error",
        theme: "auto", // Tema agregado
      }).then(() => {
        location.href = `${UrlProyecto}/${Perfil}/presupuestos`;
      });
    }
  };

  fetchPresupuestoData = async (folio) => {
    const response = await fetch(`${UrlProyecto}/${Perfil}/presupuestos/${folio}`);
    if (!response.ok) {
      throw new Error("Error al obtener el presupuesto");
    }
    const result = await response.json();
    if (!result || result.length === 0) {
      throw new Error("No se encontró el presupuesto");
    }
    return result[0];
  };

  getPermisos = () => {
    const span = document.getElementById("permisosSpan");
    return span ? span.textContent || "" : "";
  };

  renderPiezasTable = (piezas, accion, permisos) => {
    const tabla = accion === "ver" ? "#verPresupuestoTable tbody" : "#cotizarPresupuestoTable tbody";
    const tbody = document.querySelector(tabla);

    console.log(piezas);

    if (!tbody) return;

    tbody.innerHTML = "";
    const puedeVerDescripcionW32 = permisos.includes("descripcionw32.read");

    piezas.forEach((pieza, index) => {
      const nuevaFila = tbody.insertRow();
      const contador = index + 1;

      if (accion === "ver") {
        nuevaFila.innerHTML = `
          <td class="px-4 py-1">
             <button class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">${contador}</button>
          </td>
          <td class="px-2 py-1">
             <input type="text" name="numero_parte" value="${pieza.numero_parte ?? ""}" readonly placeholder="Número de parte"
             class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
          </td>
          <td class="px-2 py-1">
             <input type="text" name="descripcion" value="${pieza.descripcion ?? ""}" readonly placeholder="Descripción de parte"
             class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
          </td>
          ${puedeVerDescripcionW32 ? `
          <td class="px-2 py-1">
             <input type="text" name="descripcion_w32" value="${pieza.descripcion_w32 ?? ""}" readonly placeholder="Descripción de parte"
             class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
          </td>` : ""}
          <td class="px-2 py-1">
             <input type="number" name="cantidad" value="${pieza.numero_pzas_presupuesto ?? 0}" data-conteo="${contador}" readonly placeholder="0"
             class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
          </td>
          <td class="px-2 py-1">
             <input type="number" name="precio_unitario" value="${pieza.importe_unitario ?? 0}" readonly placeholder="$0.00"
             class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
          </td>
          <td class="px-2 py-1">
             <input type="number" name="importe_total" value="${pieza.importe_total ?? 0}" readonly placeholder="$0.00"
             class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
          </td>
          <td class="px-2 py-1">
             <input type="number" name="existencia" value="${pieza.existencia ?? 0}" readonly placeholder="0"
             class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
          </td>
          <td class="px-2 py-1">
             <input type="text" name="tiempoentrega_${contador}" value="${pieza.tiempoentrega ?? "1a3dias"}" readonly placeholder="Tiempo de entrega"
             class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
          </td>
        `;
      } else {
        const puedeActualizar = document.getElementById("canUpdatePresupuesto")?.value === "true";
        const btnEditarDesc = puedeActualizar ? `
          <button type="button" data-pieza-id="${pieza.id}" data-conteo="${contador}"
            class="btn-editar-descripcion text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 p-1" title="Editar descripción">
            <i class="fa-solid fa-pen-to-square text-xs"></i>
          </button>` : "";

        nuevaFila.innerHTML = `
          <td class="px-4 py-1">
             <button class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">${contador}</button>
          </td>
          <td class="hidden">
             <input type="hidden" name="pieza_id_${contador}" value="${pieza.id ?? ""}">
          </td>
          <td class="px-2 py-1">
             <input type="text" name="numero_parte_${contador}" value="${pieza.numero_parte ?? ""}" data-conteo="${contador}" placeholder="Número de parte"
             class="numero-parte bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          </td>
          <td class="px-2 py-1">
             <div class="flex items-center">
               <input type="text" name="descripcion_${contador}" value="${pieza.descripcion ?? ""}" placeholder="Descripción de parte" disabled
               class="descripcion-pieza block border-gray-800 dark:bg-gray-800 dark:focus:ring-primary-500 dark:placeholder-gray-400 dark:text-white m-2 rounded-lg text-sm text-white w-full">
               ${btnEditarDesc}
             </div>
          </td>
          ${puedeVerDescripcionW32 ? `
          <td class="hidden px-2 py-1">
             <input type="text" name="descripcion_w32_${contador}" value="${pieza.descripcion_w32 ?? ""}" placeholder="Descripción de parte"
             class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          </td>` : ""}
          <td class="px-2 py-1">
             <input type="text" name="cantidad_${contador}" data-conteo="${contador}" value="${pieza.numero_pzas_presupuesto ?? 0}" placeholder="0"
             class="cantidad bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          </td>
          <td class="px-2 py-1">
             <input type="number" step="0.01" name="precio_unitario_${contador}" data-conteo="${contador}" value="" placeholder="0.00"
             class="precio-unitario bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" disabled>
          </td>
          <td class="px-2 py-1">
             <input type="number" step="0.01" name="importe_total_${contador}" value="" placeholder="0.00"
             class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" disabled>
          </td>
          <td class="px-2 py-1">
             <input type="text" name="existencia_${contador}" value="${pieza.existencia ?? 0}" placeholder="0"
             class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" disabled>
          </td>
          <td class="px-2 py-1">
             <select name="tiempoentrega_${contador}" data-conteo="${contador}" id="tiempoentrega_${contador}" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                <option value="1a3dias">1 a 3 dias</option>
                <option value="4a10dias">4 a 10 dias</option>
                <option value="BackOrder">Back Order </option>
             </select>
          </td>
        `;
      }
    });
  };

  populateSiniestroInputs = (presupuestoData) => {
    const {
      id, proveedor, estado, fecha_cotizado,
      siniestros
    } = presupuestoData;

    const {
      numero_orden, numero_siniestro,
      vehiculo_info,
      cliente
    } = siniestros || {};

    const { aseguradora, marca, modelo, taller, vehiculo, vin } = vehiculo_info || {};
    const { id: id_cliente, nombre: nombre_cliente, codigo } = cliente || {};

    const formatDate = (date) => {
      if (!date) return "";
      const d = new Date(date);
      return `${d.getFullYear()}-${(d.getMonth() + 1).toString().padStart(2, "0")}-${d.getDate().toString().padStart(2, "0")}`;
    };

    console.log("aseguradora", aseguradora, "cliente;", nombre_cliente);

    const inputs = {
      id_siniestro: id,
      numero_orden,
      numero_siniestro,
      aseguradora,
      vin,
      vehiculo,
      marca,
      modelo,
      id_cliente,
      nombre_cliente,
      codigo,
      taller,
      proveedor,
      estado,
      fecha_cotizado: formatDate(fecha_cotizado),
    };

    const labelSpan = document.querySelector("#label_cliente_aseguradora");
    if (labelSpan) {
      if (nombre_cliente) {
        labelSpan.textContent = "Cliente";
      } else if (aseguradora) {
        labelSpan.textContent = "Aseguradora";
        inputs.nombre_cliente = aseguradora;
      } else {
        labelSpan.textContent = "Cliente";
      }
    }

    Object.entries(inputs).forEach(([key, val]) => {
      const inputElement = document.querySelector(`#${key}`);
      if (inputElement) {
        inputElement.value = val || "";
        inputElement.setAttribute("readonly", true);
      } else {
        console.warn(`Input con id #${key} no encontrado`);
      }
    });

    const proveedorSelect = document.querySelector("#proveedor");
    if (proveedorSelect) {
      this.consultarDatosByProveedor(proveedorSelect);
      const codigoClienteContainer = document.getElementById("codigoClienteContainer");
      if (codigoClienteContainer) {
        if (proveedorSelect.value.toUpperCase() !== "CHEVROLET") {
          codigoClienteContainer.classList.add("hidden");
          codigoClienteContainer.classList.remove("flex");
        } else {
          codigoClienteContainer.classList.add("flex");
          codigoClienteContainer.classList.remove("hidden");
        }
      }
    }
  };

  obtenerCodigoCliente = () => {
    const proveedorSelect = document.querySelector("#proveedor");
    if (proveedorSelect && proveedorSelect.value.toUpperCase() !== "CHEVROLET") {
      return "";
    }

    const taller = document.getElementById("taller")?.value || "";
    let codigo = "";

    if (Perfil === "refacciones" && !["AUTOCAR PENSIONES", "AUTOCAR PERIFERICO"].includes(taller)) {
      codigo = document.getElementById("codigo")?.value || "";
    } else {
      codigo = document.getElementById("codigoAutocar")?.value || "";
    }

    if (codigo === "") {
      swalAlert({
        title: "Atención",
        text: "Necesitas colocar el cliente",
        icon: "warning",
        theme: "auto",
        didClose: () => {
          document.getElementById("codigoAutocar")?.focus();
        }
      });
      return null;
    }

    return codigo;
  };

  consultarDatosByNumeroParte = async (numeroParte, indice, codigo) => {
    const numParteLimpio = numeroParte ? String(numeroParte).trim() : '';

    const dataW32 = await fetch(`${UrlProyecto}/${Perfil}/presupuestos/consultar-descripcion?numeroParte=${encodeURIComponent(numParteLimpio)}&codigo=${encodeURIComponent(codigo)}`,
      {
        method: "GET",
      },
    ).then(async (response) => {
      if (!response.ok) {
        throw new Error("Error en la respuesta del servidor");
      }
      return await response.json();
    });

    const importeUnitarioInput = document.querySelector(`input[name="precio_unitario_${indice}"]`);
    const descripcionW32Input = document.querySelector(`input[name="descripcion_w32_${indice}"]`);
    const existenciaInput = document.querySelector(`input[name="existencia_${indice}"]`);

    if (dataW32 && Object.keys(dataW32).length > 0) {
      // Determinar precio según estado del checkbox PVP
      const checkPVP = document.getElementById('checkPVP');
      const usarPVP = checkPVP && checkPVP.checked && dataW32.TIENE_PVP;
      const precioFinal = usarPVP ? dataW32.IMPORTE_PVP : dataW32.IMPORTE;

      if (importeUnitarioInput) {
        importeUnitarioInput.value = precioFinal || 0;
        // Guardar ambos precios en data-attributes para alternar sin re-fetch
        importeUnitarioInput.dataset.importeNormal = dataW32.IMPORTE || 0;
        importeUnitarioInput.dataset.importePvp = dataW32.IMPORTE_PVP || 0;
        importeUnitarioInput.dataset.tienePvp = dataW32.TIENE_PVP ? '1' : '0';
      }
      if (descripcionW32Input) descripcionW32Input.value = dataW32.DESCRIPTION || "";
      if (existenciaInput) existenciaInput.value = dataW32.STOCK || 0;

      // Mostrar/ocultar checkbox PVP si al menos una pieza tiene doble descuento
      this._actualizarVisibilidadCheckPVP();
    } else {
      if (importeUnitarioInput) {
        importeUnitarioInput.value = 0;
        importeUnitarioInput.dataset.importeNormal = '0';
        importeUnitarioInput.dataset.importePvp = '0';
        importeUnitarioInput.dataset.tienePvp = '0';
      }
      if (descripcionW32Input) descripcionW32Input.value = "";
      if (existenciaInput) existenciaInput.value = 0;
    }

    // Llamar a calcularTotalPorFila después de actualizar los valores
    this.calcularTotalPorFila(indice);
  };

  // Muestra el checkbox PVP si al menos un input tiene tienePvp='1'
  _actualizarVisibilidadCheckPVP = () => {
    const container = document.getElementById('checkPVPContainer');
    if (!container) return;

    const inputs = document.querySelectorAll('input[name^="precio_unitario_"]');
    const alguno = Array.from(inputs).some(inp => inp.dataset.tienePvp === '1');

    if (alguno) {
      container.classList.remove('hidden');
      container.classList.add('flex');
    } else {
      container.classList.add('hidden');
      container.classList.remove('flex');
      const check = document.getElementById('checkPVP');
      if (check) check.checked = false;
    }
  };

  // Alterna todos los precios entre normal y PVP
  alternarPreciosPVP = (usarPVP) => {
    const inputs = document.querySelectorAll('input[name^="precio_unitario_"]');
    inputs.forEach((inp, i) => {
      if (inp.dataset.tienePvp === '1') {
        inp.value = usarPVP ? (inp.dataset.importePvp || 0) : (inp.dataset.importeNormal || 0);
      }
      // Recalcular total de esta fila
      const indice = i + 1;
      this.calcularTotalPorFila(indice);
    });
  };

  extraerDatosCotizacion = (rows) => {
    const faltantes = [];
    const piezas = [];

    for (let index = 0; index < rows.length; index++) {
      const row = rows[index];
      const nth = index + 1;

      // Función auxiliar para obtener el valor del input por su sufijo de nombre
      const getValue = (name) => row.querySelector(`input[name="${name}_${nth}"]`)?.value || null;

      const id = getValue("pieza_id");
      const numeroParte = getValue("numero_parte");
      const descripcion = getValue("descripcion");
      const descripcionW32 = getValue("descripcion_w32");
      const cantidad = getValue("cantidad");
      const precioUnitario = getValue("precio_unitario");
      const total = getValue("importe_total");
      const existencia = getValue("existencia");

      const selectTiempoEntrega = row.querySelector(`select[name="tiempoentrega_${nth}"]`);
      const tiempoEntrega = selectTiempoEntrega ? selectTiempoEntrega.value : null;

      if (
        numeroParte ||
        parseFloat(cantidad) > 0 ||
        parseFloat(precioUnitario) > 0 ||
        parseFloat(total) > 0 ||
        existencia
      ) {
        piezas.push({
          id,
          numero_parte: numeroParte,
          descripcion,
          descripcion_w32: descripcionW32,
          cantidad,
          precio_unitario: precioUnitario,
          total,
          existencia,
          tiempo_entrega: tiempoEntrega,
        });
      }

      if (!numeroParte) {
        faltantes.push(descripcion);
      }
    }

    return { piezas, faltantes };
  };

  enviarCotizacionAlServidor = async (formData, folioCotizacion) => {
    // Primero, vamos a ubicar las filas que no tienen 'pieza_id_' (son adiciones nuevas)
    // El frontend POST-ea esas nuevas a la API y luego hace submit
    const piezasNuevas = [];
    const rows = document.querySelectorAll("#cotizarPresupuestoTable tbody tr");

    rows.forEach((row, index) => {
      const nth = index + 1;
      const idInput = row.querySelector(`input[name="pieza_id_${nth}"]`);

      // Si no tiene input de ID oculto con valor (o no existe), es nueva
      if (!idInput || !idInput.value) {
        const getValue = (name) => row.querySelector(`input[name="${name}_${nth}"]`)?.value || null;

        const numeroParte = getValue("numero_parte");
        const descripcion = getValue("descripcion");
        const descripcionW32 = getValue("descripcion_w32");
        const cantidad = getValue("cantidad");
        const precioUnitario = getValue("precio-unitario");
        const total = getValue("importe_total");
        const existencia = getValue("existencia");
        const selectTiempoEntrega = row.querySelector(`select[name="tiempoentrega_${nth}"]`);
        const tiempoEntrega = selectTiempoEntrega ? selectTiempoEntrega.value : "1a3dias";

        if (numeroParte || descripcion || parseInt(cantidad) > 0) {
          piezasNuevas.push({
            numero_parte: numeroParte,
            descripcion: descripcion,
            descripcion_w32: descripcionW32,
            cantidad: cantidad,
            precio_unitario: precioUnitario,
            importe_total: total,
            existencia: existencia,
            tiempoentrega: tiempoEntrega
          });
        }
      }
    });

    // Guardar las piezas nuevas
    if (piezasNuevas.length > 0) {
      for (const p of piezasNuevas) {
        const formDataNueva = new FormData();
        Object.keys(p).forEach(k => {
          if (p[k]) formDataNueva.append(k, p[k]);
        });

        try {
          const resp = await fetch(`${UrlProyecto}/${Perfil}/presupuestos/${folioCotizacion}/agregar-pieza-cotizacion`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            },
            body: formDataNueva
          });
          const jsonResp = await resp.json();
          if (jsonResp.success) {
            // Ya se agregó a la BD, ahora extraigamos los datos actualizados a Form Data normal?
            // De hecho, como enviarCotizacionAlServidor sobreescribe piezas, pero el form ya las lleva.
            // Para simplificar, insertamos su id al formData json si es necesario,
            // pero el submit final del controller no lo borrará porque ya no envia arrays que reemplazan todo,
            // simplemente hace update() de IDs que vienen. Las nuevas no vendrán con ID en formData,
            // así que no se actualizarán de nuevo, lo cual está DPM (De Puta Madre - Excelente) ✅
          }
        } catch (e) {
          console.error("No se pudo preguardar pieza nueva", e);
        }
      }
    }

    const response = await fetch(
      `${UrlProyecto}/${Perfil}/presupuestos/cotizar/${folioCotizacion}`,
      {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": token,
        },
        body: formData,
      }
    );

    if (!response.ok) {
      throw new Error("Error en la respuesta del servidor");
    }

    return await response.json();
  };
}




















export default Presupuestos;

// import {
//   UrlProyecto,
//   URLactual,
//   showLoading,
//   hideLoading,
//   DT,
//   token,
// } from "../functions/const.js";





//   DTable(permisosUsuario = null) {
//     const self = this;

//     // 1. Obtener permisos
//     // Si se pasan como argumento se usan, si no, se buscan en el DOM
//     let permisos = permisosUsuario;
//     if (!permisos) {
//       const span = document.getElementById("permisosSpan");
//       permisos = span ? span.textContent || "" : "";
//     }

//     // 2. Lógica para Fetch condicional
//     // "dependiendo los permisos ves un tipo de fetch o otro"
//     let url = "presupuestos/get";
//     if (permisos.includes("ver.talleres.chevrolet")) {
//       url = "presupuestos/getPresupuestosTalleresChevrolet";
//     } else if (permisos.includes("ver.talleres.externos")) {
//       url = "presupuestos/getPresupuestosTalleresExternos";
//     }

//     const obj2 = {
//       columns: [
//         {
//           title: "N° Orden",
//           render: function (data, type, row) {
//             return row.siniestros && row.siniestros.numero_orden
//               ? row.siniestros.numero_orden
//               : "sin número de orden";
//           },
//         },
//         {
//           title: "VIN",
//           render: function (data, type, row) {
//             return row.siniestros && row.siniestros.vehiculo_info.vin
//               ? row.siniestros.vehiculo_info.vin
//               : "sin VIN";
//           },
//         },
//         {
//           title: "Folio",
//           render: function (data, type, row) {
//             return row.numero_presupuesto
//               ? row.numero_presupuesto
//               : "Sin folio";
//           },
//         },
//         {
//           title: "N° Productos",
//           render: function (data, type, row) {
//             return row.piezas && row.piezas.length > 0
//               ? row.numero_productos
//               : "0";
//           },
//         },
//         {
//           title: "Proveedor",
//           render: function (data, type, row) {
//             return row.siniestros && row.siniestros.vehiculo_info.taller
//               ? row.siniestros.vehiculo_info.taller
//               : "sin proveedor";
//           },
//         },
//         { title: "Proveedor", data: "proveedor" },

//         { title: "Registrado por", data: "id_usuario_creacion" },
//         {
//           title: "Registrado por",
//           render: function (data, type, row) {
//             return row.usuario_creacion[0].username;
//           },
//         },
//         { title: "Fecha registro", data: "fecha_registro" },
//         {
//           title: "Estado",
//           render: function (data, type, row) {
//             const estados = {
//               Cotizado: "bg-green-300 text-green-800",
//               SinCotizar: "bg-yellow-300 text-yellow-800",
//               Pendiente: "bg-blue-300 text-blue-800",
//               Cancelado: "bg-red-300 text-red-800",
//             };
//             const badgetEstado = document.createElement("span");
//             const clasesEstado =
//               estados[row.estado] || "bg-gray-100 text-gray-800";
//             badgetEstado.className = `${clasesEstado} text-sm font-normal px-1.5 py-0.5 rounded-full`;
//             badgetEstado.textContent = row.estado;
//             return badgetEstado;
//           },
//         },
//         {
//           title: "Opciones",
//           data: null,
//           render: function (data, type, row) {
//             // Obtenemos el contenido del span de permisos
//             // Usamos la variable 'permisos' del ámbito superior (closure)
//             // let permisos = "";
//             // const span = document.getElementById("permisosSpan");
//             // ... (código eliminado por optimización)

//             // Funciones para verificar permisos
//             const puedeCotizar = permisos.includes("presupuestos.cotizar");
//             const puedeAbrir = permisos.includes("presupuestos.read");
//             const puedeNotificar = permisos.includes("evidencias.notify");
//             const puedeCrearVale = permisos.includes("vales.write");
//             const cotizarExternos = permisos.includes(
//               "cotizar.presupuestos.externos"
//             );
//             const cotizarChevrolet = permisos.includes(
//               "cotizar.presupuestos.chevrolet"
//             );

//             let botones = `
//                      <div class="flex items-center space-x-2">
//                   `;

//             // Botón de abrir siniestro solo si tiene permisos de read
//             if (puedeAbrir) {
//               botones += `
//                         <div class="relative inline-block">
//                             <button type="button" onclick="window.location.href='${URLactual}/ver?folio=${data.numero_presupuesto}'" class="abrirSiniestroButton tooltip-trigger flex items-center text-white-700 hover:text-white border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-white-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-white-500 dark:text-white-500 dark:hover:text-white dark:hover:bg-white-600 dark:focus:ring-white-900" data-tooltip-content="Abrir siniestro">
//                                 <i class="fa-solid fa-door-open"></i>
//                             </button>
//                             <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
//                                 Abrir presupuesto
//                                 <div class="tooltip-arrow" data-popper-arrow></div>
//                             </div>
//                         </div>
//                      `;
//             }

//             const esChevrolet = data.proveedor === "CHEVROLET";
//             const permisoCotizar = esChevrolet ? cotizarChevrolet : cotizarExternos;

//             if (
//               // puedeCotizar &&
//               data.estado === "SinCotizar" &&
//               permisoCotizar
//             ) {
//               //BOTON COTIZAR
//               botones += `
//                         <div class="relative inline-block">
//                             <button type="button" onclick="window.location.href='${URLactual}/cotizar?folio=${data.numero_presupuesto}'" class="cotizarSiniestroButton tooltip-trigger flex items-center text-yellow-700 hover:text-white border border-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-yellow-500 dark:text-yellow-500 dark:hover:text-white dark:hover:bg-yellow-600 dark:focus:ring-yellow-900" data-tooltip-content="Cotizar siniestro">
//                                 <i class="fa-solid fa-money-check-dollar"></i>
//                             </button>
//                             <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
//                                 Cotizar Presupuesto
//                                 <div class="tooltip-arrow" data-popper-arrow></div>
//                             </div>
//                         </div>
//                      `;
//             }

//             if (
//               puedeNotificar &&
//               (data.estado === "SinCotizar" || data.estado === "Pendiente") &&
//               data.evidencias !== null &&
//               permisoCotizar === cotizarChevrolet
//             ) {
//               botones += `
//                         <div class="relative inline-block">
//                             <button type="button" data-folio="${data.numero_presupuesto}" class="subirEvidencias tooltip-trigger flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900" data-tooltip-content="Notificar evidencia">
//                                  <i class="fa-solid fa-bell"></i>
//                             </button>
//                             <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
//                                 Notificar evidencia
//                                 <div class="tooltip-arrow" data-popper-arrow></div>
//                             </div>
//                         </div>
//                      `;
//             }

//             // Botón de asignar vale (sin cambios, ya que depende solo del estado)
//             if (puedeCrearVale && data.estado === "Cotizado") {
//               botones += `
//                         <div class="relative inline-block">
//                             <button type="button" onclick="window.location.href='${UrlProyecto}/vales/asignar?folio=${data.numero_presupuesto}'" class="asignarValeButton tooltip-trigger flex items-center text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900" data-tooltip-content="Agregar vale">
//                                 <i class="fa-solid fa-file-invoice"></i><i class="fa-solid fa-plus fa-2xs pl-px"></i>
//                             </button>
//                             <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
//                                 Agregar vale
//                                 <div class="tooltip-arrow" data-popper-arrow></div>
//                             </div>
//                         </div>
//                      `;
//             }

//             // Botón de descargar Excel (siempre visible)
//             // Ahora el botón de descarga es un enlace <a> para mejor semántica y funcionalidad.
//             botones += `
//                         <div class="relative inline-block">
//                             <a type="button" data-id="${data.id}" href="${UrlProyecto}/presupuestos/exportPresupuesto/${data.id}?folio=${data.numero_presupuesto}&taller=${data.siniestros?.vehiculo_info?.taller || ''}" class="descargarExcelButton tooltip-trigger flex items-center text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900" data-tooltip-content="Descargar Excel">
//                                 <i class="fa-solid fa-file-excel"></i>
//                                 <i class="fa-solid fa-download p-0.5"></i>
//                             </a>
//                             <div class="absolute left-1/2 -translate-x-1/2 top-0 -translate-y-full mb-2 z-50 hidden px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg tooltip">
//                                 Descargar Excel
//                                 <div class="tooltip-arrow" data-popper-arrow></div>
//                             </div>
//                         </div>
//                   `;

//             botones += `</div>`;

//             return botones;
//           },
//         },
//       ],
//     };

//     this.table = DT({
//       idTable: "presupuestosTable",
//       obj2: obj2,
//       url: url,
//     });
//     // $('input[name="filtroEstado"]').on('change', function () {
//     //    let estado = $(this).val();

//     //    if (estado === 'recibido') {
//     //       this.table.order([[8, 'desc']]).draw(); // columna fecha DESC
//     //    } else if (estado === 'Cotizado') {
//     //       this.table.order([[8, 'asc']]).draw(); // columna fecha ASC
//     //    } else {
//     //       this.table.order([[8, 'asc']]).draw(); // ejemplo: ordenar por ID
//     //    }
//     // });
//     return this.table;
//   }

//   async agregarPresupuesto(formData) {
//     try {
//       const response = await fetch(`${UrlProyecto}/presupuestos`, {
//         method: "POST",
//         headers: {
//           "X-CSRF-TOKEN": token,
//         },
//         body: formData,
//       });

//       if (!response.ok) {
//         throw new Error("Error en la respuesta del servidor");
//       }

//       const { title, message, icon } = await response.json();
//       console.log(title, message, icon);
//       return swalAlert({
//         title,
//         text: message,
//         icon,
//         confirmButtonText: "Aceptar",
//         theme: "auto",
//       });
//     } catch (error) {
//       console.error("Error al cargar los datos:", error);
//       return swalAlert({
//         title: "Error",
//         text: "No se pudo conectar con el servidor. Intenta nuevamente.",
//         icon: "error",
//         confirmButtonText: "Aceptar",
//         theme: "auto",
//       });
//     }
//   }
// }

