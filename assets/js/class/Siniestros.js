import { id_perfil, UrlProyecto, token, Toast, Perfil, swalAlert, showSendingMailAlert, dataTableLanguageEs } from "../functions/const.js";
import General from "./General.js";

class Siniestro extends General {

  constructor() {
    super();
    this.url = `${UrlProyecto}/${Perfil}/siniestros`;
    this.tipo = [];
    this.tableDetalleModalPartes = null;
  }

  getTableDetalle() {
    if (!this.tableDetalleModalPartes) {
      this.tableDetalleModalPartes = document.querySelector('#tableDetalleModalPartes');
    }
    return this.tableDetalleModalPartes;
  }

  TD() {
    const AUTOCAR_TALLERES = ["AUTOCAR PENSIONES", "AUTOCAR PERIFERICO"];
    const mapAvanceVehiculo = new Map();
    const mapAutorizadas = new Map();
    let allVinCache = []; // caché combinado Abiertos + Completados

    const normalizarTexto = (value) => String(value ?? "").trim().toUpperCase();

    const getEscenarioProgreso = (row = {}) => {
      const taller = normalizarTexto(row?.vehiculo_info?.taller ?? row?.vehiculoInfo?.taller);
      const proveedor = normalizarTexto(row?.proveedor);
      const esAutocar = AUTOCAR_TALLERES.includes(taller);
      const esChevrolet = proveedor === "CHEVROLET";
      const pzsAutorizadas = Number(row?.pzs_autorizadas ?? 0);
      const pzsSurtidas = Number(row?.pzs_surtidas ?? 0);
      const pzsRecibidas = Number(row?.pzs_recibidas ?? 0);

      let piezasBaseAvance = pzsSurtidas;

      if (esAutocar && esChevrolet) {
        piezasBaseAvance = pzsRecibidas;
      } else if (esAutocar) {
        piezasBaseAvance = pzsRecibidas;
      } else if (esChevrolet) {
        piezasBaseAvance = pzsSurtidas;
      }

      return {
        pzsAutorizadas,
        pzsAvance: Math.min(piezasBaseAvance, pzsAutorizadas),
      };
    };

    const getPorcentajeAvance = (row = {}) => {
      const { pzsAutorizadas, pzsAvance } = getEscenarioProgreso(row);

      return pzsAutorizadas > 0
        ? ((pzsAvance / pzsAutorizadas) * 100).toFixed(0)
        : 0;
    };

    const construirMapas = () => {
      mapAvanceVehiculo.clear();
      mapAutorizadas.clear();
      allVinCache.forEach(f => {
        const vin = f?.vehiculo_info?.vin ?? f?.vehiculoInfo?.vin;
        if (!vin) return;

        const { pzsAvance, pzsAutorizadas } = getEscenarioProgreso(f);
        mapAvanceVehiculo.set(vin, Number(mapAvanceVehiculo.get(vin) ?? 0) + pzsAvance);
        mapAutorizadas.set(vin, Number(mapAutorizadas.get(vin) ?? 0) + pzsAutorizadas);
      });
    };

    const initDT = () => {
      $("#siniestrosTable").DataTable({
        language: dataTableLanguageEs,
        autoWidth: false,
        headerCallback: function (thead) {
          $(thead).find('th').addClass('text-[10px] sm:text-[11px] px-2 py-1 leading-none tracking-tighter uppercase');
        },
        createdRow: function (row) {
          $(row).find('td').addClass('text-[11px] px-2 py-1 leading-tight');
        },
        ajax: {
          url: `${this.url}/getSiniestros`,
          data: function (d) {
            d.estado = document.querySelector('input[name="filtroEstado"]:checked')?.value;
          },
          // dataSrc corre síncronamente en CADA recarga de AJAX (cambio de filtro incluido)
          dataSrc: function (rows) {
            // Siempre usar el caché completo (Abiertos + Completados) para el % del vehículo
            construirMapas();
            return rows; // DataTable necesita el array de vuelta
          }
        },
        columns: [
          { title: "N° Orden", data: "numero_orden" },
          { title: "VIN", data: "vehiculo_info.vin" },
          { title: "N° Sin.", data: "numero_siniestro" },
          {
            title: "Taller",
            data: function (row) {
              return row.vehiculo_info?.taller || row.vehiculoInfo?.taller || "Sin datos";
            }
          },
          {
            title: "Cliente/Aseg",
            render: function (data, type, row) {
              const aseguradora = row.vehiculo_info?.aseguradora || "";
              const cliente = row.cliente?.nombre || "";
              return cliente || aseguradora || "Sin datos";
            }
          },
          {
            title: "Tot. Pptos",
            createdCell: function (td, cellData, rowData, row, col) {
              td.classList.add("text-center");
            },
            data: function (row) {
              const detalle = row.num_presupuestos > 0 ? 'ver-detalles-partes' : '';
              return `<a href="#" class="open-modal ${detalle} text-blue-700 dark:text-green-500 dark:hover:text-blue-500 hover:underline" data-tipo="presupuesto" data-id_siniestro="${row.id}" data-numOrden="${row.numero_orden}" data-tipo-modal="total-presupuestos" data-modal-target="siniestros-modal">${row.num_presupuestos}</a>`;
            },
          },
          {
            title: "Tot. Vales",
            createdCell: function (td, cellData, rowData, row, col) {
              td.classList.add("text-center");
            },
            data: function (row) {
              return `<a class=" text-blue-700 dark:text-green-500 select-none" data-numOrden="${row.numero_orden}" data-modal-target="siniestros-modal" data-tipo="vale" data-id_siniestro="${row.id}">${row.num_vales}</a>`;
            },
          },
          {
            title: "Tot. Ent.",
            createdCell: function (td, cellData, rowData, row, col) {
              td.classList.add("text-center");
            },
            data: function (row) {
              return `<a class=" text-blue-700 dark:text-green-500 select-none" data-numOrden="${row.numero_orden}" data-modal-target="siniestros-modal data-tipo="entrada" data-id_siniestro="${row.id}"">${row.num_entradas}</a>`;
            },
          },
          {
            title: "Pzs Auth.",
            createdCell: function (td, cellData, rowData, row, col) {
              td.classList.add("text-center");
            },
            data: function (row) {
              const detalle = row.pzs_autorizadas > 0 ? 'ver-detalles-partes' : '';
              return `<a href="#" class="open-modal ${detalle} text-blue-700 dark:text-green-500 dark:hover:text-blue-500 hover:underline" data-numOrden="${row.numero_orden}" data-tipo-modal="pzs-autorizadas" data-modal-target="siniestros-modal" data-tipo="pzs-autorizadas" data-id_siniestro="${row.id}">${row.pzs_autorizadas}</a>`;
            },
          },
          {
            title: "Pzs Surt.",
            createdCell: function (td, cellData, rowData, row, col) {
              td.classList.add("text-center");
            },
            data: function (row) {
              const detalle = row.pzs_surtidas > 0 ? 'ver-detalles-partes' : '';
              return `<a href="#" class="open-modal ${detalle} text-blue-700 dark:text-green-500 dark:hover:text-blue-500 hover:underline" data-numOrden="${row.numero_orden}" data-tipo-modal="pzs-surtidas" data-modal-target="siniestros-modal" data-tipo="pzs-surtidas" data-id_siniestro="${row.id}">${row.pzs_surtidas ? row.pzs_surtidas : "Sin datos"}</a>`;
            },
          },
          {
            title: "Pzs Rec.",
            createdCell: function (td, cellData, rowData, row, col) {
              td.classList.add("text-center");
            },
            data: function (row) {
              const detalle = row.pzs_recibidas > 0 ? 'ver-detalles-partes' : '';
              return `<a href="#" class="open-modal ${detalle} text-blue-700 dark:text-green-500 dark:hover:text-blue-500 hover:underline" data-numOrden="${row.numero_orden}" data-tipo-modal="pzs-recibidas" data-modal-target="siniestros-modal" data-tipo="pzs-recibidas" data-id_siniestro="${row.id}">${row.pzs_recibidas}</a>`;
            },
          },
          {
            title: "% Rec.",
            createdCell: function (td, cellData, rowData, row, col) {
              td.classList.add("text-center");
            },
            data: function (row) {
              const porcentaje = getPorcentajeAvance(row);
              return `<a class=" text-blue-700 dark:text-green-500 select-none" data-numOrden="${row.numero_orden}%" data-modal-target="siniestros-modal">${porcentaje}%</a>`;
            },
          }, {
            title: "% Tot. Veh.",
            createdCell: function (td, cellData, rowData, row, col) {
              td.classList.add("text-center");
            },
            data: function (row) {
              const vin = row?.vehiculo_info?.vin ?? row?.vehiculoInfo?.vin;
              const totalAvance = Number(mapAvanceVehiculo.get(vin) ?? 0);
              const totalAutorizadas = Number(mapAutorizadas.get(vin) ?? 0);
              const pct = totalAutorizadas > 0
                ? ((totalAvance / totalAutorizadas) * 100).toFixed(0)
                : 0;
              return `<a class=" text-blue-700 dark:text-green-500 select-none" data-numOrden="${row.numero_orden}" data-modal-target="siniestros-modal">${pct}%</a>`;
            },
          }, {
            title: "Pzs Falt.",
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
              const detalle = row.pzs_faltantes > 0 ? 'ver-detalles-partes' : '';
              return `<a href="#" class="open-modal ${detalle} ${colorTextPzaRecibida} dark:${colorTextPzaRecibida} hover:underline" data-numOrden="${row.numero_orden}" data-tipo-modal="pzs-faltantes" data-modal-target="siniestros-modal" data-tipo="pzs-faltantes" data-id_siniestro="${row.id}">${row.pzs_faltantes}</a>`;
            },
          },
          {
            title: "Opciones",
            data: function (data) {
              const permisosSpan = document.getElementById("permisosSpan");
              if (data.estado === "Cerrado") {
                return `
                          <div class="flex items-center space-x-4">
                              <button type="button" data-id="${data.id}" class="reabrirSiniestroButton flex items-center text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 ">
                                  Reabrir
                              </button>
                          </div>
                      `;
              } else {
                let buttons = '';
                // console.log("data", data.perfil_id, "id_perfil", id_perfil);

                if (data.estado === "Completado" && String(data.perfil_id) === String(id_perfil)) {
                  buttons += `
                              <button type="button" data-id="${data.id}" 
                                  class="cerrarSiniestroButton inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-red-700 bg-red-100/80 hover:bg-red-500 hover:text-white rounded-xl transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-red-500/30 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">
                                  <i class="fa-solid fa-lock mr-1.5"></i> Cerrar
                              </button>`;
                }
                /*
                const isRefacciones = Perfil === 'refacciones';
                const isAutocar = ['autocar_pensiones', 'autocar_periferico'].includes(Perfil.toLowerCase());

                const presupuestosCotizados = Number(data.presupuestos_cotizados || 0);
                const tallerH = String(data.vehiculo_info?.taller ?? data.vehiculoInfo?.taller ?? "").trim().toUpperCase();
                const esFilaAutocar = tallerH === 'AUTOCAR PENSIONES' || tallerH === 'AUTOCAR PERIFERICO';

                if (isRefacciones) {
                  // Refacciones puede cancelar si NO es Autocar, O si es Autocar con presupuesto cotizado (que ellos solicitan cancelar)
                  if (!esFilaAutocar || (esFilaAutocar && presupuestosCotizados > 0)) {
                    buttons += `
                    <button type="button" data-id="${data.id}" data-motivo="${data.motivo_cancelacion || ''}" 
                    class="cancelSiniestroButton inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-red-700 bg-red-100/80 hover:bg-red-500 hover:text-white rounded-xl transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-red-500/30 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">
                    <i class="fa-solid fa-ban mr-1.5"></i> Cancelar
                    </button>`;
                  }
                } else if (isAutocar) {
                  if (presupuestosCotizados > 0) {
                    buttons += `
                    <button type="button" data-id="${data.id}" 
                    class="notificarCancelacionButton inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-orange-700 bg-orange-100/80 hover:bg-orange-500 hover:text-white rounded-xl transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-orange-500/30 dark:bg-orange-900/30 dark:text-orange-400 dark:hover:bg-orange-600 dark:hover:text-white">
                    <i class="fa-solid fa-bell mr-1.5"></i> Solicitar Cancelación
                    </button>`;
                  } else {
                    buttons += `
                    <button type="button" data-id="${data.id}" data-motivo="${data.motivo_cancelacion || ''}" 
                    class="cancelSiniestroButton inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-red-700 bg-red-100/80 hover:bg-red-500 hover:text-white rounded-xl transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-red-500/30 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">
                    <i class="fa-solid fa-ban mr-1.5"></i> Cancelar
                    </button>`;
                  }
                }
                */
                // if (String(data.perfil_id) === String(id_perfil) && data.estado !== "Cancelado") {

                if (String(data.perfil_id) === String(id_perfil)) {
                  buttons += `
                  <button type="button" data-id="${data.id}" data-motivo="${data.motivo_cancelacion || ''}" 
                  class="cancelSiniestroButton inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-red-700 bg-red-100/80 hover:bg-red-500 hover:text-white rounded-xl transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-red-500/30 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">
                  <i class="fa-solid fa-ban mr-1.5"></i> Cancelar
                  </button>`;
                }

                return `
                          <div class="flex items-center gap-2">
                              ${buttons}
                          </div>
                      `;
              }
            },
            visible: permisosSpan.textContent.includes("siniestros.update"),
          },
        ],
      });
    }; // fin initDT

