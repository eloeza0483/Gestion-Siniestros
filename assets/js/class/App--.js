import {
  URLactual,
  ultimoParametro,
  genRuta,
  delegacionEvento,
  DT,
  UrlProyecto,
  token,
  Toast,
} from "../functions/const.js";
import { asignarEntrada, recargarTabla } from "../functions/vales.js";
// import { fillModal } from "../functions/home.js";

class App {
  constructor() {
    this.init();
  }

  async init() {
    const fecha = new Date();
    const time = fecha.getTime();
    let presupuestosClass,
      valesClass,
      entradasClass,
      albaranesClass,
      agregarColumnaBtn,
      eliminarColumnaBtn,
      folio,
      numVale,
      tallerSelect;


    switch (URLactual) {
      case genRuta("login"):
        const L = await import("../functions/login.js");
        document
          .querySelector("#form-login")
          .addEventListener("submit", L.login);
        break;
      case genRuta("siniestros", 'autocar_pensiones'):
      case genRuta("siniestros", 'autocar_periferico'):
      case genRuta("siniestros", 'refacciones'):
        const siniestrosClass = await import(
          `../functions/siniestros.js?${time}`
        );

        // Recuperar taller y filtro de localStorage al recargar

        const tallerGuardado = localStorage.getItem("taller");
        const filtroGuardado = localStorage.getItem("filtroEstado");
        const verTodosGuardado = localStorage.getItem("verTodos");

        // document.querySelector("#id_perfil").addEventListener("change", function (e) {
        //   const perfiles = document.querySelector("#createProductButton").dataset.perfilid;
        //   console.log(perfiles);

        // })

        const selectorTalleres = document.querySelector("#talleres");

        if (tallerGuardado && selectorTalleres) {
          selectorTalleres.value = tallerGuardado;
        }

        if (filtroGuardado) {
          const checkFiltro = document.querySelector(
            `input[name="filtroEstado"][value="${filtroGuardado}"]`,
          );
          if (checkFiltro) {
            checkFiltro.checked = true;
          }
        }

        if (verTodosGuardado === 'true') {
          const checkVerTodos = document.querySelector("#switchVerTodos");
          if (checkVerTodos) {
            checkVerTodos.checked = true;
          }
        }

        // Actualizar el texto del span
        // const spanSiniestros = document.querySelector(".siniestrosSpan");
        // if (spanSiniestros && tallerGuardado && filtroGuardado) {
        //   spanSiniestros.textContent = `Siniestros de ${tallerGuardado.toLowerCase()} ${filtroGuardado.toLowerCase()}s`;
        // }

        siniestrosClass.DTLoad();

        const numOrdenInput = document.querySelector("#numeroOrden");
        const numeroSiniestroInput = document.querySelector("#numeroSiniestro");


        if (numOrdenInput) {
          numOrdenInput.addEventListener("input", () => {
            numOrdenInput.setCustomValidity("");

          });
        }

        if (numeroSiniestroInput) {
          numeroSiniestroInput.addEventListener("input", () => {
            numeroSiniestroInput.setCustomValidity("");
          });
        }

        const agregarSiniestroForm = document.querySelector(
          "#agregarSiniestroForm",
        );
        if (agregarSiniestroForm) {
          // Desactivar validación nativa automática para manejarla manualmente en JS
          agregarSiniestroForm.setAttribute("novalidate", "");

          agregarSiniestroForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            // const id_perfil = document.querySelector('#id_perfil').value;

            console.log("submit");
            const esValido = await siniestrosClass.validarCampos(e);
            if (esValido) {
              siniestrosClass.agregarSiniestro(e);
            } else {
              console.error("Validación fallida");
            }
          });
        }



        const forms = [
          "formAseguradora",
          "formVehiculo",
          "formMarca",
        ];

