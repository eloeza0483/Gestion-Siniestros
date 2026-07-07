import Presupuestos from "../class/Presupuestos.js"
import { UrlProyecto, Perfil, token, folio, swalAlert } from "./const.js";


const P = new Presupuestos();

const ESTADOS_LABEL = {
  SinCotizar: 'Sin Cotizar',
  Pendiente: 'Pendientes',
  Cotizado: 'Cotizados',
  Cancelado: 'Cancelados',
};

const getFiltroValue = () => document.querySelector('input[name="filtroEstado"]:checked')?.value;

const setFiltroGuardado = () => {
  const valor = getFiltroValue();
  if (valor) localStorage.setItem('filtroPresupuestos', valor);
};

const initFiltro = () => {
  let guardado = localStorage.getItem('filtroPresupuestos');
  if (guardado) {
    const input = document.querySelector(`input[name="filtroEstado"][value="${guardado}"]`);
    if (input) input.checked = true;
  }
};

const actualizarTitulo = () => {
  const span = document.querySelector('.presupuestosSpan');
  const filtro = getFiltroValue() || localStorage.getItem('filtroPresupuestos');
  if (span) {
    span.textContent = filtro ? `Presupuestos ${ESTADOS_LABEL[filtro] || filtro}` : 'Presupuestos';
  }
};

export function cargarTD() {
  initFiltro();
  actualizarTitulo();

  // Escuchar cambios de filtro para guardar y recargar
  document.querySelectorAll('input[name="filtroEstado"]').forEach(radio => {
    radio.addEventListener('change', () => {
      setFiltroGuardado();
      actualizarTitulo();
      recargarTabla();
    });
  });

  P.TD();
}

export const recargarTabla = async () => {
  if ($.fn.DataTable.isDataTable("#presupuestosTable")) {
    $("#presupuestosTable").DataTable().ajax.reload(null, false);
  }
};

export const limpiarEstado = async () => {
  $('input[name="filtroEstado"]').prop("checked", false);
  localStorage.removeItem('filtroPresupuestos');
  await recargarTabla();
  document.querySelector(".presupuestosSpan").textContent = "Presupuestos";
};


//tooltips
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

/*/PRESUPESTOS/CREAR*/

//cols presupuestos/crear
export const agregarColumnaPresupuesto = async (e) => {
  P.agregarColumna(e);
}

export const eliminarColumnaPresupuesto = async (e) => {
  P.eliminarColumna(e);
}
//rellenar presupuesto
export const initPresupuesto = () => {
  const numeroOrdenInput = document.getElementById("numero_orden");
  const tallerSelect = document.querySelector("#taller");

  if (!numeroOrdenInput || !tallerSelect) return;

  const urlParams = new URLSearchParams(window.location.search);
  const numOrden = urlParams.get("numOrden");
  const tallerParam = urlParams.get("taller");

  // Si viene numOrden en la URL, rellenar automáticamente los campos
  if (numOrden && !isNaN(numOrden)) {
    numeroOrdenInput.value = numOrden;
    if (tallerParam) {
      tallerSelect.value = tallerParam;
    }
    findSiniestroByNumeroOrden(numOrden, tallerSelect);
  }

  // Al cambiar el número de orden, limpiamos primero y luego buscamos
  numeroOrdenInput.addEventListener("change", async function () {
    cleanSiniestroByNumeroOrden();
    if (this.value) {
      findSiniestroByNumeroOrden(this.value, tallerSelect);
    }
  });

  // Al cambiar el taller, volvemos a evaluar la orden actual
  tallerSelect.addEventListener("change", async function () {
    if (numeroOrdenInput.value) {
      findSiniestroByNumeroOrden(numeroOrdenInput.value, tallerSelect);
    }
  });
};

