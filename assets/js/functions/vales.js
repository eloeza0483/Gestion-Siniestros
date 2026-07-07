import Vales from "../class/Vales.js";
import { UrlProyecto, Perfil, swalQuestion, token, swalAlert, showSendingMailAlert } from "../functions/const.js";

const departamentoSpan = document.getElementById("departamentoSpan");

const valesClass = new Vales();
let piezasAlbaranesGlobal = [];

export const DTLoad = async () => {
  return valesClass.DTable();
};

export const cancelarVale = (valeButton) => {
  const id = valeButton.dataset.id;

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres cancelar este vale?",
    icon: "question",
    input: "textarea",
    inputLabel: "Motivo de cancelación",
    inputPlaceholder: "Ingresa el motivo aquí...",
    showCancelButton: true,
    confirmButtonText: "Sí, cancelar",
    cancelButtonText: "No, descartar",
    buttonsStyling: false,
    background: '#1f2937',
    color: '#ffffff',
    customClass: {
      popup: 'bg-gray-800 rounded-xl border border-gray-700 shadow-2xl',
      title: 'text-white text-xl font-bold',
      htmlContainer: 'text-gray-300 text-sm mt-2',
      input: 'bg-gray-700 text-white border border-gray-600 focus:ring-red-500 focus:border-red-500 rounded-lg placeholder-gray-400 w-full p-3 mt-4',
      inputLabel: 'text-gray-300 font-semibold text-left w-full mt-2',
      confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg px-5 py-2.5 mx-2 transition-colors',
      cancelButton: 'bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-lg px-5 py-2.5 mx-2 transition-colors',
      validationMessage: 'bg-gray-900 text-red-400 border border-red-800 mt-2 rounded-lg'
    },
    preConfirm: (motivo) => {
      if (!motivo) {
        Swal.showValidationMessage('Debes ingresar un motivo de cancelación');
      }
      return motivo;
    }
  }).then(async (result) => {
    if (result.isConfirmed) {
      const motivo = result.value;
      const { success, icon, title, message } = await valesClass.cancelar(id, motivo);
      swalAlert({
        title,
        text: message,
        icon,
        confirmButtonText: "Aceptar",
        theme: "auto",
        timer: 2000,
      }).then(() => {
        if (success) recargarTabla();
      });
    }
  });
};

export const validarCampos = async (event) => {
  event.preventDefault();

  const numeroValeInput = document.querySelector("#numero_vale");
  numeroValeInput.setCustomValidity("");
  numeroValeInput.reportValidity();
  return numeroValeInput.checkValidity();
};

export const validarSurtidoAlbaran = async (numeroVale) => {
  const idVale =
    document.getElementById("id_vale")?.value ||
    new URLSearchParams(window.location.search).get("idVale");

  const result = await valesClass.getVale(numeroVale, idVale);

  const { piezas, surtidoAlbaranPorParte } = result;

  // Calcular piezas restantes para cada pieza
  const arrayPiezasRestantes = piezas.map((pieza) => {
    if (pieza.pivot?.activo == 0) return 0; // Ignorar piezas inactivas en el conteo

    const numeroParte = pieza.numero_parte
      ? String(pieza.numero_parte).trim()
      : "";
    const totalDisponibles = surtidoAlbaranPorParte[numeroParte] || 0;
    const piezasPresupuestadas = pieza.pivot?.cantidad ?? 0;

    const cantidadSurtida = Math.min(piezasPresupuestadas, totalDisponibles);
    return piezasPresupuestadas - cantidadSurtida;
  });

  // Verificar si todas las piezas están surtidas
  const todasSurtidas = arrayPiezasRestantes.every(
    (restante) => restante === 0,
  );

  if (todasSurtidas && arrayPiezasRestantes.length > 0) {
    // Cerrar el modal
    const modal = document.querySelector("#cerrarModalAlbaran");
    if (modal) {
      modal.click();
    }

    // Mostrar SweetAlert
    swalAlert({
      title: "Albaranes completados",
      text: "Todas las piezas ya han sido surtidas. No es necesario asignar más albaranes.",
      icon: "info",
      confirmButtonText: "Aceptar",
      theme: "auto",
    });

    return false;
  }

  return true;
};

