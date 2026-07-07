import Presupuestos from "../class/Presupuestos.js";
import { UrlProyecto, token, folio , swalAlert} from "../functions/const.js";

const presupuestosClass = new Presupuestos();
export const DTLoad = async () => {
  return presupuestosClass.DTable();
};

export const initTooltips = async () => {
  const buttons = document.querySelectorAll(".tooltip-trigger");

  buttons.forEach((btn) => {
    const tooltipEl = btn.nextElementSibling; // Asumiendo que el tooltip es el siguiente elemento

    btn.addEventListener("mouseenter", () => {
      tooltipEl.classList.remove("hidden"); // Muestra el tooltip
    });

    btn.addEventListener("mouseleave", () => {
      tooltipEl.classList.add("hidden"); // Oculta el tooltip
    });
  });
};

export const recargarTabla = async () => {
  $("#presupuestosTable").DataTable().ajax.reload();
};

export const limpiarEstado = async () => {
  $('input[name="filtroEstado"]').prop("checked", false);
  await recargarTabla();
};

export const filtrarTaller = async () => {
  $('select[name="taller"]').prop("checked", false);
  await recargarTabla();
};

export const buscar = async (e) => {
  $("#presupuestosTable").DataTable().search(e.target.value).draw();
};

export const agregarPresupuesto = async (event) => {
  event.preventDefault();

  let permisos = "";
  const span = document.getElementById("permisosSpan");
  if (span) {
    permisos = span.textContent || "";
  }

  // Funciones para verificar permisos
  const puedeCotizarDirectamente = permisos.includes(
    "presupuestos.cotizardirectamente",
  );

  const formData = new FormData(nuevoPresupuestoForm);

  const piezas = [];
  const rows = document.querySelectorAll("#agregarPresupuestoTable tbody tr");

  rows.forEach((row, index) => {
    let numeroParte,
      descripcion,
      descripcion_w32,
      cantidad,
      precioUnitario,
      total,
      existencia;

    if (puedeCotizarDirectamente) {
      // Si puede cotizar directamente, los name tienen _{indice}
      numeroParte =
        row.querySelector(`input[name="numero_parte_${index + 1}"]`)?.value ||
        null;
      descripcion =
        row.querySelector(`input[name="descripcion_${index + 1}"]`)?.value ||
        null;
      descripcion_w32 =
        row.querySelector(`input[name="descripcion_w32_${index + 1}"]`)
          ?.value || null;
      cantidad =
        row.querySelector(`input[name="cantidad_${index + 1}"]`)?.value || null;
      precioUnitario =
        row.querySelector(`input[name="precio_unitario_${index + 1}"]`)
          ?.value || null;
      total =
        row.querySelector(`input[name="importe_total_${index + 1}"]`)?.value ||
        null;
      existencia =
        row.querySelector(`input[name="existencia_${index + 1}"]`)?.value ||
        null;
    } else {
      // Si no, los name no tienen _{indice}
      numeroParte =
        row.querySelector('input[name="numero_parte"]')?.value || null;
      descripcion =
        row.querySelector('input[name="descripcion"]')?.value || null;
      cantidad = row.querySelector('input[name="cantidad"]')?.value || null;
      precioUnitario =
        row.querySelector('input[name="precio_unitario"]')?.value || null;
      total = row.querySelector('input[name="importe_total"]')?.value || null;
      existencia = row.querySelector('input[name="existencia"]')?.value || null;
    }

    // Validar si hay al menos un valor relevante
    if (
      numeroParte ||
      descripcion ||
      parseFloat(cantidad) > 0 ||
      parseFloat(precioUnitario) > 0 ||
      parseFloat(total) > 0 ||
      existencia
    ) {
      piezas.push({
        numero_parte: numeroParte,
        descripcion: descripcion,
        descripcion_w32,
        cantidad: cantidad,
        precio_unitario: precioUnitario,
        total: total,
        existencia: existencia,
      });
    }
  });

  formData.append("piezas", JSON.stringify(piezas));

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres agregar este presupuesto?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, agregar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      const presupuestosClass = new Presupuestos();
      await presupuestosClass.agregarPresupuesto(formData);
      window.location.href = `${UrlProyecto}/presupuestos/`;
    }
  });
};