export const findSiniestroByNumeroOrden = async (numeroOrden, selectTaller) => {
  try {
    // const tallerParaBusqueda = await P.isRefacciones(selectTaller, numeroOrden);

    const response = await fetch(`${UrlProyecto}/${Perfil}/siniestros/${encodeURIComponent(numeroOrden)}`,
    );
    // console.log(await response.json());

    if (!response.ok) {
      cleanSiniestroByNumeroOrden();
      swalAlert({
        title: "No se encontró el siniestro",
        text: "Intenta con otra orden, esta no tiene siniestro asignado",
        icon: "error",
        theme: "auto",
      });
      throw new Error(`Error al obtener el siniestro. Status: ${response.status}`);
    }

    const data = await response.json();
    const {
      id,
      numero_orden,
      numero_siniestro,
      vehiculo_info,
      cliente
    } = data;

    const { aseguradora = "", vin = "", vehiculo = "", marca = "", modelo = "", taller = "" } = vehiculo_info || {};
    const { id: id_cliente = "", nombre: nombre_cliente = "", codigo = "" } = cliente || {};


    const inputs = {
      id_siniestro: id,
      numero_siniestro: numero_siniestro || "",
      aseguradora,
      vin,
      vehiculo,
      marca,
      modelo,
      id_cliente,
      nombre_cliente,
      codigo,
      // Solo incluir taller si NO es REFACCIONES (para mantener REFACCIONES visible)
      ...(selectTaller.value.toUpperCase() !== 'REFACCIONES' && { taller }),
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

    console.log(aseguradora);

    Object.entries(inputs).forEach(([key, val]) => {
      const el = document.querySelector(`#${key}`);
      if (el) el.value = val !== undefined && val !== null ? val : "";
    });
  } catch (error) {
    console.error("Error al buscar siniestro por número de orden:", error);
  }
};

export const cleanSiniestroByNumeroOrden = () => {
  const selectores = [
    "#id_siniestro",
    "#numero_siniestro",
    "#aseguradora",
    "#vin",
    "#vehiculo",
    "#marca",
    "#modelo"
  ].join(", ");

  document.querySelectorAll(selectores).forEach(el => el.value = "");
};

export const agregarPresupuesto = async (event) => {
  event.preventDefault();

  // Funciones para verificar permisos (basado en el DOM generado por Blade con permisos dinámicos)
  const puedeCotizarDirectamente = document.getElementById("cotizarDirectamente") !== null;

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
      await P.agregarPresupuesto(formData);
      window.location.href = `${UrlProyecto}/${Perfil}/presupuestos/`;
    }
  });
};

export const findPresupuestoByNumero = async (folio, accion) => {
  try {
    const presupuestoData = await P.fetchPresupuestoData(folio);
    const permisos = P.getPermisos();

    // 1. Renderizar la tabla de piezas
    console.log(presupuestoData.piezas);

    P.renderPiezasTable(presupuestoData.piezas, accion, permisos);

    // 2. Llenar los campos de información del siniestro y presupuesto
    P.populateSiniestroInputs(presupuestoData);
  } catch (error) {
    console.error("Error al cargar presupuesto:", error);
  }
};