        forms.forEach(form => {
          const formElement = document.querySelector(`#${form}`);
          if (formElement) {
            formElement.addEventListener("submit", async (e) => {
              e.preventDefault();
              const formData = new FormData(e.target);
              const data = Object.fromEntries(formData);

              // Determinar el endpoint según el formulario
              let endpoint = "";
              if (form === "formAseguradora") {
                endpoint = `${UrlProyecto}/aseguradoras/crear`;
              } else if (form === "formVehiculo") {
                endpoint = `${UrlProyecto}/vehiculos/crear`;
              } else if (form === "formMarca") {
                endpoint = `${UrlProyecto}/marcas/crear`;
              }

              try {
                const response = await fetch(endpoint, {
                  method: "POST",
                  headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                  },
                  body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok) {
                  Toast(result.message || "Registro creado exitosamente", "success");
                  formElement.reset();
                } else {
                  Toast(result.message || "Error al crear el registro", "error");
                }
              } catch (error) {
                console.error("Error:", error);
                Toast("Error al procesar la solicitud", "error");
              }
            });
          }
        });


        // const siniestrosTable = document.querySelector("#siniestrosTable");

        // Usar delegación de eventos para manejar clics en elementos dinámicos
        document.addEventListener("click", function (event) {
          // Buscar el elemento que coincide con el selector más cercano
          const cancelBtn = event.target.closest(".cancelSiniestroButton");
          const reabrirBtn = event.target.closest(".reabrirSiniestroButton");
          const openModalBtn = event.target.closest(".open-modal");
          const closeModalBtn = event.target.closest(".close-modal");
          const cerrarSiniestroBtn = event.target.closest(
            ".cerrarSiniestroButton",
          );

          if (cancelBtn) {
            event.preventDefault();
            siniestrosClass.cancelarSiniestro(event);
          } else if (reabrirBtn) {
            event.preventDefault();
            siniestrosClass.reabrirSiniestro(event);
          } else if (openModalBtn) {
            event.preventDefault();
            const modalTarget = openModalBtn.getAttribute("data-modal-target");
            const numOrden = openModalBtn.getAttribute("data-numorden");
            const tipoModal = openModalBtn.getAttribute("data-tipo-modal");
            siniestrosClass.abrirModal(modalTarget, numOrden, tipoModal);
            // siniestrosClass.fillModalPzasAutorizadas(numOrden, tipoModal);
          } else if (closeModalBtn) {
            event.preventDefault();
            const modalToggle = closeModalBtn.getAttribute("data-modal-toggle");
            siniestrosClass.cerrarModal(modalToggle);
          } else if (cerrarSiniestroBtn) {
            event.preventDefault();
            siniestrosClass.cerrarSiniestro(event);
          }
        });





        document
          .querySelector("#limpiarEstado")
          .addEventListener("click", siniestrosClass.limpiarEstado);
        // Sí, así está bien, pero falta llamar la función recargarTabla correctamente (faltan los paréntesis).
        document
          .querySelectorAll('input[name="filtroEstado"]')
          .forEach((input) => {
            input.addEventListener("change", function () {
              siniestrosClass.recargarTabla();
              const taller = document.querySelector("#talleres").value;
              document.querySelector(".siniestrosSpan").textContent =
                `Siniestros de ${taller.toLowerCase()} ${this.value.toLowerCase()}s`;
            });
          });

        document
          .querySelector("#simple-search")
          .addEventListener("keyup", (e) => {
            siniestrosClass.buscar(e);
          });

        document
          .querySelector("#filterDropdown")
          .addEventListener("change", function () {
            siniestrosClass.setLocalStorageFiltro();
          });

        document
          .querySelectorAll('select[name="talleres"]')
          .forEach((input) => {
            input.addEventListener("change", function (e) {
              siniestrosClass.setLocalStorageTaller(e); // Guardar en localStorage
              siniestrosClass.recargarTabla();
              const filtroEstado = document.querySelector(
                "input[name='filtroEstado']:checked",
              );

              document.querySelector(".siniestrosSpan").textContent =
                `Siniestros de ${this.value.toLowerCase()} ${filtroEstado.value.toLowerCase()}s`;
            });
          });

        document.querySelectorAll('select[name="entidad"]').forEach((input) => {
          input.addEventListener("change", function () {
            siniestrosClass.recargarTabla();
          });
        });

        // document.querySelector("#talleres").addEventListener("change", (e) => {
        //   siniestrosClass.btnBuscar(e);
        //   siniestrosClass.setLocalStorageTaller(e);
        // });

        document
          .querySelector("#switchVerTodos")
          .addEventListener("change", (e) => {
            // La lógica de URL con/sin check está en const.js
            siniestrosClass.setLocalStorageVerTodos(e);
            siniestrosClass.recargarTabla();
          });