export const findValeByNumero = async (numeroVale, accion, idVale = null) => {
  try {
    const result = await valesClass.getVale(numeroVale, idVale);

    const {
      id,
      estado,
      fecha_vale,
      fecha_promesa,
      albaranes,
      presupuestos: presupuestosRaw,
      piezas,
      surtidoAlbaranPorParte,
      surtidoEntradaPorParte,
      piezasAlbaranes,
      permisosGlobales
    } = result;

    const presupuestos = presupuestosRaw || {};
    const { numero_presupuesto, proveedor, siniestros: siniestrosRaw } = presupuestos;
    const siniestros = siniestrosRaw || {};
    const {
      numero_orden,
      numero_siniestro,
      vehiculo_info: vehiculo_infoRaw,
      cliente: clienteRaw
    } = siniestros;

    const { codigo } = clienteRaw || {};
    const codigoInput = document.querySelector("#codigo");
    if (codigoInput) {
      codigoInput.value = codigo || "";
    }

    const vehiculo_info = vehiculo_infoRaw || {};
    const cliente = clienteRaw || {};

    const taller = (vehiculo_info && vehiculo_info.taller) || "sin taller";
    const { aseguradora, vin, vehiculo, marca, modelo } = vehiculo_info || {};
    const { nombre: nombre_cliente } = cliente || {};

    piezasAlbaranesGlobal = piezasAlbaranes;

    const tbody = document.querySelector("#verValeTable tbody");

    let arrayPiezasRestantes = [];

    piezas.forEach((pieza, index) => {
      const nuevaFila = tbody.insertRow();

      const numeroParte = pieza.numero_parte
        ? String(pieza.numero_parte).trim()
        : "";

      const sAlbaran = surtidoAlbaranPorParte[numeroParte] || 0;
      const sEntrada = surtidoEntradaPorParte[numeroParte] || 0;
      const piezasPresupuestadas = pieza.pivot.cantidad ?? 0;

      // Buscar la descripción del albarán correspondiente a esta pieza
      const piezaAlbaran = piezasAlbaranes?.find((pa) => {
        const refAlbaran = pa.REFERENCIA ? String(pa.REFERENCIA).trim() : "";
        return refAlbaran === numeroParte;
      });
      const descripcionAlbaran = piezaAlbaran?.DESCRIP || "";

      const porcentajeAlbaran =
        piezasPresupuestadas > 0
          ? (Math.min(sAlbaran, piezasPresupuestadas) / piezasPresupuestadas) *
          100
          : 0;
      const porcentajeEntrada =
        piezasPresupuestadas > 0
          ? (Math.min(sEntrada, piezasPresupuestadas) / piezasPresupuestadas) *
          100
          : 0;

      const btnAlbaran = document.getElementById("asignarAlbaranButton");
      const btnEntrada = document.getElementById("asignarEntradaButton");

      const piezasRestantesAlbaran =
        piezasPresupuestadas - Math.min(sAlbaran, piezasPresupuestadas);
      const piezasRestantesEntrada =
        piezasPresupuestadas - Math.min(sEntrada, piezasPresupuestadas);

      if (pieza.pivot?.activo == 0) {
        arrayPiezasRestantes.push(0);
      } else if (btnEntrada) {
        arrayPiezasRestantes.push(piezasRestantesEntrada);
      } else {
        arrayPiezasRestantes.push(piezasRestantesAlbaran);
      }
      if (pieza.pivot?.activo == 0) {
        nuevaFila.classList.add("opacity-50", "bg-red-900/10");
      }

      let filaHTML = `
          <td class="px-1 py-1">
              <div class="numeroParte text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white ${pieza.pivot?.activo == 0 ? 'line-through text-red-500' : ''}">${pieza.numero_parte ?? ""
        }</div>
          </td>
          <td class="px-1 py-1">
              <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white ${pieza.pivot?.activo == 0 ? 'text-red-500' : ''}">
                ${pieza.descripcion ?? ""}
                ${pieza.pivot?.activo == 0 ? ' <span class="text-xs text-red-500 font-bold">(Cancelado)</span>' : ''}
                ${descripcionAlbaran ? `<p class="text-xs font-thin text-gray-100 ">${descripcionAlbaran}</p>` : ""}
                ${(pieza.pivot?.solicitud_eliminacion === true || pieza.pivot?.solicitud_eliminacion === 1) && pieza.pivot?.usuario_solicita_nombre ? `<p class="text-xs text-red-600 dark:text-red-200 font-bold mt-1"><i class="fas fa-exclamation-circle"></i> Solicitó eliminación: ${pieza.pivot.usuario_solicita_nombre} el ${pieza.pivot.fecha_solicitud_eliminacion_formateada ?? ''}</p>` : ""}
              </div>
          </td>
          <td class="px-1 py-1">
              <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${piezasPresupuestadas}
              </div>
          </td>
          <td class="px-1 py-1">
              <div class="flex flex-col gap-1">
        ${btnAlbaran
          ? `
        <div class="relative w-full h-4 bg-gray-200 rounded-full dark:bg-gray-700 overflow-hidden" title="Surtido (Albarán)">
            <div class="absolute top-0 left-0 h-full bg-blue-600 rounded-full transition-all duration-300"
                style="width: ${porcentajeAlbaran}%"></div>
            <div class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-gray-800 dark:text-white z-10 drop-shadow-md">
                Alb: ${piezasRestantesAlbaran}
            </div>
        </div>`
          : ""
        }
        ${btnEntrada
          ? `
        <div class="relative w-full h-4 bg-gray-200 rounded-full dark:bg-gray-700 overflow-hidden" title="Recibido (Entrada)">
            <div class="absolute top-0 left-0 h-full bg-green-600 rounded-full transition-all duration-300"
                style="width: ${porcentajeEntrada}%"></div>
            <div class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-gray-800 dark:text-white z-10 drop-shadow-md">
                Ent: ${piezasRestantesEntrada}
            </div>
        </div>`
          : ""
        }
        </div>
        </td>
        <td class="px-1 py-1">
            <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${pieza.importe_unitario ?? 0
        }</div>
        </td>
        <td class="px-1 py-1">
            <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${pieza.importe_total ?? 0
        }</div>
        </td>
        `;


      // Columna de Editar / Notificar (Siempre se añade para mantener alineación)
      filaHTML += `
        <td class="px-1 py-1">
            <div class="flex justify-center items-center gap-2">`;
      // console.log("permisossssssssssss:", permisosGlobales);

      const puedeEscribirVales = permisosGlobales.includes("vales.update");
      const puedePedirModificacion = permisosGlobales.includes("partes.write");
      const puedeBorrarPartes = permisosGlobales.includes("partes.delete");

      // console.log("puedeEscribirVales", puedeEscribirVales);
      // console.log("puedePedirModificacion", puedePedirModificacion);
      // console.log("puedeBorrarPartes", puedeBorrarPartes);

      const PerfilFormat = Perfil.replace(/_/g, " ").toUpperCase();
      // console.log(proveedor, PerfilFormat, puedeEscribirVales);

      if (estado !== 'Cancelado' && ((puedeEscribirVales && PerfilFormat === "REFACCIONES") || (puedeEscribirVales && PerfilFormat === "AUTOCAR PENSIONES" && PerfilFormat == "AUTOCAR PENSIONES" && proveedor != "CHEVROLET"))) {
        filaHTML += `
            <button type="button"
                class="editarPiezaButton flex items-center bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800 transition-transform duration-200 hover:scale-105"
                data-conteo="${index}" data-id="${pieza.id ?? ""}">
                <i class="fas fa-edit"></i>
            </button>`;
      }

      // El botón de notificar eliminación aparece si cuenta con el permiso para pedir modificaciones
      if (estado !== 'Cancelado' && puedePedirModificacion && (taller == "AUTOCAR PENSIONES" || taller == "AUTOCAR PERIFERICO")) {
        filaHTML += `
            <button type="button"
                class="notificarEliminarPiezaButton flex items-center bg-red-500 text-white hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 transition-transform duration-200 hover:scale-105"
                data-conteo="${index}" data-nparte="${pieza.numero_parte ?? ""}">
                <div class="flex gap-2">
                <i class="fas fa-envelope "></i> <i class="fas fa-trash"></i> 
                </div>
            </button>`;
      }

      filaHTML += `
            </div>
        </td>`;

      // Columna de Eliminar (Siempre se añade para mantener alineación)
      filaHTML += ` 
        <td class="px-1 py-1 text-center">
          <div class="flex justify-center items-center gap-2">`;
      console.log(taller);

      if (estado !== 'Cancelado' && ((puedeBorrarPartes && taller != "AUTOCAR PENSIONES" && taller != "AUTOCAR PERIFERICO") || (puedeBorrarPartes && PerfilFormat != "REFACCIONES"))) {
        // Determinar clases del botón según si tiene solicitud de eliminación
        const tieneSolicitud = pieza.pivot?.solicitud_eliminacion === true || pieza.pivot?.solicitud_eliminacion === 1;
        const clasesBoton = tieneSolicitud
          ? "eliminarPiezaButton flex items-center bg-red-700 text-white hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-red-800 dark:hover:bg-red-900 dark:focus:ring-red-700 transition-transform duration-200 hover:scale-105"
          : "eliminarPiezaButton flex items-center bg-red-500 text-white hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 transition-transform duration-200 hover:scale-105";

        let tooltipInfo = '';
        if (tieneSolicitud && pieza.pivot?.usuario_solicita_nombre) {
          tooltipInfo = `title="Solicitado por: ${pieza.pivot.usuario_solicita_nombre} el ${pieza.pivot.fecha_solicitud_eliminacion_formateada ?? ''}"`;
        }

        filaHTML += `
            <button type="button"
                    class="${clasesBoton}"
                    data-conteo="${index}" data-nparte="${pieza.numero_parte ?? ""}" ${tooltipInfo}>
                    <i class="fas fa-trash"></i>
            </button>`;

        // Si tiene solicitud de eliminación, y tiene poder para borrar/modificar partes, agregar botón para rechazar
        if (tieneSolicitud) {
          filaHTML += `
            <button type="button"
                    class="rechazarSolicitudButton flex items-center bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-3 py-2 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800 transition-transform duration-200 hover:scale-105"
                    data-conteo="${index}" data-nparte="${pieza.numero_parte ?? ""}"
                    title="Rechazar solicitud de eliminación">
                    <i class="fas fa-times"></i>
            </button>`;
        }
      }

      filaHTML += `
          </div>
        </td>`;

      nuevaFila.innerHTML = filaHTML;

      // Agregar fondo rojo sutil si la pieza tiene solicitud de eliminación
      if (pieza.pivot?.solicitud_eliminacion === true || pieza.pivot?.solicitud_eliminacion === 1) {
        nuevaFila.classList.add('bg-red-100', 'dark:bg-red-500');
      }
    });

    // Verificar si todas las piezas están completamente surtidas
    const todasSurtidas = arrayPiezasRestantes.every(
      (restante) => restante === 0,
    );

    if (todasSurtidas && arrayPiezasRestantes.length > 0) {
      swalAlert({
        title: "¡Vale Completado!",
        text: "Todas las piezas han sido surtidas correctamente.",
        icon: "success",
        confirmButtonText: "Aceptar",
        theme: "auto",
      });
    }

    const formatDate = (date) => {
      const d = new Date(date);
      return `${d.getFullYear()}-${(d.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-${d.getDate().toString().padStart(2, "0")}`;
    };

    const inputs = {
      id_vale: id,
      numero_orden,
      numero_siniestro,
      numero_presupuesto,
      aseguradora,
      vin,
      vehiculo,
      marca,
      modelo,
      taller,
      proveedor,
      estado,
      fecha_vale: formatDate(fecha_vale),
      fecha_promesa: formatDate(fecha_promesa),
    };

    const labelSpan = document.querySelector("#label_cliente_aseguradora");
    if (labelSpan) {
      if (nombre_cliente) {
        labelSpan.textContent = "Cliente";
        inputs.aseguradora = nombre_cliente;
      } else if (aseguradora) {
        labelSpan.textContent = "Aseguradora";
        inputs.aseguradora = aseguradora;
      }
    }

    Object.entries(inputs).forEach(([key, val]) => {
      const inputElement = document.querySelector(`#${key}`);
      if (inputElement) {
        inputElement.value = val || ""; // Asignar el valor (con valor por defecto)
        inputElement.setAttribute("readonly", true); // Hacer el input readonly
      } else {
        console.warn(`Input con id #${key} no encontrado`);
      }
    });
  } catch (error) {
    console.error("Error:", error);
  }
};