export const cotizarPresupuesto = async (event, cotizarPresupuestoForm) => {
  event.preventDefault();
  const formData = new FormData(cotizarPresupuestoForm); // Crea un FormData a partir del formulario

  const faltantes = [];
  const piezas = [];
  const rows = document.querySelectorAll("#cotizarPresupuestoTable tbody tr");

  for (let index = 0; index < rows.length; index++) {
    const row = rows[index];
    const id =
      row.querySelector(`input[name="pieza_id_${index + 1}"]`)?.value || null;
    const numeroParte =
      row.querySelector(`input[name="numero_parte_${index + 1}"]`)?.value ||
      null;
    const descripcion =
      row.querySelector(`input[name="descripcion_${index + 1}"]`)?.value ||
      null;
    const descripcionW32 =
      row.querySelector(`input[name="descripcion_w32_${index + 1}"]`)?.value ||
      null;
    const cantidad =
      row.querySelector(`input[name="cantidad_${index + 1}"]`)?.value || null;
    const precioUnitario =
      row.querySelector(`input[name="precio_unitario_${index + 1}"]`)?.value ||
      null;
    const total =
      row.querySelector(`input[name="importe_total_${index + 1}"]`)?.value ||
      null;
    const existencia =
      row.querySelector(`input[name="existencia_${index + 1}"]`)?.value || null;
    const selectTiempoEntrega = row.querySelector(
      `select[name="tiempoentrega_${index + 1}"]`,
    );
    const tiempoEntrega = selectTiempoEntrega
      ? selectTiempoEntrega.value
      : null;

    if (
      numeroParte ||
      parseFloat(cantidad) > 0 ||
      parseFloat(precioUnitario) > 0 ||
      parseFloat(total) > 0 ||
      existencia
    ) {
      piezas.push({
        id: id,
        numero_parte: numeroParte,
        descripcion: descripcion,
        descripcion_w32: descripcionW32,
        cantidad: cantidad,
        precio_unitario: precioUnitario,
        total: total,
        existencia: existencia,
        tiempo_entrega: tiempoEntrega,
      });
    }

    if (!numeroParte) {
      faltantes.push(descripcion);
    }
  }
  const faltantesTexto = faltantes.join(", ");

  // Definir el texto de la alerta basado en si hay piezas faltantes
  const swalText =
    faltantes.length > 0
      ? `Las siguientes piezas no tienen número de parte y no se cotizarán: ${faltantesTexto}. ¿Deseas continuar?`
      : "¿Quieres agregar esta cotización al presupuesto?";

  swalAlert({
    title: "¿Estás seguro?",
    text: swalText,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, agregar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      // Agregar las piezas al FormData
      formData.append("piezas", JSON.stringify(piezas));

      try {
        const response = await fetch(
          `${UrlProyecto}/presupuestos/cotizar/${folio}`,
          {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": token,
            },
            body: formData,
          },
        );

        if (!response.ok) {
          throw new Error("Error en la respuesta del servidor");
        }

        const data = await response.json();

        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: "Aceptar",
          theme: "auto",
          timer: 2000,
        }).then(() => {
          enviarCorreoCotizacion(data.presupuesto.numero_presupuesto);
        });
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
  });
};

export const agregarEvidencias = async (folio) => {
  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres agregar las evidencias al presupuesto?\n\nAl hacer click en aceptar, se abrirá tu cliente de correo para notificar y automáticamente se pasará a cotizar.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, agregar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const formData = new FormData();
        formData.append("folio", folio);

        const response = await fetch(
          `${UrlProyecto}/presupuestos/subir-evidencias`,
          {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": token,
            },
            body: formData,
          },
        );

        if (!response.ok) {
          throw new Error("Error en la respuesta del servidor");
        }

        const data = await response.json();

        const asunto = encodeURIComponent(
          `Evidencias para el presupuesto ${folio}`,
        );
        // Usamos HTML para el cuerpo del correo, con un hipervínculo llamado "LIGA"
        const cuerpo = encodeURIComponent(
          `Adjunto las evidencias correspondientes al presupuesto con folio ${folio}.\nLiga:${UrlProyecto}/presupuestos/cotizar?folio=${folio}`,
        );
        // Para que el correo se genere en formato HTML, se recomienda usar 'mailto' con 'body', pero algunos clientes no interpretan HTML en 'body'.
        // Sin embargo, se puede intentar con 'mailto' y el usuario puede copiar el enlace si su cliente no soporta HTML.
        window.open(`mailto:?subject=${asunto}&body=${cuerpo}`, "_blank");

        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: "Aceptar",
          theme: "auto",
        }).then(() => {
          if ($.fn.DataTable.isDataTable("#presupuestosTable")) {
            recargarTabla();
          }

          // Refrescamos la vista de detalles actual
          findPresupuestoByNumero(folio, "ver");
          // Ahora ocultamos el div padre del botón "subirEvidencias"
          const boton = document.querySelector(".subirEvidencias");
          if (boton && boton.parentElement) {
            boton.parentElement.classList.add("hidden");
          }
        });
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
  });
};