        break;
      case genRuta("presupuestos"):
        presupuestosClass = await import(
          `../functions/presupuestos.js?${time}`
        );

        presupuestosClass.DTLoad();

        $("#presupuestosTable")
          .DataTable()
          .on("draw", function () {
            presupuestosClass.initTooltips();
          });

        document.querySelector("#limpiarEstado").addEventListener("click", presupuestosClass.limpiarEstado);

        document.querySelectorAll('input[name="filtroEstado"]').forEach((input) => {
          input.addEventListener("change", function () {
            presupuestosClass.recargarTabla();
            if (this.value === "SinCotizar") {
              document.querySelector(".presupuestosSpan").textContent =
                `Presupuestos Sin Cotizar`;
            } else {
              document.querySelector(".presupuestosSpan").textContent =
                `Presupuestos ${this.value}s`;
            }
          });
        });

        tallerSelect = document.querySelectorAll('select[name="talleres"]');
        tallerSelect.forEach((input) => {
          input.addEventListener("change", presupuestosClass.recargarTabla);
        });

        document.addEventListener("click", (e) => {
          const btn = e.target.closest(".subirEvidencias");
          if (btn) {
            e.preventDefault();
            const folioBtn = btn.getAttribute("data-folio");
            presupuestosClass.agregarEvidencias(folioBtn);
          }
        });

        break;
      case genRuta("presupuestos/ver"):
        presupuestosClass = await import(
          `../functions/presupuestos.js?${time}`
        );

        folio = new URLSearchParams(window.location.search).get("folio");
        if (folio) {
          document.querySelector(".texto-presupuesto").textContent =
            `Presupuesto ${folio}`;
          presupuestosClass.findPresupuestoByNumero(folio, "ver");
        }

        const btnSubirEvidencias = document.querySelector(".subirEvidencias");
        if (btnSubirEvidencias) {
          btnSubirEvidencias.addEventListener("click", () => {
            presupuestosClass.agregarEvidencias(folio);
          });
        }

        break;
      case genRuta("presupuestos/crear"):
        presupuestosClass = await import(
          `../functions/presupuestos.js?${time}`
        );

        agregarColumnaBtn = document.getElementById(
          "agregarColumnaPresupuesto",
        );
        eliminarColumnaBtn = document.getElementById(
          "eliminarColumnaPresupuesto",
        );

        agregarColumnaBtn.addEventListener("click", () =>
          presupuestosClass.agregarColumna("crear"),
        );
        eliminarColumnaBtn.addEventListener(
          "click",
          presupuestosClass.eliminarColumna,
        );

        const numeroOrdenInput = document.getElementById("numero_orden");

        const numOrden = new URLSearchParams(window.location.search).get(
          "numOrden",
        );

        // Si viene numOrden en la URL, rellenar automáticamente los campos
        tallerSelect = document.querySelector("#taller");
        if (numOrden && !isNaN(numOrden)) {
          numeroOrdenInput.value = numOrden;
          presupuestosClass.findSiniestroByNumeroOrden(numOrden, tallerSelect);
        }

        numeroOrdenInput.addEventListener("change", async function () {
          presupuestosClass.cleanSiniestroByNumeroOrden(this.value);
        });

        tallerSelect.addEventListener("change", async function () {
          // alert(this.value);
          const numeroOrdenInput = document.querySelector("#numero_orden");
          if (numeroOrdenInput.value) {
            presupuestosClass.findSiniestroByNumeroOrden(
              numeroOrdenInput.value,
              tallerSelect,
            );
          }
        });

        numeroOrdenInput.addEventListener("change", async function () {
          // alert(this.value);
          // const taller = document.querySelector("#taller");
          if (this.value) {
            presupuestosClass.findSiniestroByNumeroOrden(
              this.value,
              tallerSelect,
            );
          }
        });

        delegacionEvento("numero-parte", "keyup", function (e) {
          presupuestosClass.consultarDatosByNumeroParte(
            e.value,
            e.dataset.conteo,
          );
        });

        delegacionEvento("proveedor", "change", function (e) {
          presupuestosClass.consultarDatosByProveedor(e);
        });

        delegacionEvento("cantidad", "keyup", function (e) {
          presupuestosClass.calcularTotalPorFila(e.dataset.conteo);
        });