export const findEntradaByNumero = async (numeroEntrada, numeroVale, proveedor) => {
  try {
    const idVale = document.getElementById("id_vale")?.value;
    const response = await fetch(
      `${UrlProyecto}/${Perfil}/entradas/w-32/${numeroEntrada}?numeroVale=${numeroVale}&proveedor=${proveedor}${idVale ? `&idVale=${encodeURIComponent(idVale)}` : ""}`,
    );
    if (!response.ok) {
      throw new Error("Error al obtener la entrada");
    }
    const result = await response.json();

    if (!result.firebird || result.firebird.length === 0) {
      const tabla = document.querySelector("#datosEntradaTable");
      if (tabla) tabla.classList.add("hidden");
      swalAlert({
        title: "Error",
        text: "No se encontró la entrada.",
        icon: "error",
        confirmButtonText: "Aceptar",
        theme: "auto",
      });
      return;
    }

    let piezas = result.firebird;

    // Validación estricta para Chevrolet: Cada pieza de la entrada debe tener un albarán previo
    if (proveedor === "CHEVROLET") {
      const piezasSinAlbaran = piezas.filter(pieza => {
        const refEntrada = pieza.REFERENCIA ? String(pieza.REFERENCIA).trim() : "";
        return !piezasAlbaranesGlobal || !piezasAlbaranesGlobal.some(pa => {
          const refAlb = pa.REFERENCIA ? String(pa.REFERENCIA).trim() : "";
          return refAlb === refEntrada;
        });
      });

      if (piezasSinAlbaran.length > 0) {
        const refsFaltantes = piezasSinAlbaran.map(p => p.REFERENCIA).join(", ");
        const tabla = document.querySelector("#datosEntradaTable");
        if (tabla) tabla.classList.add("hidden");

        swalAlert({
          title: "Entrada no permitida",
          html: `La entrada consultada contiene piezas que aún no tienen un albarán asignado:<br><br><strong>${refsFaltantes}</strong><br><br>Debe asignar primero el albarán correspondiente a estas piezas antes de poder ingresar la entrada.`,
          icon: "error",
          confirmButtonText: "Entendido",
          theme: "auto"
        });
        return;
      }
    }

    const tbody = document.querySelector("#datosEntradaTable tbody");
    tbody.innerHTML = "";

    // TOTFAC = total real de la remisión (con IVA), es el mismo para todos los renglones.
    // Se usa directamente en lugar de acumular pieza.IMP (importe por renglón individual).
    const importeRemision = piezas.length > 0 ? (Number(piezas[0].TOTFAC) || 0) : 0;
    let sumaPiezas = 0;

    piezas.forEach((pieza, index) => {
      const refEntrada = pieza.REFERENCIA ? String(pieza.REFERENCIA).trim() : "";
      const tieneMatchAlbaran = piezasAlbaranesGlobal && piezasAlbaranesGlobal.some(pa => {
        const refAlb = pa.REFERENCIA ? String(pa.REFERENCIA).trim() : "";
        return refAlb === refEntrada;
      });

      const nuevaFila = tbody.insertRow();
      const statusIcon = tieneMatchAlbaran
        ? '<i class="fa-solid fa-check-double text-green-500 mr-1" title="Coincide con albarán"></i>'
        : '<i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1" title="Sin albarán previo"></i>';

      nuevaFila.innerHTML = `
<td class="px-4 py-3 whitespace-nowrap">
    <div class="flex items-center">
        ${statusIcon}
        <input type="text" name="piezas[${index}][numero_parte]" value="${pieza.REFERENCIA ?? ""}"
            class="text-sm bg-transparent border-0 ${tieneMatchAlbaran ? "text-gray-700 dark:text-gray-300" : "text-gray-400 font-italic"} w-full p-0 focus:ring-0 focus:outline-none"
            ${!pieza.REFERENCIA ? "disabled" : ""} required readonly>
    </div>
</td>
<td class="px-4 py-3">
    <input type="text" name="piezas[${index}][descripcion]" value="${pieza.DESCRIP ?? ""}"
        class="text-sm bg-transparent border-0 ${tieneMatchAlbaran ? "text-gray-700 dark:text-gray-300" : "text-gray-400"} w-full p-0 focus:ring-0 focus:outline-none"
        ${!pieza.DESCRIP ? "disabled" : ""} required readonly>
</td>
<td class="px-4 py-3 text-center">
    <input type="number" name="piezas[${index}][cantidad]" value="${pieza.UNI ?? 0}"
        class="text-sm bg-transparent border-0 ${tieneMatchAlbaran ? "text-gray-700 dark:text-gray-300" : "text-gray-400"} w-full p-0 focus:ring-0 focus:outline-none text-center"
        ${!pieza.UNI ? "disabled" : ""} required readonly>
</td>
<td class="px-4 py-3 text-right">
    <input type="number" name="piezas[${index}][importe]" value="${pieza.IMP ?? 0}"
        class="text-sm bg-transparent border-0 ${tieneMatchAlbaran ? "text-gray-900 dark:text-white" : "text-gray-400"} w-full p-0 focus:ring-0 focus:outline-none text-right font-semibold"
        ${!pieza.IMP ? "disabled" : ""} step="0.01" required readonly>
</td>
`;
      sumaPiezas += Number(pieza.UNI) || 0;
    });

    // Usar TOTFAC como importe total de la remisión (correcto, con IVA incluido)
    document.querySelector(`input[name="importe"]`).value = importeRemision.toFixed(2);
    document.querySelector(`input[name="num_partes"]`).value = sumaPiezas;
  } catch (error) {
    console.error("Error:", error);
  }
};

