import Siniestro from "../class/Siniestros.js";
import { UrlProyecto, Perfil, UrlSplit, ultimoParametro, swalAlert, showLoading, hideLoading } from "./const.js";


const S = new Siniestro();

export function cargarTD() {
  S.TD();
}

export function initFormsMVA() {
  S.initFormsMVA();
}

export const recargarTabla = () => {
  if ($.fn.DataTable.isDataTable('#siniestrosTable')) {
    $('#siniestrosTable').DataTable().ajax.reload();
  }
};

export async function verDetallePiezas(e) {

  const { id_siniestro, tipo } = e.dataset;

  const modal = document.getElementById('modalDetallePartes');
  modal.classList.remove('hidden');

  const result = await S.getDetallePartes(id_siniestro, tipo);
  S.imprimirDetallePartes(result);
}

$(".close-modal").click(function () {
  const modal = document.getElementById('modalDetallePartes');
  modal.classList.add('hidden');
})

// export const abrirModal = (modalTarget, numOrden, tipoModal) => {
//   const modal = document.getElementById(modalTarget);
//   if (!modal) return;
//
//   document.body.style.overflow = "hidden";
//   document.body.style.height = "100%";
//
//   const tbodyModal = modal.querySelector("tbody");
//   const titleModal = modal.querySelector(".titleModal h3");
//   if (tbodyModal) tbodyModal.innerHTML = "";
//   if (titleModal) titleModal.textContent = "Cargando...";
//
//   modal.classList.remove("hidden");
//   modal.setAttribute("aria-hidden", "false");
//
//   const closeButton = modal.querySelector(".close-modal");
//   if (closeButton) {
//     closeButton.addEventListener("click", () => cerrarModal(modalTarget), { once: true });
//   }
//
//   if (numOrden && tipoModal) {
//     fillModalPzasAutorizadas(numOrden, tipoModal);
//   }
// };

// export const cerrarModal = (modalTarget) => {
//   const modal = document.getElementById(modalTarget);
//   if (modal) {
//     modal.classList.add("hidden");
//     modal.setAttribute("aria-hidden", "true");
//     document.body.style.overflow = "";
//     document.body.style.height = "";
//   }
// };

const getFiltroValue = () => document.querySelector('input[name="filtroEstado"]:checked')?.value;

export const setFiltro = () => {
  const valor = getFiltroValue();
  if (valor) localStorage.setItem("filtroSiniestros", valor);
};

export const getFiltro = () => {
  let guardado = localStorage.getItem("filtroSiniestros");

  if (!guardado) {
    guardado = "Abierto";
    localStorage.setItem("filtroSiniestros", guardado);
  }

  const input = document.querySelector(`input[name="filtroEstado"][value="${guardado}"]`);
  if (input) input.checked = true;
};

export const setTitulo = () => {
  const span = document.querySelector(".siniestrosSpan");
  const filtro = getFiltroValue() || localStorage.getItem("filtroSiniestros") || "Abierto";

  if (span) {
    const taller = Perfil.replace("_", " ");
    span.textContent = `Siniestros de ${taller} ${filtro.toLowerCase()}s`;
  }
};


export const validarCamposFormSiniestro = async (event) => {
  event.preventDefault();

  const form = event.target;
  const numeroOrdenInput = form.querySelector("#numeroOrden");
  const numeroSiniestroInput = form.querySelector("#numeroSiniestro");
  const perfilInput = form.querySelector("#taller").value;


  const vinInput = form.querySelector("#vin");

  numeroOrdenInput.setCustomValidity("");
  numeroSiniestroInput.setCustomValidity("");

  if (vinInput.value.length !== 17) {
    swalAlert("VIN Inválido", "El número de serie (VIN) debe tener exactamente 17 caracteres.", "warning");
    return false;
  }

  try {
    const response = await fetch(
      `${UrlProyecto}/${Perfil}/siniestros/${numeroOrdenInput.value}/${numeroSiniestroInput.value}/${perfilInput}`
    );
    if (!response.ok) {
      throw new Error("Error en la respuesta del servidor");
    }

    const data = await response.json();

    if (data.numeroOrdenExists) {
      numeroOrdenInput.setCustomValidity("El número de orden ya existe.");
    }

    if (data.numeroSiniestroExists) {
      numeroSiniestroInput.setCustomValidity("El número de siniestro ya existe.");
    }

    form.reportValidity();

    return (
      numeroOrdenInput.checkValidity() && numeroSiniestroInput.checkValidity()
    );
  } catch (error) {
    console.error("Error al validar campos:", error);
    return false;
  }
};