export const agregarEvidencias = async (folio) => {
  swalAlert({
    title: "¿Quieres agregar las evidencias al presupuesto?",
    text: "Al hacer click en aceptar, se abrirá tu cliente de correo para notificar y automáticamente se pasará a cotizar.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Agregar",
    cancelButtonText: "Descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const formData = new FormData();
        formData.append("folio", folio);

        const response = await fetch(
          `${UrlProyecto}/${Perfil}/presupuestos/subir-evidencias`,
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
        // alert(Perfil);
        const asunto = encodeURIComponent(
          `Evidencias para el presupuesto ${folio}`,
        );
        // Usamos HTML para el cuerpo del correo, con un hipervínculo llamado "LIGA"
        const cuerpo = encodeURIComponent(
          `Adjunto las evidencias correspondientes al presupuesto con folio ${folio}.\nLiga:${UrlProyecto}/refacciones/presupuestos/cotizar?folio=${folio}`,
        );
        // Para que el correo se genere en formato HTML, se recomienda usar 'mailto' con 'body', pero algunos clientes no interpretan HTML en 'body'.
        // Sin embargo, se puede intentar con 'mailto' y el usuario puede copiar el enlace si su cliente no soporta HTML.
        window.open(`mailto:?subject=${asunto}&body=${cuerpo}`, "_blank");

        swalAlert({
          title: data.title,
          text: data.message,
          icon: data.icon,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          theme: "auto",
        }).then(() => {
          if ($.fn.DataTable.isDataTable("#presupuestosTable")) {
            $('#presupuestosTable').DataTable().ajax.reload(null, false);
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

/* init presupuestos/cotizar*/
//consulta de pieza para cotizar presupuestos 
export const obtenerCodigoCliente = () => {
  return P.obtenerCodigoCliente();
};

export const DatosByNumeroParte = async (numeroParte, indice, codigo) => {
  await P.consultarDatosByNumeroParte(numeroParte, indice, codigo);
};

export const calcularTotalPorFila = (indice) => {
  P.calcularTotalPorFila(indice);
};

export const calcularTotalCotizacion = () => {
  P.calcularTotalCotizacion();
};

export const initCodigoAutocar = () => {
  const codigoInput = document.getElementById('codigoAutocar');
  if (!codigoInput) return;

  codigoInput.addEventListener('change', async () => {
    const codigo = codigoInput.value.trim();
    if (!codigo) return;

    const rows = document.querySelectorAll('#cotizarPresupuestoTable tbody tr');
    for (let i = 0; i < rows.length; i++) {
      const indice = i + 1;
      const numeroParteInput = rows[i].querySelector(`input[name="numero_parte_${indice}"]`);
      const numeroParte = numeroParteInput?.value?.trim();

      // Re-fetchear los datos de cada pieza con el nuevo código de cliente
      if (numeroParte) {
        await DatosByNumeroParte(numeroParte, indice, codigo);
      }
    }
  });
};

export const initCheckPVP = () => {
  const checkPVP = document.getElementById('checkPVP');
  if (!checkPVP) return;

  checkPVP.addEventListener('change', () => {
    P.alternarPreciosPVP(checkPVP.checked);
    P.calcularTotalCotizacion();
  });
};

//metodos priv para cotizarPresupuestos

const confirmarCotizacionDialog = async (faltantes) => {
  const faltantesTexto = faltantes.join(", ");
  const swalText = faltantes.length > 0
    ? `Las siguientes piezas no tienen número de parte y no se cotizarán: ${faltantesTexto}. ¿Deseas continuar?`
    : "¿Quieres agregar esta cotización al presupuesto?";

  return swalAlert({
    title: "¿Estás seguro?",
    text: swalText,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, agregar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  });
};

const manejarRespuestaCotizacion = (data) => {
  swalAlert({
    title: data.title,
    text: data.message,
    icon: data.icon,
    confirmButtonText: "Aceptar",
    theme: "auto",
    timer: 2000,
  }).then(() => {
    P.enviarCorreoCotizacion(data.presupuesto.numero_presupuesto, data.link_presupuesto);
  });
};

const manejarErrorCotizacion = (error) => {
  console.error("Error al cargar los datos:", error);
  swalAlert({
    title: "Error",
    text: "No se pudo conectar con el servidor. Intenta nuevamente.",
    icon: "error",
    confirmButtonText: "Aceptar",
    theme: "auto",
  });
};

export const cotizarPresupuesto = async (event, cotizarPresupuestoForm) => {
  event.preventDefault();

  const proveedorSelect = document.querySelector("#proveedor");
  const proveedor = proveedorSelect ? proveedorSelect.value.toUpperCase() : "CHEVROLET";

  if (proveedor === "CHEVROLET") {
    const codigoInput = document.getElementById('codigoAutocar');
    if (codigoInput && codigoInput.value.trim() === "") {
      swalAlert({
        title: "Atención",
        text: "Necesitas colocar el código del cliente para poder cotizar.",
        icon: "warning",
        theme: "auto",
        didClose: () => {
          codigoInput.focus();
        }
      });
      return;
    }
  }

  const formData = new FormData(cotizarPresupuestoForm);

  const rows = document.querySelectorAll("#cotizarPresupuestoTable tbody tr");

  // Extraemos la lógica pesada del DOM a la clase
  const { piezas, faltantes } = P.extraerDatosCotizacion(rows);

  const result = await confirmarCotizacionDialog(faltantes);

  if (result.isConfirmed) {
    formData.append("piezas", JSON.stringify(piezas));

    try {
      // Delegamos el Fetch HTTP POST a la clase
      const data = await P.enviarCotizacionAlServidor(formData, folio);
      manejarRespuestaCotizacion(data);
    } catch (error) {
      manejarErrorCotizacion(error);
    }
  }
};