export const findAlbaranByNumero = async (numeroAlbaran, numeroVale) => {
  try {
    const idVale = document.getElementById("id_vale")?.value;
    const response = await fetch(
      `${UrlProyecto}/${Perfil}/albaranes/w-32/${numeroAlbaran}?numeroVale=${numeroVale}${idVale ? `&idVale=${encodeURIComponent(idVale)}` : ""}`,
    );
    if (!response.ok) {
      throw new Error("Error al obtener el albaran");
    }
    const result = await response.json();

    const piezas = result.firebird;
    console.log(result);

    if (piezas.length === 0) {
      const tabla = document.querySelector("#datosAlbaranTable");
      tabla.classList.add("hidden");
      const tbody = tabla.querySelector("tbody");
      tbody.innerHTML = "";
      swalAlert({
        title: "Error",
        text: "No se encontraron piezas para el albarán.",
        icon: "error",
        confirmButtonText: "Aceptar",
        theme: "auto",
      });
      return;
    }

    // Verifico si hay diferentes id (es decir, más de un albarán con el mismo número)
    const idsRemision = new Set();

    // Agregar cada ID de remisión al conjunto
    piezas.forEach((pieza) => {
      idsRemision.add(pieza.ID_REMISION);
    });

    const selectAlbaranTable = document.querySelector("#selectAlbaranTable");
    const selectAlbaranTbody = selectAlbaranTable.querySelector("tbody");

    // Si hay más de un albarán, mostrar la tabla de selección
    if (idsRemision.size > 1) {
      selectAlbaranTable.classList.remove("hidden");
      selectAlbaranTbody.innerHTML = "";

      // Obtener los albaranes únicos por ID_REMISION
      const albaranesUnicos = [];
      const idsAgregados = new Set();
      piezas.forEach((pieza) => {
        if (!idsAgregados.has(pieza.ID_REMISION)) {
          albaranesUnicos.push(pieza);
          idsAgregados.add(pieza.ID_REMISION);
        }
      });

      // Agregar filas a la tabla de selección
      albaranesUnicos.forEach((albaran, idx) => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
<td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 font-medium">${albaran.NUMREMISION ?? ""}</td>
<td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 font-medium">${albaran.FECHA_ ?? ""}</td>
<td class="px-4 py-3 text-center">
    <input type="radio" name="albaranSeleccionado" value="${albaran.ID_REMISION}" ${idx === 0 ? "checked" : ""}
        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 transition-all cursor-pointer">
</td>
`;
        selectAlbaranTbody.appendChild(fila);
      });

      // Evento para filtrar las piezas según el albarán seleccionado
      selectAlbaranTable
        .querySelectorAll('input[name="albaranSeleccionado"]')
        .forEach((radio) => {
          radio.addEventListener("change", function () {
            const idSeleccionado = this.value;
            // Filtrar las piezas que corresponden al albarán seleccionado
            const piezasFiltradas = piezas.filter(
              (p) => p.ID_REMISION == idSeleccionado,
            );

            // Actualizar la tabla de piezas
            const tbody = document.querySelector("#datosAlbaranTable tbody");
            tbody.innerHTML = "";
            let sumaImporte = 0;
            let sumaPiezas = 0;
            piezasFiltradas.forEach((pieza, index) => {
              const nuevaFila = tbody.insertRow();
              nuevaFila.innerHTML = `
<td class="px-4 py-3 whitespace-nowrap">
    <input type="text" name="piezas[${index}][referencia]" value="${pieza.REFERENCIA ?? ""}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none"
        ${!pieza.REFERENCIA ? "disabled" : ""} readonly>
</td>
<td class="px-4 py-3">
    <input type="text" name="piezas[${index}][descripcion]" value="${pieza.DESCRIP ?? ""}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none"
        ${!pieza.DESCRIP ? "disabled" : ""} readonly>
</td>
<td class="px-4 py-3 text-center">
    <input type="number" name="piezas[${index}][cantidad]" value="${pieza.UNI ?? 0}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none text-center"
        ${!pieza.UNI ? "disabled" : ""} readonly>
</td>
<td class="px-4 py-3 text-right">
    <input type="number" name="piezas[${index}][importe]" value="${pieza.IMP ?? 0}"
        class="text-sm bg-transparent border-0 text-gray-900 dark:text-white w-full p-0 focus:ring-0 focus:outline-none text-right font-semibold"
        ${!pieza.IMP ? "disabled" : ""} step="0.01" readonly>
</td>
`;
              sumaImporte += Number(pieza.IMP) || 0;
              sumaPiezas += Number(pieza.UNI) || 0;
            });
            const importeRemision = piezasFiltradas.length > 0 ? (Number(piezasFiltradas[0].TOTFAC) || 0) : 0;
            document.querySelector(`input[name="importe"]`).value =
              importeRemision.toFixed(2);
            document.querySelector(`input[name="num_partes"]`).value =
              sumaPiezas;
          });
        });

      // Disparar el evento de cambio para el primer radio (por defecto)
      const primerRadio = selectAlbaranTable.querySelector(
        'input[name="albaranSeleccionado"]:checked',
      );
      if (primerRadio) {
        primerRadio.dispatchEvent(new Event("change"));
      }
    } else {
      // Si no hay más de un albarán, ocultar la tabla de selección y limpiar selección previa
      selectAlbaranTable.classList.add("hidden");
      selectAlbaranTbody.innerHTML = "";
    }

    // Actualizar la tabla de piezas (siempre, ya sea que haya uno o varios albaranes)
    const tbody = document.querySelector("#datosAlbaranTable tbody");
    tbody.innerHTML = "";

    let sumaImporte = 0;
    let sumaPiezas = 0;
    piezas.forEach((pieza, index) => {
      const nuevaFila = tbody.insertRow();

      nuevaFila.innerHTML = `
<td class="px-4 py-3 whitespace-nowrap">
    <input type="text" name="piezas[${index}][referencia]" value="${pieza.REFERENCIA ?? ""}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none"
        ${!pieza.REFERENCIA ? "disabled" : ""} readonly>
</td>
<td class="px-4 py-3">
    <input type="text" name="piezas[${index}][descripcion]" value="${pieza.DESCRIP ?? ""}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none"
        ${!pieza.DESCRIP ? "disabled" : ""} readonly>
</td>
<td class="px-4 py-3 text-center">
    <input type="number" name="piezas[${index}][cantidad]" value="${pieza.UNI ?? 0}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none text-center"
        ${!pieza.UNI ? "disabled" : ""} readonly>
</td>
<td class="px-4 py-3 text-right">
    <input type="number" name="piezas[${index}][importe]" value="${pieza.IMP ?? 0}"
        class="text-sm bg-transparent border-0 text-gray-900 dark:text-white w-full p-0 focus:ring-0 focus:outline-none text-right font-semibold"
        ${!pieza.IMP ? "disabled" : ""} step="0.01" readonly>
</td>
`;
      sumaImporte += Number(pieza.IMP) || 0;
      sumaPiezas += Number(pieza.UNI) || 0;
    });

    const importeRemision = piezas.length > 0 ? (Number(piezas[0].TOTFAC) || 0) : 0;
    document.querySelector(`input[name="importe"]`).value =
      importeRemision.toFixed(2);
    document.querySelector(`input[name="num_partes"]`).value = sumaPiezas;
  } catch (error) {
    console.error("Error:", error);
  }
};

/**
 * Función auxiliar para renderizar la tabla de piezas del presupuesto
 */
const renderizarTablaPresupuesto = (piezas) => {
  const tbody = document.querySelector("#verPresupuestoTable tbody");
  if (!tbody) return;

  // Limpiar la tabla antes de inyectar por si hay datos previos
  tbody.innerHTML = "";

  piezas.forEach((pieza, index) => {
    const nuevaFila = tbody.insertRow();
    nuevaFila.innerHTML = `
<td class="px-1 py-1">
    <button class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${index + 1}</button>
</td>
<td class="px-1 py-1">
    <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${pieza.numero_parte ?? ""}</div>
</td>
<td class="px-1 py-1">
    <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${pieza.descripcion ?? ""}</div>
</td>
<td class="px-1 py-1">
    <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${pieza.numero_pzas_presupuesto ?? 0}</div>
</td>
<td class="px-1 py-1">
    <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${pieza.importe_unitario ?? 0}</div>
</td>
<td class="px-1 py-1">
    <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${pieza.importe_total ?? 0}</div>
</td>
<td class="px-1 py-1">
    <div class="text-black text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:text-white">${pieza.existencia ?? 0}</div>
</td>
`;
  });
};

/**
 * Función auxiliar para llenar el formulario HTML con los meta-datos del presupuesto
 */
const llenarFormularioPresupuesto = (presupuestoData) => {
  const {
    id,
    proveedor,
    siniestros = {},
  } = presupuestoData;

  const {
    numero_orden,
    numero_siniestro,
    vehiculo_info,
    cliente
  } = siniestros || {};

  const { aseguradora, marca, modelo, taller, vehiculo, vin } = vehiculo_info || {};
  const { id: id_cliente, nombre: nombre_cliente, codigo } = cliente || {};

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
    id_cliente,
    nombre_cliente,
    codigo,
  };

  const labelSpan = document.querySelector("#label_cliente_aseguradora");
  if (labelSpan) {
    if (nombre_cliente) {
      labelSpan.textContent = "Cliente";
      inputs.aseguradora = nombre_cliente; // En vales, el ID real del input en el DOM siempre es #aseguradora
    } else if (aseguradora) {
      labelSpan.textContent = "Aseguradora";
      inputs.aseguradora = aseguradora;
    } else {
      labelSpan.textContent = "Cliente";
      inputs.aseguradora = "";
    }
  }

  Object.entries(inputs).forEach(([key, val]) => {
    const inputElement = document.querySelector(`#${key}`);
    if (inputElement) {
      inputElement.value = val || ""; // Asignar el valor (con valor por defecto)
      inputElement.setAttribute("readonly", true); // Hacer el input readonly
    } else {
      console.warn(`Input con id #${key} no encontrado`);
    }
  });
};

/**
 * Función principal encargada de consultar el API y distribuir información a la vista
 */
export const findPresupuestoByNumero = async (numeroPresupuesto) => {
  try {
    const isVale = true;
    const response = await fetch(
      `${UrlProyecto}/${Perfil}/presupuestos/${numeroPresupuesto}/${isVale}`
    );

    if (!response.ok) {
      throw new Error("Error al obtener el presupuesto");
    }

    const result = await response.json();
    if (!result || result.length === 0) {
      console.warn("No se encontraron resultados para el presupuesto");
      return;
    }

    const presupuestoData = result[0];

    renderizarTablaPresupuesto(presupuestoData.piezas || []);
    llenarFormularioPresupuesto(presupuestoData);

  } catch (error) {
    console.error("Error en findPresupuestoByNumero:", error);
  }
};

/**
 * Función auxiliar para constuir y renderizar el catálogo de piezas disponibles
 */
const renderizarTablaPiezasDisponibles = (piezas) => {
  const tbody = document.querySelector("#piezasDisponiblesTable tbody");
  if (!tbody) return;

  tbody.innerHTML = ""; // Limpiar antes de inyectar

  piezas.forEach((pieza, index) => {
    const nuevaFila = tbody.insertRow();

    nuevaFila.innerHTML = `
<td class="hidden">
    <input name="id_pieza" type="number" value="${pieza.id ?? ""}" class="hidden">
</td>
<td class="px-4 py-3 whitespace-nowrap">
    <input name="numero_parte" type="text" value="${pieza.numero_parte?.trim() ?? ""}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none"
        readonly>
</td>
<td class="px-4 py-3">
    <input name="descripcion" type="text" value="${pieza.descripcion?.trim() ?? ""}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none"
        readonly>
</td>
<td class="px-4 py-3 text-center">
    <input name="cantidad" id="cantidad" type="number"
        value="${pieza.restante_para_vale ?? 0}" min="1"
        max="${pieza.restante_para_vale ?? 0}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none text-center font-bold">
</td>
<td class="px-4 py-3 text-right">
    <input name="importe_unitario" type="number"
        value="${pieza.importe_unitario ?? 0}"
        class="text-sm bg-transparent border-0 text-gray-700 dark:text-gray-300 w-full p-0 focus:ring-0 focus:outline-none text-right"
        readonly>
</td>
<td class="px-4 py-3 text-right">
    <input name="totalParte" type="number" value="0"
        class="text-sm bg-transparent border-0 text-gray-900 dark:text-white w-full p-0 focus:ring-0 focus:outline-none text-right font-semibold"
        readonly>
</td>
<td class="px-4 py-3 text-center">
    <div class="flex justify-center items-center">
        <input type="checkbox" class="filaCheckBox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 cursor-pointer">
    </div>
</td>
<td class="px-4 py-3 text-center whitespace-nowrap">
    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
        <span class="piezasFaltantes inline-flex items-center gap-1 justify-center w-full">
            <span class="numeroFaltanteVale font-bold text-blue-600 dark:text-blue-400" data-restante="${pieza.restante_para_vale}">${pieza.restante_para_vale}</span> 
            <span class="text-gray-400">/</span> 
            <span>${pieza.numero_pzas_presupuesto}</span>
        </span>
    </div>
</td>
`;
  });
};

/**
 * Función principal encargada de cargar e inyectar las piezas disponibles en la vista
 */
export const cargarPiezasDisponibles = async (numeroPresupuesto) => {
  try {
    const response = await fetch(
      `${UrlProyecto}/${Perfil}/vales/${numeroPresupuesto}/piezas-disponibles`
    );
    if (!response.ok) {
      throw new Error("Error al obtener las piezas");
    }

    const piezas = await response.json();

    if (!piezas || piezas.length === 0) {
      swalAlert({
        toast: true,
        position: "top-end",
        icon: "info",
        title: "Todas las partes del presupuesto ya están registradas en uno o varios vales.",
        theme: "auto",
        showConfirmButton: false,
        timer: 3000,
      });
      return; // Salir de la función si no hay piezas
    }

    renderizarTablaPiezasDisponibles(piezas);

  } catch (error) {
    console.error("Error al ejecutar cargarPiezasDisponibles:", error);
  }
};


/**
 * Actualiza la visualización de piezas faltantes dinámicamente según la cantidad ingresada
 */
const actualizarPiezasFaltantes = () => {
  const tbody = document.querySelector("#piezasDisponiblesTable tbody");
  if (!tbody) return;

  Array.from(tbody.rows).forEach((fila) => {
    const checkbox = fila.querySelector('input[type="checkbox"]');
    const piezaInput = fila.querySelector('input[name="cantidad"]');
    const spanFaltante = fila.querySelector(".numeroFaltanteVale");

    if (spanFaltante && piezaInput) {
      let base = parseFloat(spanFaltante.dataset.restante);
      if (isNaN(base)) base = 1;

      let actual = 0;
      if (checkbox && checkbox.checked) {
        actual = parseFloat(piezaInput.value);
        if (isNaN(actual)) actual = 1;
      }

      spanFaltante.textContent = base - actual;
    }
  });
};

/**
 * Calcula los totales (por fila y globales) según las piezas seleccionadas y su importe
 */
const actualizarTotalesVale = () => {
  const tbody = document.querySelector("#piezasDisponiblesTable tbody");
  if (!tbody) return;

  const subtotalInput = document.querySelector('input[name="subtotal"]');
  const ivaInput = document.querySelector('input[name="iva"]');
  const totalInput = document.querySelector('input[name="total"]');
  const totalParte = document.querySelectorAll('input[name="totalParte"]');

  let subtotal = 0;
  let iva = 0;
  let total = 0;

  // Resetear todos los totalParte a 0 antes de calcular
  totalParte.forEach((input) => (input.value = 0));

  // Iterar sobre las filas de la tabla
  Array.from(tbody.rows).forEach((fila, i) => {
    const checkbox = fila.querySelector('input[type="checkbox"]');
    const cantidad = parseFloat(fila.cells[3].querySelector("input").value) || 0;
    const importeUnitario = parseFloat(fila.cells[4].querySelector("input").value) || 0;

    if (checkbox && checkbox.checked) {
      const totalFila = cantidad * importeUnitario;
      subtotal += totalFila; // Sumar al subtotal
      iva = subtotal * 0.16; // Calcular IVA
      total = subtotal + iva;
      if (totalParte[i]) totalParte[i].value = totalFila.toFixed(2); // Total de esta fila
    }
  });

  if (subtotalInput) subtotalInput.value = subtotal.toFixed(2); // Actualizar el input de subtotal
  if (ivaInput) ivaInput.value = iva.toFixed(2); // Actualizar el input de iva
  if (totalInput) totalInput.value = total.toFixed(2); // Actualizar el input de total
};

export const calcularCantidades = () => {
  actualizarPiezasFaltantes();
  actualizarTotalesVale();
};

export const agregarVale = async (e, presupuesto) => {
  e.preventDefault();
  const numero_vale = document.querySelector("#numero_vale").value;
  const fecha_vale = document.querySelector("#fecha_vale").value;
  const fecha_promesa = document.querySelector("#fecha_promesa").value;

  const subtotal = document.querySelector("#subtotal").value;
  const iva = document.querySelector("#iva").value;
  const total = document.querySelector("#total").value;

  const tbody = document.querySelector("#piezasDisponiblesTable tbody");
  const filasSeleccionadas = [];

  // const regex = /^[0-9]+$/;

  // if (!regex.test(numero_vale)) {
  //   swalAlert({
  //     title: "Error",
  //     text: "El número de vale debe ser un número entero",
  //     icon: "error",
  //     confirmButtonText: "Aceptar",
  //     theme: "auto",
  //   });
  //   return;
  // }

  if (numero_vale)
    // Iterar sobre las filas de la tabla
    for (const fila of tbody.rows) {
      const checkbox = fila.querySelector('input[type="checkbox"]');
      if (checkbox && checkbox.checked) {
        // Si el checkbox está seleccionado, recoger los datos de la fila
        const id_pieza = fila.cells[0].querySelector("input").value;
        const numero_parte = fila.cells[1].querySelector("input").value;
        // const descripcion = fila.cells[1].querySelector('input').value;
        const cantidad_pieza = fila.cells[3].querySelector("input").value;
        // const importeUnitario = fila.cells[3].querySelector('input').value;

        filasSeleccionadas.push({
          id_pieza,
          numero_parte,
          cantidad_pieza,
        });
      }
    }

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres agregar este vale?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, agregar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content");

        const response = await fetch(
          `${UrlProyecto}/${Perfil}/vales/${presupuesto}/agregar-vale`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
              numero_vale,
              fecha_vale,
              fecha_promesa,
              subtotal,
              iva,
              total,
              filasSeleccionadas,
            }),
          },
        );

        const data = await response.json();

        if (!response.ok || data.success === false) {
          return swalAlert({
            title: data.title || "Error",
            text: data.message || "No se pudo agregar el vale.",
            icon: data.icon || "error",
            confirmButtonText: "Aceptar",
            theme: "auto",
          });
        }

        console.log(data);

        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: "Aceptar",
          theme: "auto",
          timer: 2000,
        }).then(async () => {
          try {
            const idVale = data?.vale?.id;
            const linkVale = `${UrlProyecto}/${Perfil}/vales/ver?numVale=${numero_vale}${idVale ? `&idVale=${idVale}` : ""}`;
            showSendingMailAlert();
            const responseMail = await fetch(
              `${UrlProyecto}/${Perfil}/vales/mail-vale-creado`,
              {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                  "X-CSRF-TOKEN": token,
                },
                body: JSON.stringify({
                  numero_vale,
                  numero_presupuesto: presupuesto,
                  link: linkVale,
                }),
              },
            );

            if (!responseMail.ok) {
              throw new Error("Error al enviar el correo de vale creado");
            }

            window.location.href = `${UrlProyecto}/${Perfil}/vales/ver?numVale=${numero_vale}${idVale ? `&idVale=${idVale}` : ""}`;
          } catch (error) {
            console.error("Error al enviar el correo de vale creado:", error);
            swalAlert({
              title: "Error",
              text: "No se pudo enviar el correo de vale creado. Intenta nuevamente.",
              icon: "error",
              confirmButtonText: "Aceptar",
              theme: "auto",
            });
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




// const checkAlbaran = () => {
//   const
// }
export const asignarEntrada = () => {
  const estadoInput = document.getElementById("estado");

  if (estadoInput.value == "Completado") {
    document.querySelector("#cerrarModalButton").click();

    return swalAlert({
      title: "Error",
      text: "No se puede asignar una entrada a un vale completado.",
      icon: "error",
      confirmButtonText: "Aceptar",
      theme: "auto",
    });
  }

  const proveedor = document.querySelector("#proveedor").value;

  // Validación simplificada: solo verificamos si hay albaranes en general
  if (proveedor === "CHEVROLET" && piezasAlbaranesGlobal.length === 0) {
    const cerrarBtn = document.querySelector(".cerrarModalEntrada");
    if (cerrarBtn) cerrarBtn.click();

    swalAlert({
      title: "Sin Albaranes",
      text: "Aún no ha sido asignado ningún albarán a este vale. Es obligatorio asignar los albaranes antes de las entradas.",
      icon: "warning",
      confirmButtonText: "Aceptar",
      theme: "auto",
    });
    return;
  } else {
    const $modal = $("#asignarEntrada-modal");
    if ($modal.length) {
      $modal.draggable({
        handle: ".modal-header",
        containment: false,
      });
    }
    document.querySelector("#numero_orden_asignacion").value =
      document.querySelector("#numero_orden").value;
    document.querySelector("#numero_siniestro_asignacion").value =
      document.querySelector("#numero_siniestro").value;
    document.querySelector("#numero_presupuesto_asignacion").value =
      document.querySelector("#numero_presupuesto").value;
  }
};

export const submitAsignarEntrada = async (event, numVale) => {
  const formData = new FormData(event.target);
  const idVale = document.getElementById("id_vale")?.value;
  if (idVale) {
    formData.append("id_vale", idVale);
  }
  const proveedor = document.querySelector("#proveedor")?.value || "";

  // Validación final al enviar: que lo que se envía tenga albarán
  if (proveedor === "CHEVROLET") {
    const piezasParaAsignar = [];
    for (let [key, value] of formData.entries()) {
      if (key.includes("[numero_parte]")) {
        piezasParaAsignar.push(String(value).trim());
      }
    }

    const piezasSinAlbaran = piezasParaAsignar.filter(refEntrada => {
      return !piezasAlbaranesGlobal || !piezasAlbaranesGlobal.some(pa => {
        const refAlb = pa.REFERENCIA ? String(pa.REFERENCIA).trim() : "";
        return refAlb === refEntrada;
      });
    });

    if (piezasSinAlbaran.length > 0) {
      return swalAlert({
        title: "Piezas sin albarán",
        html: `No puede asignar esta entrada porque las siguientes piezas no tienen un albarán registrado:<br><br><strong>${piezasSinAlbaran.join(", ")}</strong>`,
        icon: "error",
        confirmButtonText: "Corregir",
        theme: "auto",
      });
    }
  }

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres asignar esta entrada al vale?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, asignar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    console.log("Usuario confirmó:", result.isConfirmed);
    if (result.isConfirmed) {
      try {
        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content");

        const response = await fetch(
          `${UrlProyecto}/${Perfil}/vales/asignar-entrada/${numVale}`,
          {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": csrfToken,
            },
            body: formData,
          },
        );

        if (!response.ok) {
          throw new Error("Error en la respuesta del servidor");
        }
        const data = await response.json();

        try {
          const numeroEntrada = formData.get("entrada") || "";
          const idEntrada = data.entrada.id;
          const linkEntrada = `${UrlProyecto}/${Perfil}/entradas/detalle/${idEntrada}`;
          showSendingMailAlert();
          const responseMail = await fetch(
            `${UrlProyecto}/${Perfil}/entradas/mail-entrada-asignada`,
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
              },
              body: JSON.stringify({
                numEntrada: numeroEntrada,
                numVale: numVale,
                idVale: idVale,
                link: linkEntrada,
              }),
            },
          );

          if (!responseMail.ok) {
            throw new Error("Error al enviar el correo de entrada asignada");
          }

          const dataMail = await responseMail.json();
          swalAlert({
            title: dataMail.title,
            text: dataMail.message,
            icon: dataMail.icon,
            confirmButtonText: "Aceptar",
            theme: "auto",
            timer: 2000,
          });
        } catch (error) {
          console.error(
            "Error al enviar el correo de entrada asignada:",
            error,
          );
          swalAlert({
            title: "Error",
            text: "No se pudo enviar el correo de entrada asignada. Intenta nuevamente.",
            icon: "error",
            confirmButtonText: "Aceptar",
            theme: "auto",
          });
        }

        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: "Aceptar",
          theme: "auto",
          timer: 2000,
        }).then(() => {
          location.reload();
        });
      } catch (error) {
        console.error("Error al asignar la entrada:", error);
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

export const asignarAlbaran = () => {
  const $modal = $("#asignarAlbaran-modal");
  if ($modal.length) {
    $modal.draggable({
      handle: ".modal-header", // El header del modal es el área de arrastre
      containment: false, // Permite mover el modal libremente, sin restricciones
    });
  }

  // const estado = document.querySelector("#estado");
  // if (document.getElementById("estado").value == "Completado") {
  //   document.querySelector("#cerrarModalButton").click();

  //   return swalAlert({
  //     title: "Error",
  //     text: "No se puede asignar un albarán a un vale completado.",
  //     icon: "error",
  //     confirmButtonText: "Aceptar",
  //     theme: "auto",
  //   });
  // }

  document.querySelector("#numero_orden_asignacion").value =
    document.querySelector("#numero_orden").value;
  document.querySelector("#numero_siniestro_asignacion").value =
    document.querySelector("#numero_siniestro").value;
  document.querySelector("#numero_presupuesto_asignacion").value =
    document.querySelector("#numero_presupuesto").value;
};

export const submitAsignarAlbaran = async (event, numVale) => {
  const formData = new FormData(event.target);
  const idVale = document.getElementById("id_vale")?.value;
  if (idVale) {
    formData.append("id_vale", idVale);
  }

  // Obtener el taller del vale
  const taller = document.querySelector("#taller")?.value || "";
  formData.append("taller", taller);

  // console.log(Object.fromEntries(formData));


  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres asignar este albaran al vale?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, asignar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content");

        const response = await fetch(
          `${UrlProyecto}/${Perfil}/vales/asignar-albaran/${numVale}`,
          {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": csrfToken,
            },
            body: formData,
          },
        );

        if (!response.ok) {
          throw new Error("Error en la respuesta del servidor");
        }

        const data = await response.json();

        // Enviar correo después de asignar el albarán correctamente
        try {
          const numeroAlbaran = formData.get("albaran") || "";
          // Construir el enlace al albarán
          const linkAlbaran = `${UrlProyecto}/${Perfil}/albaranes/ver?numAlbaran=${numeroAlbaran}`;
          showSendingMailAlert();
          const responseMail = await fetch(
            `${UrlProyecto}/${Perfil}/albaranes/mail-albaran-asignado`,
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
              },
              body: JSON.stringify({
                numVale: numVale,
                idVale: idVale,
                numAlbaran: numeroAlbaran,
                link: linkAlbaran,
              }),
            },
          );

          if (!responseMail.ok) {
            throw new Error("Error al enviar el correo de albarán asignado");
          }

          // Si el correo se envió correctamente, mostrar un SweetAlert de éxito
          const dataMail = await responseMail.json();
          swalAlert({
            title: dataMail.title,
            text: dataMail.message,
            icon: dataMail.icon,
            confirmButtonText: "Aceptar",
            theme: "auto",
            timer: 2000,
          });
        } catch (error) {
          console.error(
            "Error al enviar el correo de albarán asignado:",
            error,
          );
          swalAlert({
            title: "Error",
            text: "No se pudo enviar el correo de albarán asignado. Intenta nuevamente.",
            icon: "error",
            confirmButtonText: "Aceptar",
            theme: "auto",
          });
        }

        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: "Aceptar",
          theme: "auto",
          timer: 2000,
        }).then(() => {
          location.reload();
        });
      } catch (error) {
        console.error("Error al asignar el albaran:", error);
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

export const validarMatchAlbaran = async (event, numVale) => {

  event.preventDefault();
  const formData = new FormData(event.target);
  const idVale = document.getElementById("id_vale")?.value;
  if (idVale) {
    formData.append("id_vale", idVale);
  }

  const response = await fetch(
    `${UrlProyecto}/${Perfil}/vales/validar-match-albaran/${numVale}`,
    {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": token,
      },
      body: formData,
    },
  );

  const data = await response.json();

  const match = data.match;

  console.log("data", data);
  console.log("match", match);
  if (match) {
    return match;
  } else {
    swalAlert({
      title: data.title,
      html: data.html,
      icon: data.icon,
      confirmButtonText: "Aceptar",
      theme: "auto",
      timer: 1000000,
    });
    return match;
  }
};