function enviarCorreoCotizacion(numeroPresupuesto) {
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

  fetch(`${UrlProyecto}/presupuestos/mail-cotizacion`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": token,
    },
    body: JSON.stringify({
      numeroPresupuesto: numeroPresupuesto,
      linkPresupuesto: `${UrlProyecto}/vales/asignar?folio=${numeroPresupuesto}`,
    }),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Error en la solicitud");
      }
      return response.json();
    })
    .then((data) => {
      swalAlert({
        title: data.title, // Título del objeto data
        text: data.message, // Mensaje del objeto data
        icon: data.icon, // Icono del objeto data
        theme: "auto", // Tema agregado
        timer: 3000,
        timerProgressBar: true,
      }).then(() => {
        location.href = `${UrlProyecto}/presupuestos`;
      });
    })
    .catch((error) => {
      console.error("Error:", error);
      swalAlert({
        title: "Error",
        text: "No se pudo enviar el correo.",
        icon: "error",
        theme: "auto", // Tema agregado
      }).then(() => {
        location.href = `${UrlProyecto}/presupuestos`;
      });
    });
}

export const findSiniestroByNumeroOrden = async (numeroOrden, selectTaller) => {
  console.log(selectTaller.value);

  try {
    let tallerParaBusqueda = selectTaller.value;

    // Si el taller es REFACCIONES, primero obtener el taller real del siniestro
    if (selectTaller.value.toUpperCase() === 'REFACCIONES') {
      try {
        // Hacer una petición sin taller para obtener el siniestro
        const responseTaller = await fetch(
          `${UrlProyecto}/siniestros/numero-orden/${numeroOrden}`
        );

        if (responseTaller.ok) {
          const siniestroData = await responseTaller.json();
          // Obtener el taller real del siniestro
          tallerParaBusqueda = siniestroData.vehiculo_info?.taller || selectTaller.value;
        }
      } catch (error) {
        console.warn('No se pudo obtener el taller real, usando REFACCIONES:', error);
      }
    }

    const response = await fetch(
      `${UrlProyecto}/siniestros/${numeroOrden}/${tallerParaBusqueda}`,
    );
    if (!response.ok) {
      cleanSiniestroByNumeroOrden();
      swalAlert({
        title: "No se encontro el siniestro",
        text: "Intenta con otra orden, esta no tiene siniestro asignado",
        icon: "error",
        theme: "auto",
      });
      throw new Error("Error al obtener el siniestro");
    }

    const result = await response.json();
    const {
      id,
      numero_orden,
      numero_siniestro,
      vehiculo_info: { aseguradora, vin, vehiculo, marca, modelo, taller },
    } = result;

    const inputs = {
      id_siniestro: id,
      numero_siniestro,
      aseguradora,
      vin,
      vehiculo,
      marca,
      modelo,
      // Solo incluir taller si NO es REFACCIONES (para mantener REFACCIONES visible)
      ...(selectTaller.value.toUpperCase() !== 'REFACCIONES' && { taller }),
    };

    Object.entries(inputs).forEach(([key, val]) => {
      document.querySelector(`#${key}`).value = val;
    });
  } catch (error) {
    console.error("Error:", error);
  }
};

export const cleanSiniestroByNumeroOrden = async () => {
  const ids = [
    "id_siniestro",
    "numero_siniestro",
    "aseguradora",
    "vin",
    "vehiculo",
    "marca",
    "modelo",
  ];

  ids.forEach((id) => {
    const element = document.querySelector(`#${id}`);
    if (element) {
      element.value = "";
    }
  });
};