    // Pre-cargar Abiertos + Completados en paralelo, luego inicializar el DataTable
    Promise.all([
      fetch(`${this.url}/getSiniestros`).then(r => r.json()),
      fetch(`${this.url}/getSiniestros?estado=Completado`).then(r => r.json())
    ])
      .then(([abiertos, completados]) => { allVinCache = [...abiertos, ...completados]; })
      .catch(() => { })
      .finally(() => initDT());

  }


  async getDetallePartes(id_siniestro, tipo) {
    this.tipo = tipo;
    const url = `${UrlProyecto}/${Perfil}/siniestros/getInfoPzas/${id_siniestro}/${tipo}`;
    const response = await fetch(url);
    return await response.json();

  }

  imprimirDetallePartes(result) {
    console.log(this.tipo, result)

    const table = this.getTableDetalle();
    if (!table) return;

    if (!result || result.success === false) {
      table.querySelector('thead').innerHTML = '';
      table.querySelector('tbody').innerHTML = `
        <tr><td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 font-medium italic">${result?.message || 'No se encontraron datos'}</td></tr>`;
      return;
    }

    if (this.tipo == 'presupuesto') {
      this.htmlPresupuestos(result.data)
    } else {
      this.htmlPiezas(result.data)
    }
  }

  htmlPiezas(piezas) {
    const table = this.getTableDetalle();
    if (!table) return;

    const thClass = 'px-4 py-3 text-[10px] font-bold text-white uppercase tracking-widest bg-gray-800 dark:bg-gray-700 border-none';
    const trClass = 'border-b border-gray-100 dark:border-gray-700/50 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition-colors duration-150';
    const tdClass = 'px-4 py-3 text-sm text-gray-600 dark:text-gray-300';
    const tdDescClass = 'px-4 py-3 text-sm text-gray-600 dark:text-gray-300 min-w-[200px] whitespace-normal';

    table.querySelector('thead').innerHTML = `
         <tr>
           <th class="${thClass} rounded-tl-xl">N° Orden</th>
           <th class="${thClass}">N° Vale</th>
           <th class="${thClass}">N° Presupuesto</th>
           <th class="${thClass}">N° Parte</th>
           <th class="${thClass}">Descripción</th>
           <th class="${thClass} rounded-tr-xl">Cantidad</th>
         </tr>`;

    let td = '';
    piezas.forEach(f => {
      td += `
          <tr class="${trClass}">
               <td class="${tdClass}">${f.numero_orden ?? "N/A"}</td>
               <td class="${tdClass}">${f.numero_vale ?? "N/A"}</td>
               <td class="${tdClass}">${f.numero_presupuesto ?? "N/A"}</td>
               <td class="${tdClass} font-bold text-blue-600 dark:text-blue-400">${f.numero_parte ?? "N/A"}</td>
               <td class="${tdDescClass}">${f.descripcion_w32 ?? "N/A"}</td>
               <td class="${tdClass} text-center font-bold">${f.cantidad ?? "0"}</td>
          </tr>`;
    });

    table.querySelector('tbody').innerHTML = td;
  }

  htmlPresupuestos(presupuestos) {
    const table = this.getTableDetalle();
    if (!table) return;

    const thClass = 'px-4 py-3 text-[10px] font-bold text-white uppercase tracking-widest bg-gray-800 dark:bg-gray-700 border-none';
    const tdClass = 'px-4 py-3 text-sm text-gray-600 dark:text-gray-300';

    table.querySelector('thead').innerHTML = `
         <tr>
           <th class="${thClass} rounded-tl-xl">N° de Presupuesto</th>
           <th class="${thClass}">Proveedor</th>
           <th class="${thClass} rounded-tr-xl">Total</th>
         </tr>`;

    let td = '';
    presupuestos.forEach(f => {
      td += `
          <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-150">
               <td class="px-4 py-3">
                  <a href="${UrlProyecto}/${Perfil}/presupuestos/ver?folio=${f.numero_presupuesto}" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 hover:underline font-bold" target="_${f.numero_presupuesto}" rel="noopener noreferrer">
                     ${f.numero_presupuesto ?? "N/A"}
                  </a>
               </td>
               <td class="${tdClass}">${f.proveedor ?? "N/A"}</td>
               <td class="${tdClass} font-bold text-gray-900 dark:text-white">$${f.total ?? "0.00"}</td>
          </tr>`;
    });

    table.querySelector('tbody').innerHTML = td;
  }

  initFormsMVA() {
    const forms = ["formAseguradora", "formVehiculo", "formMarca", "formCliente"];

    forms.forEach(form => {
      const formElement = document.querySelector(`#${form}`);
      if (!formElement) return;

      formElement.addEventListener("submit", async (e) => {
        e.preventDefault();

        const confirm = await swalAlert({
          title: "¿Deseas realizar el registro?",
          text: "Asegúrate de que los datos sean correctos antes de continuar.",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Sí, registrar",
          cancelButtonText: "Cancelar"
        });

        if (!confirm.isConfirmed) return;

        const data = Object.fromEntries(new FormData(e.target));

        const endpoints = {
          formAseguradora: `${UrlProyecto}/aseguradoras/crear`,
          formVehiculo: `${UrlProyecto}/vehiculos/crear`,
          formMarca: `${UrlProyecto}/marcas/crear`,
          formCliente: `${UrlProyecto}/clientes/crear`,
        };

        try {
          const response = await fetch(endpoints[form], {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify(data),
          });

          const result = await response.json();

          if (response.ok) {
            swalAlert("¡Éxito!", result.message || "Registro creado correctamente", "success");
            formElement.reset();
          } else {
            Toast(result.message || "Error al crear el registro", "error");
          }
        } catch (error) {
          console.error("Error:", error);
          Toast("Error al procesar la solicitud", "error");
        }
      });
    });
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

  async cancelar(id, motivo) {
    try {
      const response = await fetch(`${this.url}/${id}/cancelar`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
        body: JSON.stringify({ motivo_cancelacion: motivo }),
      });
      if (!response.ok) throw new Error("Error en la respuesta del servidor");
      return await response.json();
    } catch (error) {
      console.error("Error al cancelar:", error);
      swalAlert({ title: "Error", text: "Problema de conexión.", icon: "error" });
    }
  }

  async cerrar(id) {
    try {
      const response = await fetch(`${this.url}/${id}/cerrar`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
      });
      if (!response.ok) {
        const errorData = await response.json().catch(() => null);
        if (errorData) return errorData;
        throw new Error("Error en la respuesta del servidor");
      }
      return await response.json();
    } catch (error) {
      console.error("Error al cerrar:", error);
      swalAlert({ title: "Error", text: "Problema de conexión o de servidor.", icon: "error" });
      return { success: false, title: "Error", message: "Problema de conexión", icon: "error" };
    }
  }

  async solicitarCancelacion(id, motivo) {
    try {
      showSendingMailAlert();
      const response = await fetch(`${this.url}/${id}/notificar-cancelacion`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
        body: JSON.stringify({ motivo_cancelacion: motivo }),
      });
      if (!response.ok) throw new Error("Error en la respuesta del servidor");
      return await response.json();
    } catch (error) {
      console.error("Error al solicitar cancelación:", error);
      swalAlert({ title: "Error", text: "Problema de conexión.", icon: "error" });
    }
  }

  async reabrir(id) {
    try {
      const response = await fetch(`${this.url}/${id}/reabrir`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
      });
      if (!response.ok) throw new Error("Error en la respuesta del servidor");
      return await response.json();
    } catch (error) {
      console.error("Error al reabrir:", error);
      swalAlert({ title: "Error", text: "Problema de conexión.", icon: "error" });
    }
  }
}


export default Siniestro;