export const validarMatchEntrada = async (event, numVale, proveedor) => {

  event.preventDefault();
  const formData = new FormData(event.target);
  const idVale = document.getElementById("id_vale")?.value;
  if (idVale) {
    formData.append("id_vale", idVale);
  }

  const response = await fetch(
    `${UrlProyecto}/${Perfil}/vales/validar-match-entrada/${numVale}/${proveedor}`,
    {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": token,
      },
      body: formData,
    },
  );

  const data = await response.json();

  const match = data.match;

  if (match) {
    return match;
  } else {
    swalAlert({
      title: data.title,
      html: data.html,
      icon: data.icon,
      confirmButtonText: "Aceptar",
      theme: "auto",
      timer: 1000000,
    });
    return match;
  }
};

export const consultarDatosByNumeroParte = async (numeroParte, indice) => {
  const codigo = document.querySelector("#codigo")?.value || "";
  const response = await fetch(
    `${UrlProyecto}/${Perfil}/presupuestos/consultar-descripcion?numeroParte=${numeroParte}&codigo=${codigo}`,
    {
      method: "GET",
    },
  );
  const dataW32 = await response.json();

  if (!dataW32 || dataW32.length === 0) {
    if (indice === undefined) {
      // Limpiar campos del modal de complemento si no hay datos
      const descripcionW32Input = document.querySelector(
        `input[name="descripcion_w32"]`,
      );
      const descripcionInput = document.querySelector(
        `#addComplementoForm #descripcion`,
      );
      const existenciaInput = document.querySelector(
        `input[name="existencia"]`,
      );
      const importeUnitarioInput = document.querySelector(
        `input[name="importe_unitario"]`,
      );
      const importeTotalInput = document.querySelector(
        `input[name="importe_total"]`,
      );

      if (descripcionW32Input) descripcionW32Input.value = "";
      if (descripcionInput) descripcionInput.value = "";
      if (existenciaInput) existenciaInput.value = "";
      if (importeUnitarioInput) importeUnitarioInput.value = "";
      if (importeTotalInput) importeTotalInput.value = "";
    }
    return false;
  }

  if (indice !== undefined) {
    // Si hay índice, estamos modificando una fila del vale
    const tabla = document.querySelector("#verValeTable tbody");
    if (!tabla) return false;
    const fila = tabla.rows[indice];
    if (!fila) return false;

    const celdaImporteUnitario = fila.cells[4];
    const inputImporteTotal = fila.querySelector(
      'input[name="importe_total[]"]',
    );
    const inputCantidad = fila.querySelector('input[name="cantidad[]"]');

    if (celdaImporteUnitario) {
      celdaImporteUnitario.textContent = dataW32[0].PVP1;
    }

    if (inputImporteTotal && inputCantidad) {
      const cantidad = parseFloat(inputCantidad.value) || 0;
      const total = (dataW32[0].PVP1 * cantidad).toFixed(2);
      inputImporteTotal.value = total;
    }
  } else {
    // Si no hay índice, es el modal de Agregar Complemento
    const descripcionW32Input = document.querySelector(
      `input[name="descripcion_w32"]`,
    );
    const existenciaInput = document.querySelector(`input[name="existencia"]`);
    const importeUnitarioInput = document.querySelector(
      `input[name="importe_unitario"]`,
    );
    const importeTotalInput = document.querySelector(
      `input[name="importe_total"]`,
    );
    const cantidadInput = document.querySelector(`input[name="cantidad"]`);

    if (descripcionW32Input) descripcionW32Input.value = dataW32[0].DESCRIP;
    if (existenciaInput) existenciaInput.value = dataW32[0].STOCK;
    if (importeUnitarioInput) importeUnitarioInput.value = dataW32[0].PVP1;

    if (cantidadInput && importeTotalInput) {
      const cantidad = parseFloat(cantidadInput.value) || 0;
      const total = (dataW32[0].PVP1 * cantidad).toFixed(2);
      importeTotalInput.value = total;
    }
  }
  return true;
};