        const nuevoPresupuestoForm = document.querySelector(
          "#nuevoPresupuestoForm",
        );
        nuevoPresupuestoForm.addEventListener("submit", (e) => {
          presupuestosClass.agregarPresupuesto(e);
        });

        break;
      case genRuta("presupuestos/cotizar"):
        presupuestosClass = await import(
          `../functions/presupuestos.js?${time}`
        );

        folio = new URLSearchParams(window.location.search).get("folio");
        if (folio) {
          document.querySelector(".texto-presupuesto").textContent =
            `Cotizar presupuesto ${folio}`;
          await presupuestosClass.findPresupuestoByNumero(folio, "cotizar");
        }

        agregarColumnaBtn = document.getElementById(
          "agregarColumnaPresupuesto",
        );
        eliminarColumnaBtn = document.getElementById(
          "eliminarColumnaPresupuesto",
        );

        // agregarColumnaBtn.addEventListener("click", () => presupuestosClass.agregarColumna('cotizar'));
        // eliminarColumnaBtn.addEventListener("click", () => presupuestosClass.eliminarColumna('cotizar'));

        if (document.querySelector("#proveedor").value === "CHEVROLET") {
          delegacionEvento("numero-parte", "keyup", function (e) {

            presupuestosClass.consultarDatosByNumeroParte(
              e.value,
              e.dataset.conteo,
            );
          });
        }

        delegacionEvento("precio-unitario", "keyup", function (e) {
          presupuestosClass.calcularTotalPorFila(e.dataset.conteo);
        });

        delegacionEvento("cantidad", "keyup", function (e) {
          presupuestosClass.calcularTotalPorFila(e.dataset.conteo);
        });

        const cotizarPresupuestoForm = document.querySelector(
          "#cotizarPresupuestoForm",
        );
        if (cotizarPresupuestoForm) {
          cotizarPresupuestoForm.addEventListener("submit", (e) => {
            presupuestosClass.cotizarPresupuesto(e, cotizarPresupuestoForm);
          });
        }

        const proveedorInput = document.querySelector("#proveedor");
        if (proveedorInput) {
          presupuestosClass.consultarDatosByProveedor(proveedorInput);
        }

        break;
      case genRuta("vales"):
        valesClass = await import(`../functions/vales.js?${time}`);

        valesClass.DTLoad();

        $("#presupuestosTable")
          .DataTable()
          .on("draw", function () {
            valesClass.initTooltips();
          });

        delegacionEvento("cancelValeButton", "click", valesClass.cancelarVale);

        document
          .querySelector("#limpiarEstado")
          .addEventListener("click", valesClass.limpiarEstado);
        document
          .querySelectorAll('input[name="filtroEstado"]')
          .forEach((input) => {
            input.addEventListener("change", valesClass.recargarTabla);
          });

        document
          .querySelectorAll('input[name="filtroEstado"]')
          .forEach((input) => {
            input.addEventListener("change", function () {
              valesClass.recargarTabla();
              document.querySelector(".valesSpan").textContent =
                `Vales ${this.value}s`;
            });
          });

        tallerSelect = document.querySelectorAll('select[name="taller"]');
        if (tallerSelect) {
          tallerSelect.forEach((input) => {
            input.addEventListener("change", valesClass.recargarTabla);
          });
        }

        // Delegación de eventos para manejar clics en botones dinámicos
        document.addEventListener("click", (e) => {
          const btnAbrirVale = e.target.closest(".btnAbrirVale");
          if (btnAbrirVale) {
            e.preventDefault();
            valesClass.numVale(e);
          }
        });

        break;
      case genRuta("vales/ver"):
        valesClass = await import(`../functions/vales.js?${time}`);
        numVale = new URLSearchParams(window.location.search).get("numVale");
        if (numVale) {
          document.querySelector(".texto-vale").textContent = `Vale ${numVale}`;
          valesClass.findValeByNumero(numVale, "ver");
        }

        const asignarAlbaranBoton = document.getElementById(
          "asignarAlbaranButton",
        );
        if (asignarAlbaranBoton) {
          asignarAlbaranBoton.addEventListener("click", async () => {
            // Primero validar si todas las piezas ya están surtidas
            const puedeAsignar =
              await valesClass.validarSurtidoAlbaran(numVale);
            if (puedeAsignar) {
              valesClass.asignarAlbaran();
            }
          });
        }

