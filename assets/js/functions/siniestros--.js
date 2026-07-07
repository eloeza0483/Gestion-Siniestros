import Siniestros from "../class/Siniestros.js";
import { UrlProyecto, swalQuestion, token , swalAlert} from "./const.js";

const departamentoSpan = document.getElementById("departamentoSpan");
// console.log(departamentoSpan.textContent);

const siniestrosClass = new Siniestros();

export const DTLoad = async () => {
  return siniestrosClass.DTable();
};


// return;

export const validarCampos = async (event) => {
  // alert("Validando campos");
  event.preventDefault();

  const numeroOrdenInput = document.querySelector("#numeroOrden");
  const numeroSiniestroInput = document.querySelector("#numeroSiniestro");
  const tallerInput = document.querySelector("#taller");
  // const numeroSiniestroInput = document.querySelector("#numeroSiniestro");

  console.log(numeroOrdenInput.value);
  console.log(numeroSiniestroInput.value);

  numeroOrdenInput.setCustomValidity("");
  numeroSiniestroInput.setCustomValidity("");

  try {
    const response = await fetch(
      `${UrlProyecto}/siniestros/${numeroOrdenInput.value}/${numeroSiniestroInput.value}/${tallerInput.value}`
    );
    if (!response.ok) {
      throw new Error("Error en la respuesta del servidor");
    }

    const data = await response.json();
    console.log(data);

    if (data.numeroOrdenExists) {
      numeroOrdenInput.setCustomValidity("El número de orden ya existe.");
    }

    if (data.numeroSiniestroExists) {
      numeroSiniestroInput.setCustomValidity(
        "El número de siniestro ya existe."
      );
    }

    // Mostrar mensajes de error si existen
    numeroOrdenInput.reportValidity();
    numeroSiniestroInput.reportValidity();

    return (
      numeroOrdenInput.checkValidity() && numeroSiniestroInput.checkValidity()
    );
  } catch (error) {
    console.error("Error al validar campos:", error);
    return false;
  }
};

export const agregarSiniestro = async (event) => {
  // event.preventDefault();

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres agregar este siniestro?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, agregar",
    cancelButtonText: "Cancelar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      const {
        success,
        message,
        icon,
        confirmButtonText,
        cancelButtonText,
        numeroOrden,
        taller,
      } = await siniestrosClass.setForm(event.target).crear();
      swalQuestion(message, { cancelButtonText }).then((result) => {
        if (result.isConfirmed) {
          location.href = `${UrlProyecto}/presupuestos/crear?numOrden=${numeroOrden}&taller=${taller}`;
        } else {
          recargarTabla();
          document
            .querySelector("[data-modal-target='createSiniestroModal']")
            .click();
          document.querySelector("#agregarSiniestroForm").reset();
        }
      });
    }
  });
};

export const cancelarSiniestro = (event) => {
  const id = event.target.dataset.id;

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres cancelar este siniestro?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, cancelar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      const { success, icon, title, message } = await siniestrosClass.cancelar(
        id
      );
      swalAlert({
        title,
        text: message,
        icon,
        confirmButtonText: "Aceptar",
        theme: "auto",
        timer: 2000,
      });
    }
  });
};

export const cerrarSiniestro = (event) => {
  const id = event.target.dataset.id;

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres cerrar este siniestro?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, cerrar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      const { success, icon, title, message } = await siniestrosClass.cerrar(
        id
      );
      swalAlert({
        title,
        text: message,
        icon,
        confirmButtonText: "Aceptar",
        theme: "auto",
        timer: 2000,
      });
    }
  });
};

export const reabrirSiniestro = (event) => {
  const id = event.target.dataset.id;

  swalAlert({
    title: "¿Estás seguro?",
    text: "¿Quieres reabrir este siniestro?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, cancelar",
    cancelButtonText: "No, descartar",
    theme: "auto",
    buttonsStyling: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      const { success, icon, title, message } = await siniestrosClass.reabrir(
        id
      );

      swalAlert({
        title,
        text: message,
        icon,
        confirmButtonText: "Aceptar",
        theme: "auto",
        timer: 2000,
      }).then(() => {
        if (data.success) {
          recargarTabla();
        }
      });
    }
  });
};

export const abrirModalCRUDSiniestro = (modalTarget, numOrden, tipoModal) => {
  const modal = document.getElementById(modalTarget);
  if (modal) {
    document.body.style.overflow = "hidden";
    document.body.style.height = "100%";

    const tbodyModal = modal.querySelector("tbody");
    const titleModal = modal.querySelector(".titleModal h3");
    if (tbodyModal) tbodyModal.innerHTML = "";
    if (titleModal) titleModal.textContent = "Cargando...";

    modal.classList.remove("hidden");
    modal.setAttribute("aria-hidden", "false");

    // // Cerrar al hacer clic fuera del contenido del modal
    // modal.addEventListener('click', function (event) {
    //    if (event.target === modal) {
    //       cerrarModal(modalTarget);
    //    }
    // });

    // Cerrar al hacer clic en el botón de cerrar (X)
    const closeButton = modal.querySelector(".close-modal");
    if (closeButton) {
      closeButton.addEventListener("click", () => {
        cerrarModal(modalTarget);
      });
    }

    if (numOrden && tipoModal) {
      fillModalPzasAutorizadas(numOrden, tipoModal);
    }
  }
};