// export const calcularTotalComplemento = () => {
//   const cantidadInput = document.querySelector("#addComplementoForm #cantidad");
//   const importeInput = document.querySelector("#addComplementoForm #importe");
//   const importeTotalInput = document.querySelector(
//     "#addComplementoForm #importe_total",
//   );

//   if (cantidadInput && importeInput && importeTotalInput) {
//     const cantidad = parseFloat(cantidadInput.value) || 0;
//     const precio = parseFloat(importeInput.value) || 0;
//     importeTotalInput.value = (precio * cantidad).toFixed(2);
//   }
// };

export const modificarPartes = async (e) => {
  const index = e.dataset.conteo;
  // Obtener el número de parte de la fila correspondiente
  const tabla = document.querySelector("#verValeTable tbody");
  if (!tabla) return;

  const fila = tabla.rows[index];
  if (!fila) return;

  // Selecciona la celda del número de parte
  const celdaNumeroParte = fila.cells[0];

  // Si la fila ya está en modo edición, no hacer nada (revisamos la de numeroParte)
  if (celdaNumeroParte?.querySelector("input")) {
    return; // Ya está en modo edición, salir
  }

  document.querySelector("#modificarPartesButton")?.classList.remove("hidden");
  document.querySelector("#cancelarModificacionPartesButton")?.classList.remove("hidden");

  // Obtener el valor actual del número de parte
  const numeroParteActual = celdaNumeroParte.querySelector(".numeroParte")
    ? celdaNumeroParte.querySelector(".numeroParte").textContent.trim()
    : "";

  // Agregar un input oculto para enviar el número de parte original (antes de editar)
  if (!fila.querySelector('input[name="numero_parte_original[]"]')) {
    const inputHidden = document.createElement("input");
    inputHidden.type = "hidden";
    inputHidden.name = "numero_parte_original[]";
    inputHidden.value = numeroParteActual;
    fila.appendChild(inputHidden);
  }

  // Reemplazar el contenido de la celda con input tipo array
  celdaNumeroParte.innerHTML = `
<input type="text" name="numero_parte[]" value="${numeroParteActual}"
    class="numeroParte inputCantidadVale w-20 px-1 py-1 border rounded bg-white dark:bg-gray-800" data-conteo="${index}" style="text-align:center;" />
`;

  const celdaDescripcion = fila.cells[1];
  const celdaCantidad = fila.cells[2];
  const celdaImporteUnitario = fila.cells[4];
  const celdaImporteTotal = fila.cells[5];

  if (!celdaDescripcion || !celdaCantidad || !celdaImporteUnitario || !celdaImporteTotal) return;

  const descripcionActual = celdaDescripcion.textContent.trim();
  const cantidadActual = celdaCantidad.textContent.trim();
  const importeUnitario = parseFloat(celdaImporteUnitario.textContent.trim()) || 0;

  celdaDescripcion.innerHTML = `
<input type="text" name="descripcion[]" value="${descripcionActual}"
    class="inputDescripcionVale w-full px-1 py-1 border rounded bg-white dark:bg-gray-800" />
`;

  celdaCantidad.innerHTML = `
<input type="number" name="cantidad[]" min="0" step="1" value="${cantidadActual}"
    class="inputCantidadVale w-20 px-1 py-1 border rounded bg-white dark:bg-gray-800" style="text-align:center;" />
`;

  const importeTotalInicial = (importeUnitario * (parseFloat(cantidadActual) || 0)).toFixed(2);

  celdaImporteTotal.innerHTML = `
<input type="number" name="importe_total[]" min="0" step="0.01" value="${importeTotalInicial}"
    class="inputImporteTotalVale w-24 px-1 py-1 border rounded bg-white dark:bg-gray-800" style="text-align:center;"
    readonly />
`;

  const inputCantidad = celdaCantidad.querySelector("input");
  const inputImporteTotal = celdaImporteTotal.querySelector("input");

  inputCantidad.addEventListener("input", function () {
    const nuevaCantidad = parseFloat(this.value) || 0;
    const precioActual = parseFloat(celdaImporteUnitario.textContent.trim()) || 0;
    const nuevoImporteTotal = (precioActual * nuevaCantidad).toFixed(2);
    if (inputImporteTotal) {
      inputImporteTotal.value = nuevoImporteTotal;
    }
  });


  // Enfocar el input de número de parte para mejor UX
  const inputNumeroParte = celdaNumeroParte.querySelector("input");
  if (inputNumeroParte) {
    inputNumeroParte.focus();
  }
};