        const asignarAlbaranFor = document.getElementById("asignarAlbaranForm");
        if (asignarAlbaranFor) {
          asignarAlbaranFor.addEventListener("submit", (e) => {
            valesClass.validarMatchAlbaran(e, numVale).then((match) => {
              if (match) {
                valesClass.submitAsignarAlbaran(e, numVale);
              }
            });
          });
        }

        const asignarEntradaButton = document.getElementById(
          "asignarEntradaButton",
        );
        if (asignarEntradaButton) {
          asignarEntradaButton.addEventListener(
            "click",
            valesClass.asignarEntrada,
          );
        }
        const asignarEntradaForm =
          document.getElementById("asignarEntradaForm");
        if (asignarEntradaForm) {
          asignarEntradaForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const proveedor = document.getElementById("proveedor")?.value || "";
            valesClass.validarMatchEntrada(e, numVale, proveedor).then((match) => {
              if (match) {
                valesClass.submitAsignarEntrada(e, numVale);
              }
            });
          });
        }

        const entrada = document.getElementById("entrada");
        if (entrada) {
          entrada.addEventListener("keydown", async function (e) {
            if (e.key === "Enter") {
              e.preventDefault(); // Solo previene para este campo específico

              const spinner = document.querySelector("#spinner-entrada");
              const tabla = document.querySelector("#datosEntradaTable");
              const valor = this.value.trim();

              if (valor === "") {
                if (tabla) {
                  tabla.classList.add("hidden");
                  const tbody = tabla.querySelector("tbody");
                  if (tbody) tbody.innerHTML = "";
                }
                return;
              }

              if (spinner) spinner.classList.remove("hidden");
              if (tabla) tabla.classList.remove("hidden");
              try {
                const proveedor = document.getElementById("proveedor")?.value || "";
                await valesClass.findEntradaByNumero(valor, numVale, proveedor);
              } catch (error) {
                console.error(error);
                if (tabla) tabla.classList.add("hidden");
              } finally {
                if (spinner) spinner.classList.add("hidden");
              }
            }
          });
        }

        const asignarAlbaranButton = document.getElementById(
          "asignarAlbaranButton",
        );
        if (asignarAlbaranButton) {
          asignarAlbaranButton.addEventListener(
            "click",
            valesClass.asignarAlbaran,
          );
        }

        const asignarAlbaranForm =
          document.getElementById("asignarAlbaranForm");
        if (asignarAlbaranForm) {
          asignarAlbaranForm.addEventListener("submit", (e) => {
            valesClass.validarMatchAlbaran(e, numVale).then((match) => {
              if (match) {
                valesClass.submitAsignarAlbaran(e, numVale);
              }
            });
          });
        }

        const albaran = document.getElementById("albaran");
        if (albaran) {
          albaran.addEventListener("keydown", async function (e) {
            if (e.key === "Enter") {
              e.preventDefault(); // Solo previene para este campo específico

              const spinner = document.querySelector("#spinner-albaran");
              const tabla = document.querySelector("#datosAlbaranTable");
              const valor = this.value.trim();

              if (valor === "") {
                if (tabla) {
                  tabla.classList.add("hidden");
                  const tbody = tabla.querySelector("tbody");
                  if (tbody) tbody.innerHTML = "";
                }
                return;
              }

              if (spinner) spinner.classList.remove("hidden");
              if (tabla) tabla.classList.remove("hidden");
              try {
                await valesClass.findAlbaranByNumero(valor, numVale);
              } catch (error) {
                console.error(error);
                if (tabla) tabla.classList.add("hidden");
              } finally {
                if (spinner) spinner.classList.add("hidden");
              }
            }
          });

          delegacionEvento("numeroParte", "keyup", function (e) {
            valesClass.consultarDatosByNumeroParte(e.value, e.dataset.conteo);
          });
        }

        // Movido fuera del if (albaran) para que funcione sin importar permisos
        const addComplementoForm =
          document.getElementById("addComplementoForm");

        if (addComplementoForm) {
          let timeout;
          addComplementoForm.addEventListener("keyup", (e) => {
            const targetId = e.target.id;
            console.log("target", targetId);

            clearTimeout(timeout);
            timeout = setTimeout(() => {
              valesClass.calcularTotalComplemento(addComplementoForm, targetId);
            }, 500);
          });

          addComplementoForm.addEventListener("submit", (e) => {
            valesClass.addComplemento(e, numVale);
          });
        }

        delegacionEvento("editarPiezaButton", "click", function (e) {
          valesClass.modificarPartes(e);
        });

        delegacionEvento("notificarEliminarPiezaButton ", "click", function (e) {
          valesClass.notificarEliminarParteAut(e);
        });

        delegacionEvento("eliminarPiezaButton", "click", function (e) {
          valesClass.eliminarPartes(e);
        });

        delegacionEvento("rechazarSolicitudButton", "click", function (e) {
          valesClass.rechazarSolicitudEliminacion(e);
        });

        const verValeForm = document.getElementById("verValeForm");
        if (verValeForm) {
          verValeForm.addEventListener("submit", (e) => {
            valesClass.submitModificarPartes(e, numVale).then((match) => {
              if (match) {
                valesClass.submitAsignarAlbaran(e, numVale);
              }
            });
          });
        }

        delegacionEvento("pedirModificacionButton", "click", function (e) {
          valesClass.pedirModificacion(e, numVale);
        });

        //ERNESTO -- VER ENTRADAS (movido fuera del if albaran)
        document.addEventListener("click", (e) => {
          const btnAbrirVale = e.target.closest(".verEntrada");

          if (btnAbrirVale) {
            e.preventDefault();
            valesClass.verEntradas(e);
          }
        });

        break;
      case genRuta("vales/asignar"):
        valesClass = await import(`../functions/vales.js?${time}`);
        folio = new URLSearchParams(window.location.search).get("folio");
        if (folio) {
          document.querySelector(".numPresupuestoSpan").textContent =
            `Presupuesto ${folio}`;
          await valesClass.findPresupuestoByNumero(folio);
          await valesClass.cargarPiezasDisponibles(folio);
          //Manejar cantidades al agregar vale
          const cant = document.querySelectorAll('input[name="cantidad"]');
          cant.forEach((input) => {
            input.addEventListener("input", () => {
              valesClass.calcularCantidades();
            });
          });
        }
        delegacionEvento("filaCheckBox", "click", (e) => {
          // if (!e.checked) {
          // const fila = e.closest("tr");
          // if (fila) {
          // const inputCantidad = fila.querySelector(
          // 'input[name="cantidad"]'
          // );
          // if (inputCantidad) inputCantidad.value = 1;
          // }
          // }
          valesClass.calcularCantidades();
          valesClass.asignarFaltantes();
        });

        const numValeInput = document.querySelector("#numero_vale");
        if (numValeInput) {
          numValeInput.addEventListener("input", () => {
            numValeInput.setCustomValidity("");
          });
        }

        const valeForm = document.getElementById("valeForm");
        if (valeForm) {
          valeForm.addEventListener("submit", async (e) => {
            const esValido = await valesClass.validarCampos(e);
            if (esValido) {
              valesClass.agregarVale(e, folio);
            } else {
              console.error("Validación fallida");
            }
          });
        }

        break;
      case genRuta("entradas"):
        entradasClass = await import(`../functions/entradas.js?${time}`);
        entradasClass.DTLoad();

        document
          .querySelector("#limpiarEstado")
          .addEventListener("click", entradasClass.limpiarEstado);

        document
          .querySelectorAll('input[name="filtroEstado"]')
          .forEach((input) => {
            input.addEventListener("change", function () {
              entradasClass.recargarTabla();
              document.querySelector(".entradasSpan").textContent =
                `Entradas ${this.value}s`;
            });
          });

        tallerSelect = document.querySelectorAll('select[name="taller"]');
        tallerSelect.forEach((input) => {
          input.addEventListener("change", entradasClass.recargarTabla);
        });

        folio = new URLSearchParams(window.location.search).get("folio");

        break;
      // case genRuta("entradas/ver"):

      // entradasClass = await import(`../functions/entradas.js?${time}`);

      // const numEntrada = new URLSearchParams(window.location.search).get('numEntrada');
      // if (numEntrada) {
      // document.querySelector(".texto-entrada").textContent = `Entrada ${numEntrada}`;
      // entradasClass.findEntradaByNumero(numEntrada);
      // }

      // delegacionEvento('liberarParteButton', 'click', function (e) {
      // entradasClass.liberarPartes(e);
      // });

      // break;
      case genRuta(
        "entradas",
        "detalle",
        document.querySelector("#id_entrada")?.value,
      ):
        entradasClass = await import(`../functions/entradas.js?${time}`);
        document.querySelectorAll(".liberarParteButton").forEach((f) =>
          f.addEventListener("click", function (e) {
            entradasClass.liberarPartes(this);
          }),
        );
        break;
      case genRuta("albaranes"):
        albaranesClass = await import(`../functions/albaranes.js?${time}`);
        albaranesClass.DTLoad();
        document
          .querySelector("#limpiarEstado")
          .addEventListener("click", albaranesClass.limpiarEstado);
        document
          .querySelectorAll('input[name="filtroEstado"]')
          .forEach((input) => {
            input.addEventListener("change", function () {
              albaranesClass.recargarTabla();
              document.querySelector(".albaranesSpan").textContent =
                `Albaranes ${this.value}s`;
            });
          });

        break;
      case genRuta("albaranes/ver"):
        albaranesClass = await import(`../functions/albaranes.js?${time}`);

        const numAlbaran = new URLSearchParams(window.location.search).get(
          "numAlbaran",
        );
        if (numAlbaran) {
          document.querySelector(".texto-albaran").textContent =
            `Albaran ${numAlbaran}`;
          albaranesClass.findAlbaranByNumero(numAlbaran);
        }
        break;
      case genRuta("procesos-vehiculos"):
        const procesosVehiculosClass = await import(
          `../functions/procesosVehiculos.js?${time}`
        );
        procesosVehiculosClass.DTLoad();

        document
          .querySelector("#limpiarEstado")
          .addEventListener("click", procesosVehiculosClass.limpiarEstado);

        document
          .querySelectorAll('input[name="filtroEstado"]')
          .forEach((input) => {
            input.addEventListener("change", function () {
              procesosVehiculosClass.recargarTabla();
              if (this.value === "EnProceso") {
                document.querySelector(".procesosVehiculosSpan").textContent =
                  `En proceso`;
              } else {
                document.querySelector(".procesosVehiculosSpan").textContent =
                  `Procesos ${this.value}s`;
              }
            });
          });

        const procesosVehiculosTable = document.querySelector(
          "#procesosVehiculosTable",
        );
        procesosVehiculosTable.addEventListener("click", function (event) {
          if (event.target.classList.contains("cambiar-estado")) {
            console.log("click");

            procesosVehiculosClass.cambiarEstadoProceso(event);
          }
        });

        break;
      case genRuta("seguimiento-trabajos"):
        const SeguimientoTrabajosClass = await import(
          `../functions/seguimientoTrabajos.js?${time}`
        );
        SeguimientoTrabajosClass.DTLoad();

        document
          .querySelector("#limpiarEstado")
          .addEventListener("click", SeguimientoTrabajosClass.limpiarEstado);

        document
          .querySelectorAll('input[name="filtroEstado"]')
          .forEach((input) => {
            input.addEventListener("change", function () {
              SeguimientoTrabajosClass.recargarTabla();
              if (this.value === "EnProceso") {
                document.querySelector(".seguimientoTrabajosSpan").textContent =
                  `En proceso`;
              }
              if (this.value === "CasoEspecial") {
                document.querySelector(".seguimientoTrabajosSpan").textContent =
                  `Casos Especiales`;
              } else {
                document.querySelector(".seguimientoTrabajosSpan").textContent =
                  `Procesos ${this.value}s`;
              }
            });
          });
        break;
      case genRuta("reportes"):
        const reportesClass = await import(`../functions/reportes.js?${time}`);
        reportesClass.DTLoad();
        const buscarButton = document.querySelector(".buscarButton");
        buscarButton.addEventListener("click", function () {
          reportesClass.recargarTabla();
        });

        const selectEntidad = document.getElementById("entidad");

        // Inicializar con los estados de siniestros (valor por defecto)
        reportesClass.actualizarEstados("siniestros");

        // Escuchar cambios en el select de entidad
        selectEntidad.addEventListener("change", function () {
          reportesClass.actualizarEstados(this.value);
        });
        break;
      default:
        break;
    }
  }
}
export default App;