export const findPresupuestoByNumero = async (folio, accion) => {
  try {
    const response = await fetch(`${UrlProyecto}/presupuestos/${folio}`);
    if (!response.ok) {
      throw new Error("Error al obtener el presupuesto");
    }
    const result = await response.json();

    const tiempoEntrega = result[0].piezas[0].tiempoentrega;

    const {
      id,
      proveedor,
      estado,
      fecha_cotizado,
      // subtotal,
      // iva,
      // total,
      siniestros: {
        numero_orden,
        numero_siniestro,
        vehiculo_info: { aseguradora, marca, modelo, taller, vehiculo, vin },
      } = {},
    } = result[0];
    const piezas = result[0].piezas;
    //***Puede ser Form por alguna razon
    const tabla =
      accion === "ver"
        ? "#verPresupuestoTable tbody"
        : "#cotizarPresupuestoTable tbody";
    const tbody = document.querySelector(tabla);
    if (tbody) tbody.innerHTML = "";

    let permisos = "";
    const span = document.getElementById("permisosSpan");
    if (span) {
      permisos = span.textContent || "";
    }

    const puedeVerDescripcionW32 = permisos.includes("descripcionw32.read");

    piezas.forEach((pieza, index) => {
      const nuevaFila = tbody.insertRow();
      const contador = index + 1;

      nuevaFila.innerHTML =
        accion === "ver"
          ? `
            <td class="px-4 py-1">
               <button class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">${contador}</button>
            </td>
            <td class="px-2 py-1">
               <input type="text" name="numero_parte" value="${pieza.numero_parte ?? ""
          }" readonly placeholder="Número de parte"
               class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
            </td>
            <td class="px-2 py-1">
               <input type="text" name="descripcion" value="${pieza.descripcion ?? ""
          }" readonly placeholder="Descripción de parte"
               class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
            </td>
            ${puedeVerDescripcionW32
            ? `
            <td class="px-2 py-1">
               <input type="text" name="descripcion_w32" value="${pieza.descripcion_w32 ?? ""
            }" readonly placeholder="Descripción de parte"
               class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
            </td>
            `
            : ""
          }
            <td class="px-2 py-1">
               <input type="number" name="cantidad" value="${pieza.numero_pzas_presupuesto ?? 0
          }" data-conteo="${contador}" readonly placeholder="0"
               class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
            </td>
            <td class="px-2 py-1">
               <input type="number" name="precio_unitario" value="${pieza.importe_unitario ?? 0
          }" readonly placeholder="$0.00"
               class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
            </td>
            <td class="px-2 py-1">
               <input type="number" name="importe_total" value="${pieza.importe_total ?? 0
          }" readonly placeholder="$0.00"
               class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
            </td>
            <td class="px-2 py-1">
               <input type="number" name="existencia" value="${pieza.existencia ?? 0
          }" readonly placeholder="0"
               class="text-gray-900 text-sm block w-full m-2 bg-transparent border-0 focus:ring-0 focus:border-0 dark:bg-transparent dark:border-0 dark:placeholder-gray-400 dark:text-white">
            </td>
           <td class="px-2 py-1">
                  <input type="text" name="tiempoentrega_${contador}" value="${pieza.tiempoentrega ?? "1a3dias"
          }" placeholder="Tiempo de entrega"
                  class="block border-gray-800 dark:bg-gray-800 dark:focus:ring-primary-500 dark:placeholder-gray-400 dark:text-white m-2 rounded-lg text-sm text-white w-full" disabled>
                  
               </td>
         `
          : `
               <td class="px-4 py-1">
                  <button class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">${contador}</button>
               </td>
               <td class="hidden">
                  <input type="hidden" name="pieza_id_${contador}" value="${pieza.id ?? ""
          }" ${!pieza.id ? "" : ""}>
               </td>
               <td class="px-2 py-1">
                  <input type="text" name="numero_parte_${contador}" value="${pieza.numero_parte ?? ""
          }" data-conteo="${contador}" placeholder="Número de parte"
                  class="numero-parte bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
               </td>
               <td class="px-2 py-1">
                  <input type="text" name="descripcion_${contador}" value="${pieza.descripcion ?? ""
          }" placeholder="Descripción de parte"
                  class="block border-gray-800 dark:bg-gray-800 dark:focus:ring-primary-500 dark:placeholder-gray-400 dark:text-white m-2 rounded-lg text-sm text-white w-full" disabled>
               </td>
               ${puedeVerDescripcionW32
            ? `
               <td class="hidden px-2 py-1">
                  <input type="text" name="descripcion_w32_${contador}" value="${pieza.descripcion_w32 ?? ""
            }" placeholder="Descripción de parte"
                  class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
               </td>
               `
            : ""
          }
               <td class="px-2 py-1">
                  <input type="text" name="cantidad_${contador}" data-conteo="${contador}" value="${pieza.numero_pzas_presupuesto ?? 0
          }" placeholder="0"
                  class="cantidad bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
               </td>
               <td class="px-2 py-1">
                  <input type="text" name="precio_unitario_${contador}" data-conteo="${contador}" value="" placeholder="$0.00"
                  class="precio-unitario bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" disabled>
               </td>
               <td class="px-2 py-1">
                  <input type="text" name="importe_total_${contador}" value="" placeholder="$0.00"
                  class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" disabled>
               </td>
               <td class="px-2 py-1">
                  <input type="text" name="existencia_${contador}" value="${pieza.existencia ?? 0
          }" placeholder="0"
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
    });

    const formatDate = (date) => {
      const d = new Date(date);
      return `${d.getFullYear()}-${(d.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-${d.getDate().toString().padStart(2, "0")}`;
    };

    const inputs = {
      id_siniestro: id,
      numero_orden,
      numero_siniestro,
      aseguradora,
      vin,
      vehiculo,
      marca,
      modelo,
      taller,
      proveedor,
      estado,
      fecha_cotizado: formatDate(fecha_cotizado),
    };

    Object.entries(inputs).forEach(([key, val]) => {
      const inputElement = document.querySelector(`#${key}`);
      if (inputElement) {
        inputElement.value = val || ""; // Asignar el valor (con valor por defecto)
        inputElement.setAttribute("readonly", true); // Hacer el input readonly
      } else {
        console.warn(`Input con id #${key} no encontrado`);
      }
    });

    // Validar proveedor después de cargar los datos
    const proveedorSelect = document.querySelector("#proveedor");
    if (proveedorSelect) {
      consultarDatosByProveedor(proveedorSelect);
    }
  } catch (error) {
    console.error("Error:", error);
  }
};