export const agregarSiniestro = async (event) => {
  swalAlert("¿Estás seguro?", "¿Quieres agregar este siniestro?", "question", {
    confirmButtonText: "Sí, agregar",
    cancelButtonText: "Cancelar",
  }).then(async (result) => {
    if (result.isConfirmed) {
      const { success, message, icon, numeroOrden, taller,
      } = await S.setForm(event.target).crear();

      if (success) {
        swalAlert("¡Éxito!", message, icon, {
          showCancelButton: true,
          confirmButtonText: "Ir a presupuesto",
          cancelButtonText: "Cerrar",
        }).then((result) => {
          if (result.isConfirmed) {
            location.href = `${UrlProyecto}/${Perfil}/presupuestos/crear?numOrden=${numeroOrden}&taller=${taller}`;
          } else {
            recargarTabla();
            // Cerrar modal y resetear form
            const closeBtn = document.querySelector("[data-modal-toggle='createSiniestroModal']");
            if (closeBtn) closeBtn.click();
            document.querySelector("#agregarSiniestroForm").reset();
          }
        });
      } else {
        swalAlert("Error", message, "error");
      }
    }
  });
};

export const cancelarSiniestro = (event) => {
  const btn = event.target.closest('.cancelSiniestroButton') || event.target;
  const id = btn.dataset.id;
  const motivoExistente = btn.dataset.motivo;

  const configBase = {
    title: "¿Confirmar cancelación?",
    text: "¿Quieres cancelar este siniestro, se cancelará todo lo relacionado con él (presupuestos, vales, etc)?",
    icon: "question",
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
    }
  };

  if (motivoExistente) {
    configBase.text = `Se utilizará el motivo previo: "${motivoExistente}". ¿Deseas proceder?`;
    swalAlert(configBase).then(async (result) => {
      if (result.isConfirmed) {
        const { success, icon, title, message } = await S.cancelar(id, motivoExistente);
        swalAlert(title, message, icon, { confirmButtonText: "Aceptar" }).then(() => {
          if (success) recargarTabla();
        });
      }
    });
  } else {
    swalAlert({
      ...configBase,
      title: "¿Estás seguro?",
      input: "textarea",
      inputLabel: "Motivo de cancelación",
      inputPlaceholder: "Ingresa el motivo aquí...",
      preConfirm: (motivo) => {
        if (!motivo) {
          Swal.showValidationMessage('Debes ingresar un motivo de cancelación');
        }
        return motivo;
      }
    }).then(async (result) => {
      if (result.isConfirmed) {
        const motivo = result.value;
        const { success, icon, title, message } = await S.cancelar(id, motivo);
        swalAlert(title, message, icon, { confirmButtonText: "Aceptar" }).then(() => {
          if (success) recargarTabla();
        });
      }
    });
  }
};

export const notificarCancelacion = (event) => {
  const btn = event.target.closest('.notificarCancelacionButton') || event.target;
  const id = btn.dataset.id;

  swalAlert({
    title: "Solicitar Cancelación",
    text: "Ingresa el motivo por el cual solicitas cancelar este siniestro. Se enviará un correo para su gestión.",
    icon: "warning",
    input: "textarea",
    inputLabel: "Motivo de cancelación",
    inputPlaceholder: "Ingresa el motivo aquí...",
    showCancelButton: true,
    confirmButtonText: "Enviar solicitud",
    cancelButtonText: "Cancelar",
    buttonsStyling: false,
    background: '#1f2937',
    color: '#ffffff',
    customClass: {
      popup: 'bg-gray-800 rounded-xl border border-gray-700 shadow-2xl',
      title: 'text-white text-xl font-bold',
      htmlContainer: 'text-gray-300 text-sm mt-2',
      input: 'bg-gray-700 text-white border border-gray-600 focus:ring-orange-500 focus:border-orange-500 rounded-lg placeholder-gray-400 w-full p-3 mt-4',
      inputLabel: 'text-gray-300 font-semibold text-left w-full mt-2',
      confirmButton: 'bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-lg px-5 py-2.5 mx-2 transition-colors',
      cancelButton: 'bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-lg px-5 py-2.5 mx-2 transition-colors',
      validationMessage: 'bg-gray-900 text-red-400 border border-red-800 mt-2 rounded-lg'
    },
    preConfirm: (motivo) => {
      if (!motivo) {
        Swal.showValidationMessage('Debes ingresar un motivo para la solicitud');
      }
      return motivo;
    }
  }).then(async (result) => {
    if (result.isConfirmed) {
      const motivo = result.value;
      const { success, icon, title, message } = await S.solicitarCancelacion(id, motivo);
      swalAlert(title, message, icon, {
        confirmButtonText: "Aceptar",
      });
    }
  });
};