function obtenerNumeroParteVale(e) {
  const numParte = e.dataset.nparte;
  // Obtener el número de vale de la URL
  const urlParams = new URLSearchParams(window.location.search);
  const numVale = urlParams.get("numVale");
  const idVale =
    document.getElementById("id_vale")?.value || urlParams.get("idVale");
  return { numParte, numVale, idVale }
}

export const notificarEliminarParteAut = async (e) => {
  const { numParte, numVale, idVale } = obtenerNumeroParteVale(e);

  // Verificar si la pieza tiene albaranes asignados
  const tieneAlbaran = piezasAlbaranesGlobal?.some(pa => {
    const ref = pa.REFERENCIA ? String(pa.REFERENCIA).trim() : "";
    return ref === String(numParte).trim();
  });

  const textoAviso = tieneAlbaran
    ? "Esta parte tiene albaranes o entradas asignadas. Deben liberarse antes de poder eliminarla. ¿Deseas continuar con la solicitud?"
    : "¿Quieres solicitar la eliminación de esta parte?";

  swalAlert({
    title: "¿Estás seguro?",
    text: textoAviso,
    icon: tieneAlbaran ? "warning" : "question",
    input: 'textarea',
    inputLabel: 'Motivo de la solicitud (opcional)',
    inputPlaceholder: 'Escribe el motivo de la solicitud...',
    inputAttributes: {
      'aria-label': 'Motivo de la solicitud'
    },
    showCancelButton: true,
    confirmButtonText: "Sí, solicitar",
    cancelButtonText: "No, cancelar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const motivo = result.value || ''; // Obtener el motivo (vacío si no se escribió nada)

        showSendingMailAlert();
        const response = await fetch(`${UrlProyecto}/${Perfil}/vales/solicitar-eliminar-parte`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify({
            numParte,
            numVale,
            id_vale: idVale,
            motivo
          })
        });

        if (!response.ok) {
          throw new Error('Error en la solicitud');
        }

        const data = await response.json();

        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: 'Aceptar',
          theme: 'auto',
        });
      } catch (error) {
        console.error('Error:', error);
        swalAlert({
          title: 'Error',
          text: 'No se pudo procesar la solicitud. Intenta nuevamente.',
          icon: 'error',
          confirmButtonText: 'Aceptar',
          theme: 'auto',
        });
      }
    }
  });
}