export const agregarColumna = async (accion) => {
  let permisos = "";
  const span = document.getElementById("permisosSpan");
  if (span) {
    permisos = span.textContent || "";
  }
  //Verificar si cotiza directamente o sigue el flujo normal
  const puedeCotizarDirectamente = permisos.includes(
    "presupuestos.cotizardirectamente",
  );

  const tabla =
    accion === "cotizar"
      ? "cotizarPresupuestoTable"
      : "agregarPresupuestoTable";
  const tbody = document.getElementById(tabla).getElementsByTagName("tbody")[0];
  const contador = tbody.rows.length;
  const nuevaFila = tbody.insertRow(); // Inserta la fila al final del tbody
  document
    .getElementById("eliminarColumnaPresupuesto")
    .classList.remove("opacity-0");

  // Si puede cotizar directamente, todos los inputs son habilitados y tienen los mismos estilos que descripcion y cantidad
  if (accion === "crear") {
    if (puedeCotizarDirectamente) {
      nuevaFila.innerHTML = `
            <td class="px-4 py-1">
              <span class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">${contador + 1
        }</span>
            </td>
            <td class="px-2 py-1">
              <input type="text" name="numero_parte_${contador + 1
        }" data-conteo="${contador + 1}" id="numero_parte_${contador + 1
        }" placeholder="Número de parte" required class="numero-parte bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="descripcion_${contador + 1
        }" data-conteo="${contador + 1}" id="descripcion_${contador + 1
        }" placeholder="Descripción de parte" required class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
              <input type="hidden" name="descripcion_w32_${contador + 1
        }" data-conteo="${contador + 1}" id="descripcion_w32_${contador + 1
        }" placeholder="Descripción de w32">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="cantidad_${contador + 1}" data-conteo="${contador + 1
        }" id="cantidad_${contador + 1
        }" placeholder="0" required class="cantidad bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="precio_unitario_${contador + 1
        }" data-conteo="${contador + 1}" id="precio_unitario_${contador + 1
        }" placeholder="$0.00" required class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="importe_total_${contador + 1
        }" data-conteo="${contador + 1}" id="importe_total_${contador + 1
        }" placeholder="$0.00" required class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="existencia_${contador + 1
        }" data-conteo="${contador + 1}" id="existencia_${contador + 1
        }" placeholder="0" required class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            </td>
             <td class="px-2 py-1">
                  <select name="tiempoentrega_${contador + 1}" data-conteo="${contador + 1
        }" id="tiempoentrega_${contador + 1
        }" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50" disabled>
                                 <option value="1a3dias">1 a 3 dias</option>
                                 <option value="4a10dias">4 a 10 dias</option>
                                 <option value="BackOrder">Back Order </option>
                                 </select>
                  
               </td>
         `;
    } else {
      nuevaFila.innerHTML = `
            <td class="px-4 py-1">
              <span class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">${contador + 1
        }</span>
            </td>
            <td class="px-2 py-1">
              <input type="text" name="numero_parte" id="numero_parte" disabled placeholder="Número de parte" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="descripcion" id="descripcion" placeholder="Descripción de parte" required class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="cantidad" id="cantidad" placeholder="0" required class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="precio_unitario" id="precio_unitario" disabled placeholder="$0.00" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="importe_total" id="importe_total" disabled placeholder="$0.00" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50">
            </td>
            <td class="px-2 py-1">
              <input type="text" name="existencia" id="existencia" disabled placeholder="0" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50">
            </td>
               <td class="px-2 py-1">
                  <select name="tiempoentrega" id="tiempoentrega" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50" disabled>
                                 <option value="1a3dias">1 a 3 dias</option>
                                 <option value="4a10dias">4 a 10 dias</option>
                                 <option value="BackOrder">Back Order </option>
                                 </select>
                  
               </td>
         `;
    }
  }
  `
      <td class="px-4 py-1">
        <span class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300">${contador + 1
    }</span>
      </td>
      <td class="hidden px-2 py-1">
        <input type="hidden" name="pieza_id_${contador + 1}">
      </td>
      <td class="px-2 py-1">
        <input type="text" name="numero_parte_${contador + 1
    }" id="numero_parte_${contador + 1}" data-conteo="${contador + 1
    }" placeholder="Número de parte" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      </td>
      <td class="px-2 py-1">
        <input type="text" name="descripcion_${contador + 1}" id="descripcion_${contador + 1
    }" placeholder="Descripción de parte" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      </td>
      <td class="px-2 py-1 hidden">
        <input type="text" name="descripcion_w32_${contador + 1
    }" id="descripcion_w32_${contador + 1
    }" placeholder="Descripción de parte" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      </td>
      <td class="px-2 py-1">
        <input type="number" name="cantidad_${contador + 1}" data-conteo="${contador + 1}" id="cantidad_${contador + 1
    }" placeholder="0" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      </td>
      <td class="px-2 py-1">
        <input type="Number" name="precio_unitario_${contador + 1
    }" data-conteo="${contador + 1}" id="precio_unitario_${contador + 1
    }" placeholder="$0.00" class="precio-unitario bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      </td>
      <td class="px-2 py-1">
        <input type="Number" name="importe_total_${contador + 1
    }" id="importe_total_${contador + 1
    }" placeholder="$0.00" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      </td>
      <td class="px-2 py-1">
        <input type="Number" name="existencia_${contador + 1}" id="existencia_${contador + 1
    }" disabled placeholder="0" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      </td>
       <td class="px-2 py-1">
                  <select name="tiempoentrega_${contador + 1
    }" id="tiempoentrega_${contador + 1
    }" class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-primary-600 focus:border-primary-600 block w-full m-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 opacity-50" disabled>
                                 <option value="1a3dias">1 a 3 dias</option>
                                 <option value="4a10dias">4 a 10 dias</option>
                                 <option value="BackOrder">Back Order </option>
                                 </select>
                  
               </td>
   `;
};