export const cerrarModal = (modalTarget) => {
  const modal = document.getElementById(modalTarget);

  if (modal) {
    modal.classList.add("hidden");
    modal.setAttribute("aria-hidden", "true");

    document.body.style.overflow = "";
    document.body.style.height = "";
  }
};



export const fillModalPzasAutorizadas /*siniestros*/ = async (
  numOrden,
  tipoModal
) => {
  try {
    const url = `${UrlProyecto}/siniestros/getInfoPzas/${numOrden}/${tipoModal}`;
    const response = await fetch(url);
    const pzasAutorizadas = await response.json();
    console.log(pzasAutorizadas.data);

    const modalSiniestros = document.querySelector("#siniestros-modal");
    const contentModalSiniestros = document.querySelector(
      "#siniestros-modal .content-modal"
    );
    const titleModal = modalSiniestros.querySelector(".titleModal h3");
    const tbodyModal = contentModalSiniestros.querySelector("tbody");
    const theadModal = contentModalSiniestros.querySelector("thead");

    // Limpiar el contenido anterior
    tbodyModal.innerHTML = "";
    theadModal.innerHTML = "";

    // Verificar si no hay datos
    if (!pzasAutorizadas.data || pzasAutorizadas.data.length === 0) {
      tbodyModal.innerHTML = `
            <tr>
               <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">
                  No se encontraron datos para mostrar.
               </td>
            </tr>
         `;
      return;
    }

    const titulos = {
      "pzs-autorizadas": "Piezas Autorizadas",
      "pzs-surtidas": "Piezas Surtidas",
      "pzs-recibidas": "Piezas Recibidas",
      "pzs-faltantes": "Piezas Faltantes",
      "total-presupuestos": "Total de Presupuestos del Siniestro",
    };

    if (tipoModal === "total-presupuestos") {
      titleModal.textContent = `Presupuestos del Siniestro - ${pzasAutorizadas.data[0].numero_presupuesto || "N/A"
        }`;
    } else {
      titleModal.textContent = `${titulos[tipoModal] || "Información de Piezas"
        } - Orden ${pzasAutorizadas.data[0].numero_orden || "N/A"}`;
    }
    titleModal.className = "text-lg font-semibold text-white";

    const rowFirst = document.createElement("tr");

    if (tipoModal === "total-presupuestos") {
      rowFirst.innerHTML = `
         <th>N° de Presupuesto</th>
         <th>Proveedor</th>
         <th>Total</th>`;
    } else {
      rowFirst.innerHTML = `
         <th>N° Entrada</th>
         <th>N° Vale</th>
         <th>N° Presupuesto</th>
         <th>N° Parte</th>
         <th>Descripción</th>
         <th>Piezas</th>`;
    }

    theadModal.appendChild(rowFirst);

    if (tipoModal === "total-presupuestos") {
      pzasAutorizadas.data.forEach((item) => {
        const { numero_presupuesto, proveedor, total } = item;
        const row = document.createElement("tr");

        row.innerHTML = `
               <td class="px-4 py-2">
                  <a href="${UrlProyecto}/presupuestos/ver?folio=${numero_presupuesto}" class="text-blue-400 hover:underline" target="_blank" rel="noopener noreferrer">
                     ${numero_presupuesto ?? "N/A"}
                  </a>
               </td>
               <td class="px-4 py-2">${proveedor ?? "N/A"}</td>
               <td class="px-4 py-2">${total ?? "N/A"}</td>
            `;
        row.className = "border-b-2 border-white hover:bg-gray-600 ";
        tbodyModal.appendChild(row);
      });
    } else {
      pzasAutorizadas.data.forEach((item) => {
        const {
          cantidad,
          descripcion_w32,
          numero_orden,
          numero_parte,
          numero_presupuesto,
          numero_vale,
        } = item;
        const row = document.createElement("tr");

        row.innerHTML = `
               <td class="px-4 py-2">${numero_orden ?? "N/A"}</td>
               <td class="px-4 py-2">${numero_vale ?? "N/A"}</td>
               <td class="px-4 py-2">${numero_presupuesto ?? "N/A"}</td>
               <td class="px-4 py-2">${numero_parte ?? "N/A"}</td>
               <td class="px-4 py-2">${descripcion_w32 ?? "N/A"}</td>
               <td class="px-4 py-2 text-center">${cantidad ?? "N/A"}</td>
            `;

        row.className = "border-b-2 border-white hover:bg-gray-600 ";
        tbodyModal.appendChild(row);
      });
    }
  } catch (error) {
    console.error("Error al cargar los datos:", error);
    const errorModal = document.querySelector(
      "#siniestros-modal .content-modal tbody"
    );
    if (errorModal) {
      errorModal.innerHTML = `
            <tr>
               <td colspan="6" class="text-center py-4 text-red-500">
                  Error al cargar los datos. Intente nuevamente.
               </td>
            </tr>
         `;
    }
  }
};

export const getAllTalleres = async ($check) => {
  const url = `${UrlProyecto}/siniestros/getSiniestros/${$check}`;
  const response = await fetch(url);
  const talleres = await response.json();
  return talleres;
};

export const setLocalStorageTaller = (e) => {
  const taller = e.target.value;
  localStorage.setItem("taller", taller);
};

export const setLocalStorageFiltro = () => {
  const filtro = document.querySelector(
    "input[name='filtroEstado']:checked"
  ).value;

  localStorage.setItem("filtroEstado", filtro);
};

export function setLocalStorageVerTodos(e) {
  const verTodos = e.target.checked;
  localStorage.setItem("verTodos", verTodos);
}