export const rechazarSolicitudEliminacion = async (e) => {
  const { numParte, numVale, idVale } = obtenerNumeroParteVale(e);

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres rechazar la solicitud de eliminación de esta parte?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, rechazar",
    cancelButtonText: "No, cancelar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        showSendingMailAlert();
        const response = await fetch(`${UrlProyecto}/${Perfil}/vales/rechazar-solicitud-parte`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify({
            numParte,
            numVale,
            id_vale: idVale
          })
        });

        if (!response.ok) {
          throw new Error('Error en la solicitud');
        }

        const data = await response.json();

        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: 'Aceptar',
          theme: 'auto',
        }).then(() => {
          if (data.success) {
            location.reload();
          }
        });
      } catch (error) {
        console.error('Error:', error);
        swalAlert({
          title: 'Error',
          text: 'No se pudo procesar la solicitud. Intenta nuevamente.',
          icon: 'error',
          confirmButtonText: 'Aceptar',
          theme: 'auto',
        });
      }
    }
  });
}
export const eliminarPartes = async (e) => {
  const { numParte, numVale, idVale } = obtenerNumeroParteVale(e);

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres eliminar esta parte?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content");

        const formData = new FormData();
        formData.append("numVale", numVale);
        formData.append("numParte", numParte);
        if (idVale) {
          formData.append("id_vale", idVale);
        }

        const response = await fetch(`${UrlProyecto}/${Perfil}/vales/eliminar-parte`, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": csrfToken,
          },
          body: formData,
        });

        if (!response.ok) {
          throw new Error("Error en la respuesta del servidor");
        }

        const data = await response.json();

        // Mostrar SweetAlert con la respuesta recibida del backend
        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: "Aceptar",
          theme: "auto",
        }).then((result) => {
          if (data.success === true) {
            location.reload();
          }
        });
      } catch (error) {
        console.error("Error al eliminar la parte:", error);
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

export const submitModificarPartes = async (e, numVale) => {
  e.preventDefault();

  //valicadion numero de vale solo enteros
  console.log(e);

  const formData = new FormData(e.target);
  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres modificar este vale?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, añadir",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content");

        const response = await fetch(`${UrlProyecto}/${Perfil}/vales/modificar-parte`, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": csrfToken,
          },
          body: formData,
        });

        if (!response.ok) {
          throw new Error("Error en la respuesta del servidor");
        }

        const data = await response.json();

        // Mostrar SweetAlert con la respuesta del backend
        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: "Aceptar",
          theme: "auto",
        }).then((result) => {
          if (data.success === true) {
            location.reload();
          }
        });
      } catch (error) {
        console.error("Error al modificar las partes:", error);
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

export const addComplemento = async (event, numVale) => {
  event.preventDefault();
  const formData = new FormData(event.target);
  const idVale = document.getElementById("id_vale")?.value;
  if (idVale) {
    formData.append("id_vale", idVale);
  }

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres añadir este complemento al vale?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, añadir",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content");

        const response = await fetch(
          `${UrlProyecto}/${Perfil}/vales/agregar-complemento/${numVale}`,
          {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": csrfToken,
            },
            body: formData,
          },
        );

        if (!response.ok) {
          throw new Error("Error en la respuesta del servidor");
        }

        const data = await response.json();

        const descripcion = formData.get("descripcion");
        const cantidad = formData.get("cantidad");

        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          confirmButtonText: "Aceptar",
          theme: "auto",
          timer: 2000,
        }).then(async () => {
          await enviarCorreoComplemento(numVale, descripcion, cantidad);
        });
      } catch (error) {
        console.error("Error al agregar el complemento:", error);
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

export const calcularTotalComplemento = async (form, targetId) => {
  if (!form) return;

  const numParteInput = form.querySelector("#numero_parte");
  const cantidadInput = form.querySelector("#cantidad");
  const descripcionInput = form.querySelector("#descripcion");
  const importeInput = form.querySelector("#importe");
  const importeTotalInput = form.querySelector("#importe_total");

  const numParte = numParteInput.value.trim();
  const cantidad = cantidadInput.value.trim();

  // Validaciones de limpieza si se vacía el número de parte
  if (!numParte) {
    if (descripcionInput) descripcionInput.value = "";
    if (importeInput) importeInput.value = "";
    if (importeTotalInput) importeTotalInput.value = "";
    const descW32 = form.querySelector("#descripcion_w32");
    const stock = form.querySelector("#existencia");
    if (descW32) descW32.value = "";
    if (stock) stock.value = "";
    return;
  }

  // Escenario 1: Cambia el número de parte (Consulta a w32)
  if (targetId === "numero_parte") {
    const encontrado = await consultarDatosByNumeroParte(numParte);

    // Obtener descripción de w32 (campo oculto)
    const descW32 = form.querySelector("#descripcion_w32");

    if (encontrado && descW32 && descW32.value) {
      descripcionInput.value = descW32.value;
    } else {
      // Si no se encuentra o el resultado es [], limpiar campos detallados
      descripcionInput.value = "";
      importeInput.value = "";
      importeTotalInput.value = "";
    }
  }

  // Escenario 2: Cambia la cantidad o si ya tenemos precio
  if (targetId === "cantidad" || (numParte && importeInput.value)) {
    if (!cantidad) {
      importeTotalInput.value = "";
    } else {
      const precioUnitario = parseFloat(importeInput.value) || 0;
      const cant = parseFloat(cantidad) || 0;
      importeTotalInput.value = (precioUnitario * cant).toFixed(2);
    }
  }
};

export const enviarCorreoComplemento = async (
  numVale,
  descripcion,
  cantidad,
) => {
  const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

  try {
    const idVale =
      document.getElementById("id_vale")?.value ||
      new URLSearchParams(window.location.search).get("idVale");
    const link = `${UrlProyecto}/${Perfil}/vales/ver?numVale=${numVale}${idVale ? `&idVale=${idVale}` : ""}`;

    showSendingMailAlert();
    const response = await fetch(`${UrlProyecto}/${Perfil}/vales/mail-complemento`, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ numVale, descripcion, cantidad, link, idVale }),
    });

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
      location.reload();
    });
  } catch (error) {
    console.error("Error al agregar el complemento:", error);
    swalAlert({
      title: "Error",
      text: "No se pudo conectar con el servidor. Intenta nuevamente.",
      icon: "error",
      confirmButtonText: "Aceptar",
      theme: "auto",
    });
  }
};

export const pedirModificacion = async (e, numVale) => {
  const idVale =
    document.getElementById("id_vale")?.value ||
    new URLSearchParams(window.location.search).get("idVale");
  swalAlert({
    title: "Solicitar modificación de partes",
    text: "Por favor, describe la modificación que necesitas realizar.",
    input: "textarea",
    inputLabel: "Mensaje:",
    inputPlaceholder: "Escribe aquí el motivo o detalle de la modificación...",
    inputAttributes: {
      "aria-label": "Mensaje para solicitar modificación",
    },
    showCancelButton: true,
    confirmButtonText: "Enviar solicitud",
    cancelButtonText: "Cancelar",
    theme: "auto",
    buttonsStyling: true,
    preConfirm: (mensaje) => {
      if (!mensaje || mensaje.trim() === "") {
        Swal.showValidationMessage("Debes escribir un mensaje.");
        return false;
      }
      return mensaje;
    },
  }).then(async (result) => {
    if (result.isConfirmed && result.value) {
      try {
        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content");
        const mensaje = result.value;

        showSendingMailAlert();
        const response = await fetch(
          `${UrlProyecto}/${Perfil}/vales/mail-pedir-modificacion`,
          {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": csrfToken,
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              mensaje,
              num_vale: numVale,
              id_vale: idVale,
              link: `${UrlProyecto}/${Perfil}/vales/ver?numVale=${numVale}${idVale ? `&idVale=${idVale}` : ""}`,
            }),
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
        });
      } catch (error) {
        console.error("Error al solicitar la modificación:", error);
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

export const recargarTabla = async () => {
  $("#valesTable").DataTable().ajax.reload();
};

export const limpiarEstado = async () => {
  $('input[name="filtroEstado"]').prop("checked", false);
  await recargarTabla();
};

export const buscar = async (e) => {
  $("#valesTable").DataTable().search(e.target.value).draw();
};

export const numVale = async (e) => {
  e.preventDefault();
  const button = e.target.closest(".btnAbrirVale");
  if (!button) return;

  const numVale =
    button.dataset.numVale || button.getAttribute("data-num-vale");
  const idVale =
    button.dataset.idVale || button.getAttribute("data-id-vale");
  if (!numVale) {
    console.error("No se encontró el número de vale");
    return;
  }

  try {
    const query = idVale ? `?idVale=${encodeURIComponent(idVale)}` : "";
    const response = await fetch(
      `${UrlProyecto}/${Perfil}/vales/existsEntradaByValeId/${numVale}${query}`,
    );
    const result = await response.json();
    window.open(`${UrlProyecto}/${Perfil}/vales/ver?numVale=${numVale}${idVale ? `&idVale=${idVale}` : ""}`, '_blank');
  } catch (error) {
    console.error("Error al verificar la entrada:", error);
    window.open(`${UrlProyecto}/${Perfil}/vales/ver?numVale=${numVale}${idVale ? `&idVale=${idVale}` : ""}`, '_blank');
  }
};

export const verEntradas = async () => {
  // alert();
  const obtenerParametrosUrl = (nombre) => {
    const url = window.location.href;
    const regex = new RegExp("[?&]" + nombre + "(=([^&#]*)|&|#|$)");
    const resultados = regex.exec(url);
    console.log(resultados);

    if (!resultados) return null;
    if (!resultados[2]) return "";
    return decodeURIComponent(resultados[2].replace(/\+/g, " "));
  };

  const numVale = obtenerParametrosUrl("numVale");
  const idVale = obtenerParametrosUrl("idVale");

  let result;
  try {
    const query = idVale ? `?idVale=${encodeURIComponent(idVale)}` : "";
    const response = await fetch(
      `${UrlProyecto}/${Perfil}/vales/existsEntradaByValeId/${numVale}${query}`,
    );
    result = await response.json();
    console.log(result.entradas);

    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }

    // const responseText = await response.text();

    // result = responseText ? JSON.parse(responseText) : null;
  } catch (error) {
    console.error("Error al obtener las entradas:", error);
    const contentModalEntradas = document.querySelector(
      ".content-modal-entradas",
    );
    contentModalEntradas.innerHTML =
      '<p class="text-red-500">Error al cargar las entradas. Intente nuevamente.</p>';
    return;
  }

  const contentModalEntradas = document.querySelector(
    ".content-modal-entradas",
  );
  contentModalEntradas.innerHTML = "";

  if (!result.entradas) {
    contentModalEntradas.innerHTML = "<p>Este vale no cuenta con entradas.</p>";
    return;
  }
  const entradas = Array.isArray(result.entradas)
    ? result.entradas
    : [result.entradas];

  console.log(entradas);
  if (entradas.length === 0) {
    contentModalEntradas.innerHTML = "<p>Este vale no cuenta con entradas.</p>";
    return;
  }
  entradas.forEach((entrada) => {
    if (!entrada) return;
    const div = document.createElement("div");
    div.className = "mb-1 p-2";
    div.innerHTML = `
<span class="inline-flex items-baseline">
    <span class="font-medium mr-2">Número de entrada:</span>
    <a href="${UrlProyecto}/${Perfil}/entradas/detalle/${entrada.id}" target="_blank"
        class="hover:underline text-blue-500">${entrada.numero_entrada || "N/A"
      }</a>
</span>
`;
    contentModalEntradas.appendChild(div);
  });
};
// export const asignarFaltantes = () => { };