export const eliminarColumna = async (accion) => {
  const tabla =
    accion === "cotizar"
      ? "cotizarPresupuestoTable"
      : "agregarPresupuestoTable";
  const tbody = document.getElementById(tabla).getElementsByTagName("tbody")[0];
  if (tbody.rows.length > 1) {
    tbody.deleteRow(tbody.rows.length - 1); // Elimina la última fila
  }
  if (tbody.rows.length === 1) {
    // Buscar el botón por su ID y agregar la clase 'opacity-0'
    const btnEliminar = document.getElementById("eliminarColumnaPresupuesto");
    if (btnEliminar) {
      btnEliminar.classList.add("opacity-0");
    }
  }
};

export const consultarDatosByNumeroParte = async (numeroParte, indice) => {

  console.log("estoy aca");

  const response = await fetch(
    `${UrlProyecto}/presupuestos/consultar-descripcion/${numeroParte}`,
    {
      method: "GET",
    },
  );
  const dataW32 = await response.json();

  const importeUnitarioInput = document.querySelector(
    `input[name="precio_unitario_${indice}"]`,
  );
  const descripcionW32Input = document.querySelector(
    `input[name="descripcion_w32_${indice}"]`,
  );
  const existenciaInput = document.querySelector(
    `input[name="existencia_${indice}"]`,
  );

  if (dataW32 && dataW32.length > 0) {
    if (importeUnitarioInput) importeUnitarioInput.value = dataW32[0].PVP1 || 0;
    if (descripcionW32Input)
      descripcionW32Input.value = dataW32[0].DESCRIP || "";
    if (existenciaInput) existenciaInput.value = dataW32[0].STOCK || 0;
  } else {
    if (importeUnitarioInput) importeUnitarioInput.value = 0;
    if (descripcionW32Input) descripcionW32Input.value = "";
    if (existenciaInput) existenciaInput.value = 0;
  }

  // Llamar a calcularTotalPorFila después de actualizar los valores
  calcularTotalPorFila(indice);
};