export const cerrarSiniestro = (event) => {
  const btn = event.target.closest('.cerrarSiniestroButton') || event.target;
  const id = btn.dataset.id;

  swalAlert("¿Estás seguro?", "¿Quieres cerrar este siniestro?", "question", {
    confirmButtonText: "Sí, cerrar",
    cancelButtonText: "No, descartar",
  }).then(async (result) => {
    if (result.isConfirmed) {
      showLoading("Cerrando siniestro y enviando notificaciones...");
      const { success, icon, title, message } = await S.cerrar(id);
      hideLoading();
      swalAlert(title, message, icon, {
        showCancelButton: false,
        confirmButtonText: "Aceptar",
      }).then(() => {
        if (success) recargarTabla();
      });
    }
  });
};

export const reabrirSiniestro = (event) => {
  const btn = event.target.closest('.reabrirSiniestroButton') || event.target;
  const id = btn.dataset.id;

  swalAlert("¿Estás seguro?", "¿Quieres reabrir este siniestro?", "question", {
    confirmButtonText: "Sí, reabrir",
    cancelButtonText: "No, descartar",
  }).then(async (result) => {
    if (result.isConfirmed) {
      const { success, icon, title, message } = await S.reabrir(id);
      swalAlert(title, message, icon, {
        showCancelButton: false,
        confirmButtonText: "Aceptar",
      }).then(() => {
        if (success) recargarTabla();
      });
    }
  });
};

/*
export const fillModalPzasAutorizadas = async (numOrden, tipoModal) => {
  try {
    const url = `${UrlProyecto}/siniestros/getInfoPzas/${numOrden}/${tipoModal}`;
    const response = await fetch(url);
    const pzasAutorizadas = await response.json();

    const modalSiniestros = document.querySelector("#siniestros-modal");
    const contentModalSiniestros = document.querySelector("#siniestros-modal .content-modal");
    const titleModal = modalSiniestros.querySelector(".titleModal h3");
    const tbodyModal = contentModalSiniestros.querySelector("tbody");
    const theadModal = contentModalSiniestros.querySelector("thead");

    tbodyModal.innerHTML = "";
    theadModal.innerHTML = "";

    if (!pzasAutorizadas.data || pzasAutorizadas.data.length === 0) {
      tbodyModal.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">No se encontraron datos para mostrar.</td></tr>`;
      return;
    }

    const titulos = {
      "pzs-autorizadas": "Piezas Autorizadas",
      "pzs-surtidas": "Piezas Surtidas",
      "pzs-recibidas": "Piezas Recibidas",
      "pzs-faltantes": "Piezas Faltantes",
      "total-presupuestos": "Total de Presupuestos del Siniestro",
    };

    titleModal.textContent = tipoModal === "total-presupuestos"
      ? `Presupuestos del Siniestro - ${pzasAutorizadas.data[0].numero_presupuesto || "N/A"}`
      : `${titulos[tipoModal] || "Información de Piezas"} - Orden ${pzasAutorizadas.data[0].numero_orden || "N/A"}`;
    titleModal.className = "text-lg font-semibold text-white";

    const rowFirst = document.createElement("tr");
    rowFirst.innerHTML = tipoModal === "total-presupuestos"
      ? `<th>N° de Presupuesto</th><th>Proveedor</th><th>Total</th>`
      : `<th>N° Entrada</th><th>N° Vale</th><th>N° Presupuesto</th><th>N° Parte</th><th>Descripción</th><th>Piezas</th>`;
    theadModal.appendChild(rowFirst);

    pzasAutorizadas.data.forEach((item) => {
      const row = document.createElement("tr");
      row.className = "border-b-2 border-white hover:bg-gray-600";
      row.innerHTML = tipoModal === "total-presupuestos"
        ? `<td class="px-4 py-2"><a href="${UrlProyecto}/presupuestos/ver?folio=${item.numero_presupuesto}" class="text-blue-400 hover:underline" target="_blank" rel="noopener noreferrer">${item.numero_presupuesto ?? "N/A"}</a></td>
           <td class="px-4 py-2">${item.proveedor ?? "N/A"}</td>
           <td class="px-4 py-2">${item.total ?? "N/A"}</td>`
        : `<td class="px-4 py-2">${item.numero_orden ?? "N/A"}</td>
           <td class="px-4 py-2">${item.numero_vale ?? "N/A"}</td>
           <td class="px-4 py-2">${item.numero_presupuesto ?? "N/A"}</td>
           <td class="px-4 py-2">${item.numero_parte ?? "N/A"}</td>
           <td class="px-4 py-2">${item.descripcion_w32 ?? "N/A"}</td>
           <td class="px-4 py-2 text-center">${item.cantidad ?? "N/A"}</td>`;
      tbodyModal.appendChild(row);
    });

  } catch (error) {
    console.error("Error al cargar los datos:", error);
    const errorModal = document.querySelector("#siniestros-modal .content-modal tbody");
    if (errorModal) {
      errorModal.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-red-500">Error al cargar los datos. Intente nuevamente.</td></tr>`;
    }
  }
};
*/