export const calcularTotalPorFila = async (indice) => {
  // Buscar la fila correspondiente usando el input de cantidad
  const cantidadInput = document.querySelector(
    `input[name="cantidad_${indice}"]`,
  );
  const importeUnitarioInput = document.querySelector(
    `input[name="precio_unitario_${indice}"]`,
  );
  const importeTotalInput = document.querySelector(
    `input[name="importe_total_${indice}"]`,
  );

  // Si alguno de los inputs no existe, salir
  if (!cantidadInput || !importeUnitarioInput || !importeTotalInput) return;

  const precioUnitario = parseFloat(importeUnitarioInput.value) || 0;
  const cantidad = parseFloat(cantidadInput.value) || 0;
  const total = precioUnitario * cantidad;
  importeTotalInput.value = total.toFixed(2); // Asegura que el total tenga 2 decimales
  // Calcular el subtotal, IVA y total de la cotización
  calcularTotalCotizacion();
};

export const calcularTotalCotizacion = async () => {
  let permisos = "";
  const span = document.getElementById("permisosSpan");
  if (span) {
    permisos = span.textContent || "";
  }
  // Función para verificar permisos
  const puedeCotizarDirectamente = permisos.includes(
    "presupuestos.cotizardirectamente",
  );
  // Si puede cotizar directamente, usar "crearPresupuestoTable", si no, "cotizarPresupuestoTable"
  const tableId = puedeCotizarDirectamente
    ? "#agregarPresupuestoTable"
    : "#cotizarPresupuestoTable";
  const rows = document.querySelectorAll(`${tableId} tbody tr`);
  let subtotal = 0;

  // Sumar todos los totales de las filas
  Array.from(rows).forEach((row, index) => {
    const importeTotalInput = row.querySelector(
      `input[name="importe_total_${index + 1}"]`,
    ); // Usa index + 1 si tus inputs están numerados desde 1
    if (importeTotalInput) {
      subtotal += parseFloat(importeTotalInput.value) || 0;
    }
  });

  // Calcular el IVA (16%)
  const iva = subtotal * 0.16;

  // Calcular el total (subtotal + IVA)
  const total = subtotal + iva;

  // Actualizar los inputs correspondientes
  const subTotalInput = document.querySelector('input[name="subtotal"]');
  const ivaInput = document.querySelector('input[name="iva"]');
  const totalInput = document.querySelector('input[name="total"]');

  if (subTotalInput) subTotalInput.value = subtotal.toFixed(2);
  if (ivaInput) ivaInput.value = iva.toFixed(2);
  if (totalInput) totalInput.value = total.toFixed(2);
};

export const consultarDatosByProveedor = (proveedor) => {
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
