import { delegacionEvento, genRuta, URLactual, Perfil } from "../functions/const.js";

class App {
  constructor() {
    this.init();

  }

  async init() {
    let presupuestosClass, agregarColumnaBtn, eliminarColumnaBtn, tallerSelect, folio, numVale, valesClass, entradasClass, albaranesClass, numValeInput, numOrden;
    const time = new Date().getTime();

    switch (true) {

      case URLactual.includes("/login"):
        const L = await import("../functions/login.js");
        document.querySelector("#loginForm").addEventListener("submit", L.login);

        break;

      case URLactual.includes("/siniestros"):

        const siniestrosClass = await import(`../functions/siniestros.js?${time}`);

        siniestrosClass.cargarTD();

        delegacionEvento('ver-detalles-partes', 'click', siniestrosClass.verDetallePiezas);

        //localStorage del filtro
        siniestrosClass.getFiltro();
        siniestrosClass.setTitulo();
        document.querySelectorAll('input[name="filtroEstado"]').forEach(input => {
          input.addEventListener('change', () => {
            siniestrosClass.setFiltro();
            siniestrosClass.recargarTabla();
            siniestrosClass.setTitulo();
          });
        });

        //formAgregarSiniestro
        const siniestroForm = document.querySelector("#agregarSiniestroForm");

        if (siniestroForm) {
          const tallerSelectModal = siniestroForm.querySelector("#taller");
          const numeroOrdenInputModal = siniestroForm.querySelector("#numeroOrden");
          const numeroSiniestroInputModal = siniestroForm.querySelector("#numeroSiniestro");

          // Limpiar validación al escribir o cambiar de taller
          [tallerSelectModal, numeroOrdenInputModal, numeroSiniestroInputModal].forEach(el => {
            const evento = el.tagName === 'SELECT' ? 'change' : 'input';
            el.addEventListener(evento, () => {
              numeroOrdenInputModal.setCustomValidity("");
              numeroSiniestroInputModal.setCustomValidity("");
            });
          });

          //Agregar siniestro
          siniestroForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const esValido = await siniestrosClass.validarCamposFormSiniestro(e);
            if (esValido) {
              siniestrosClass.agregarSiniestro(e);
            }
          });
        }

        //Forms para agregar Marca, Vehiculo y Aseguradora
        siniestrosClass.initFormsMVA();

        //CRUD Siniestro
        document.addEventListener("click", (event) => {
          const acciones = {
            cancelSiniestroButton: () => siniestrosClass.cancelarSiniestro(event),
            notificarCancelacionButton: () => siniestrosClass.notificarCancelacion(event),
            cerrarSiniestroButton: () => siniestrosClass.cerrarSiniestro(event),
            reabrirSiniestroButton: () => siniestrosClass.reabrirSiniestro(event),
          };

          for (const [selector, fn] of Object.entries(acciones)) {
            if (event.target.closest(`.${selector}`)) {
              event.preventDefault();
              fn();
              return;
            }
          }

          //para borrar ya se implemento una nueva function: detalle-partes

          //modal para ver los detalles de los siniestros
          // const openModalBtn = event.target.closest(".open-modal");
          // if (openModalBtn) {
          //   event.preventDefault();
          //   const { modalTarget, numorden, tipoModal } = openModalBtn.dataset;
          //   siniestrosClass.abrirModal(modalTarget, numorden, tipoModal);
          //   return;
          // }

          // const closeModalBtn = event.target.closest(".close-modal");
          // if (closeModalBtn) {
          //   event.preventDefault();
          //   siniestrosClass.cerrarModal(closeModalBtn.dataset.modalToggle);
          // }
        });

        //para borrar es para limpiar el estado y el buscador de sineistros

        // document
        //   .querySelector("#limpiarEstado")
        //   .addEventListener("click", siniestrosClass.limpiarEstado);
        // // Sí, así está bien, pero falta llamar la función recargarTabla correctamente (faltan los paréntesis).
        // document
        //   .querySelectorAll('input[name="filtroEstado"]')
        //   .forEach((input) => {
        //     input.addEventListener("change", function () {
        //       siniestrosClass.recargarTabla();
        //       const taller = document.querySelector("#talleres").value;
        //       document.querySelector(".siniestrosSpan").textContent =
        //         `Siniestros de ${taller.toLowerCase()} ${this.value.toLowerCase()}s`;
        //     });
        //   });

        // document
        //   .querySelector("#simple-search")
        //   .addEventListener("keyup", (e) => {
        //     siniestrosClass.buscar(e);
        //   });

        // document
        //   .querySelector("#filterDropdown")
        //   .addEventListener("change", function () {
        //     siniestrosClass.setLocalStorageFiltro();
        //   });

        // document
        //   .querySelector("#switchVerTodos")
        //   .addEventListener("change", (e) => {
        //     // La lógica de URL con/sin check está en const.js
        //     siniestrosClass.setLocalStorageVerTodos(e);
        //     siniestrosClass.recargarTabla();
        //   });

        //para Borrar alv

        // document
        //   .querySelectorAll('select[name="talleres"]')
        //   .forEach((input) => {
        //     input.addEventListener("change", function (e) {
        //       siniestrosClass.setLocalStorageTaller(e); // Guardar en localStorage
        //       siniestrosClass.recargarTabla();
        //       const filtroEstado = document.querySelector(
        //         "input[name='filtroEstado']:checked",
        //       );

        //       document.querySelector(".siniestrosSpan").textContent =
        //         `Siniestros de ${this.value.toLowerCase()} ${filtroEstado.value.toLowerCase()}s`;
        //     });
        //   });

        // document.querySelectorAll('select[name="entidad"]').forEach((input) => {
        //   input.addEventListener("change", function () {
        //     siniestrosClass.recargarTabla();
        //   });
        // });

        // document.querySelector("#talleres").addEventListener("change", (e) => {
        //   siniestrosClass.btnBuscar(e);
        //   siniestrosClass.setLocalStorageTaller(e);
        // });



        break;

      /*------------------------------------------------------------------------------------presupuestoss--------------------------------------------------------------*/
      case URLactual.endsWith("/presupuestos"):
        presupuestosClass = await import(
          `../functions/presupuestos.js?${time}`
        );

        presupuestosClass.cargarTD();

        $("#presupuestosTable")
          .DataTable()
          .on("draw", function () {
            presupuestosClass.initTooltips();
          });

        //filtrado por estado (manejado internamente por cargarTD)
        document.querySelector("#limpiarEstado").addEventListener("click", presupuestosClass.limpiarEstado);


        // tallerSelect = document.querySelectorAll('select[name="talleres"]');
        // tallerSelect.forEach((input) => {
        //   input.addEventListener("change", presupuestosClass.recargarTabla);
        // });

        document.addEventListener("click", (e) => {
          const btn = e.target.closest(".subirEvidencias");
          if (btn) {
            e.preventDefault();
            const folioBtn = btn.getAttribute("data-folio");
            presupuestosClass.agregarEvidencias(folioBtn);
          }
        });

        break;
      case URLactual.includes("/presupuestos/ver"):

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

      /*------------------------------------------------------------------------------------presupuestos/crear --------------------------------------------------------------*/
      case URLactual.includes("/presupuestos/crear"):

        presupuestosClass = await import(
          `../functions/presupuestos.js?${time}`
        );

        agregarColumnaBtn = document.getElementById("agregarColumnaPresupuesto",);
        eliminarColumnaBtn = document.getElementById("eliminarColumnaPresupuesto",);

        agregarColumnaBtn?.addEventListener("click", () => presupuestosClass.agregarColumnaPresupuesto("crear"),);
        eliminarColumnaBtn?.addEventListener("click", presupuestosClass.eliminarColumnaPresupuesto,);

        //rellena el presupuesto al inicializarlo si viene numOrden en la url
        presupuestosClass.initPresupuesto();
        /*listo hasta aca - fin */

        delegacionEvento("numero-parte", "keyup", function (e) {
          const codigo = document.getElementById("codigo")?.value || "";
          presupuestosClass.DatosByNumeroParte(e.value, e.dataset.conteo, codigo);
        });

        /*pendiente de revisar*/
        delegacionEvento("proveedor", "change", function (e) {
          presupuestosClass.consultarDatosByProveedor(e);
        });
        /*pendiente de revisar*/
        delegacionEvento("cantidad", "keyup", function (e) {
          presupuestosClass.calcularTotalPorFila(e.dataset.conteo);
        });

        /*init */
        const nuevoPresupuestoForm = document.querySelector(
          "#nuevoPresupuestoForm",
        );
        nuevoPresupuestoForm.addEventListener("submit", (e) => {
          presupuestosClass.agregarPresupuesto(e);
        });
        /*fin de refact*/
        break;

      /*------------------------------------------------------------------------------------presupuestos/cotizar --------------------------------------------------------------*/
      case URLactual.includes("/presupuestos/cotizar"):
        presupuestosClass = await import(
          `../functions/presupuestos.js?${time}`
        );

        folio = new URLSearchParams(window.location.search).get("folio");
        if (folio) {
          document.querySelector(".texto-presupuesto").textContent = `Cotizar presupuesto ${folio}`;
          await presupuestosClass.findPresupuestoByNumero(folio, "cotizar");
        }

        presupuestosClass.initCodigoAutocar();
        presupuestosClass.initCheckPVP();

        agregarColumnaBtn = document.getElementById("agregarColumnaPresupuesto");
        eliminarColumnaBtn = document.getElementById("eliminarColumnaPresupuesto");

        let codigo = "";
        if (document.querySelector("#proveedor").value === "CHEVROLET") {
          delegacionEvento("numero-parte", "keyup", function (e) {
            const codigo = presupuestosClass.obtenerCodigoCliente();
            if (!codigo) return;

            presupuestosClass.DatosByNumeroParte(e.value, e.dataset.conteo, codigo);
          });
        }

        delegacionEvento("precio-unitario", "keyup", function (e) { presupuestosClass.calcularTotalPorFila(e.dataset.conteo); });
        delegacionEvento("cantidad", "keyup", function (e) { presupuestosClass.calcularTotalPorFila(e.dataset.conteo); });

        // Guardado dinámico y añadir filas si tiene permisos
        const puedeActualizar = document.getElementById("canUpdatePresupuesto")?.value === "true";
        if (puedeActualizar) {
          agregarColumnaBtn?.addEventListener("click", () => presupuestosClass.agregarColumnaPresupuesto("cotizar"));
          eliminarColumnaBtn?.addEventListener("click", () => presupuestosClass.eliminarColumnaPresupuesto("cotizar"));

          // Botón editar descripción (delegación en tbody para elementos dinámicos)
          const tbodyCotizar = document.querySelector("#cotizarPresupuestoTable tbody");
          if (tbodyCotizar) {
            tbodyCotizar.addEventListener("click", (e) => {
              const btn = e.target.closest(".btn-editar-descripcion");
              if (!btn) return;
              const conteo = btn.dataset.conteo;
              const inputDesc = document.querySelector(`input[name="descripcion_${conteo}"]`);
              if (!inputDesc) return;

              if (inputDesc.disabled) {
                // Habilitar edición
                inputDesc.disabled = false;
                inputDesc.classList.add("ring-2", "ring-yellow-400");
                inputDesc.focus();
                btn.innerHTML = `<i class="fa-solid fa-check text-xs"></i>`;
                btn.classList.remove("text-yellow-600", "hover:text-yellow-800", "dark:text-yellow-400", "dark:hover:text-yellow-300");
                btn.classList.add("text-green-600", "hover:text-green-800", "dark:text-green-400", "dark:hover:text-green-300");
              } else {
                // Guardar y bloquear
                inputDesc.disabled = true;
                inputDesc.classList.remove("ring-2", "ring-yellow-400");
                btn.innerHTML = `<i class="fa-solid fa-pen-to-square text-xs"></i>`;
                btn.classList.remove("text-green-600", "hover:text-green-800", "dark:text-green-400", "dark:hover:text-green-300");
                btn.classList.add("text-yellow-600", "hover:text-yellow-800", "dark:text-yellow-400", "dark:hover:text-yellow-300");
              }
            });
          }
        }

        const cotizarPresupuestoForm = document.querySelector("#cotizarPresupuestoForm");
        if (cotizarPresupuestoForm) {
          cotizarPresupuestoForm.addEventListener("submit", (e) => {
            presupuestosClass.cotizarPresupuesto(e, cotizarPresupuestoForm);
          });
        }

        // const proveedorInput = document.querySelector("#proveedor");
        // if (proveedorInput) {
        //   presupuestosClass.consultarDatosByProveedor(proveedorInput);
        // }

        break;

      case URLactual.endsWith("/vales"):
        valesClass = await import(`../functions/vales.js?${time}`);

        valesClass.DTLoad();

        $("#presupuestosTable")
          .DataTable()
          .on("draw", function () {
            valesClass.initTooltips();
          });

        delegacionEvento("cancelValeButton", "click", valesClass.cancelarVale);

        document.querySelector("#limpiarEstado").addEventListener("click", valesClass.limpiarEstado);
        document.querySelectorAll('input[name="filtroEstado"]').forEach((input) => {
          input.addEventListener("change", valesClass.recargarTabla);
        });

        document.querySelectorAll('input[name="filtroEstado"]').forEach((input) => {
          input.addEventListener("change", function () {
            valesClass.recargarTabla();
            document.querySelector(".valesSpan").textContent = `Vales ${this.value}s`;
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

      case URLactual.includes("/vales/ver"):
        valesClass = await import(`../functions/vales.js?${time}`);
        const valesParams = new URLSearchParams(window.location.search);
        numVale = valesParams.get("numVale");
        const idVale = valesParams.get("idVale");
        if (numVale) {
          document.querySelector(".texto-vale").textContent = `Vale ${numVale}`;
          valesClass.findValeByNumero(numVale, "ver", idVale);
        }

        //evento para asignar albaran y entrada
        const setupModalAsignacion = (tipo) => {
          const btn = document.getElementById(`asignar${tipo}Button`);
          const form = document.getElementById(`asignar${tipo}Form`);

          if (btn) {
            btn.addEventListener("click", async () => {
              if (tipo === "Albaran") {
                const puede = await valesClass.validarSurtidoAlbaran(numVale);
                if (puede) valesClass.asignarAlbaran();
              } else {
                valesClass.asignarEntrada();
              }
            });
          }

          if (form) {
            form.addEventListener("submit", (e) => {
              e.preventDefault();
              const proveedor = tipo === "Entrada" ? (document.getElementById("proveedor")?.value || "") : null;
              const matchMethod = tipo === "Entrada"
                ? valesClass.validarMatchEntrada(e, numVale, proveedor)
                : valesClass.validarMatchAlbaran(e, numVale);

              matchMethod.then((match) => {
                if (match) {
                  valesClass[`submitAsignar${tipo}`](e, numVale);
                }
              });
            });
          }
        };

        setupModalAsignacion("Albaran");
        setupModalAsignacion("Entrada");

        const entrada = document.getElementById("entrada");
        if (entrada) {
          const procesarEntrada = async (e) => {
            if (e.type === "keydown" && e.key !== "Enter") return;
            if (e.type === "keydown") e.preventDefault(); // Solo previene para este campo específico

            const spinner = document.querySelector("#spinner-entrada");
            const tabla = document.querySelector("#datosEntradaTable");
            const valor = entrada.value.trim();

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
          };

          entrada.addEventListener("keydown", procesarEntrada);
          entrada.addEventListener("change", procesarEntrada);
        }



        const albaran = document.getElementById("albaran");
        if (albaran) {
          const procesarAlbaran = async (e) => {
            if (e.type === "keydown" && e.key !== "Enter") return;
            if (e.type === "keydown") e.preventDefault(); // Solo previene para este campo específico

            const spinner = document.querySelector("#spinner-albaran");
            const tabla = document.querySelector("#datosAlbaranTable");
            const valor = albaran.value.trim();

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
          };

          albaran.addEventListener("keydown", procesarAlbaran);
          albaran.addEventListener("change", procesarAlbaran);

          delegacionEvento("numeroParte", "keyup", function (e) {
            const aseguradora = document.getElementById("aseguradora")?.value || "";
            valesClass.consultarDatosByNumeroParte(e.value, e.dataset.conteo, aseguradora);
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

        const btnCancelarModificaciones = document.getElementById("cancelarModificacionPartesButton");
        if (btnCancelarModificaciones) {
          btnCancelarModificaciones.addEventListener("click", () => {
            location.reload();
          });
        }

        delegacionEvento("pedirModificacionButton", "click", function (e) {
          valesClass.pedirModificacion(e, numVale);
        });

        //ERNESTO -- VER ENTRADAS (movido fuera del if albaran)
        document.addEventListener("click", (e) => {
          const btnAbrirVale = e.target.closest(".verEntrada");

          if (btnAbrirVale) {
            valesClass.verEntradas(e);
          }
        });

        break;
      case URLactual.includes("/vales/asignar"):
        valesClass = await import(`../functions/vales.js?${time}`);
        folio = new URLSearchParams(window.location.search).get("folio");
        if (folio) {
          document.querySelector(".numPresupuestoSpan").textContent = `Presupuesto ${folio}`;
          await valesClass.findPresupuestoByNumero(folio);
          await valesClass.cargarPiezasDisponibles(folio);
        }

        delegacionEvento("filaCheckBox", "click", (e) => {
          valesClass.calcularCantidades();
          // valesClass.asignarFaltantes();
        });

        // Manejar cantidades al agregar vale (delegación de eventos para elementos dinámicos)
        document.addEventListener("input", (e) => {
          if (e.target && e.target.name === "cantidad") {
            valesClass.calcularCantidades();
          }
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
      case URLactual.endsWith("/entradas"):
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
      case URLactual.includes("/entradas/detalle"):
        entradasClass = await import(`../functions/entradas.js?${time}`);
        document.querySelectorAll(".liberarParteButton").forEach((f) =>
          f.addEventListener("click", function (e) {
            entradasClass.liberarPartes(this);
          }),
        );
        break;
      case URLactual.endsWith("/albaranes"):
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
      case URLactual.includes("/albaranes/ver"):
        albaranesClass = await import(`../functions/albaranes.js?${time}`);

        const numAlbaran = new URLSearchParams(window.location.search).get(
          "numAlbaran",
        );
        if (numAlbaran) {
          document.querySelector(".texto-albaran").textContent =
            `Albaran ${numAlbaran}`;
          await albaranesClass.findAlbaranByNumero(numAlbaran);
        }
        document
          .querySelector("#verAlbaranTable")
          ?.addEventListener("click", (event) => {
            const button = event.target.closest(".liberarParteButton");
            if (button) {
              albaranesClass.liberarPartes(button);
            }
          });
        break;
      case URLactual.includes("/procesos-vehiculos"):
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
      case URLactual.includes("/seguimiento-trabajos"):
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
      case URLactual.includes("/reportes") && !URLactual.includes("/reportes/refacciones"):
        const reportesClass = await import(`../functions/reportes.js?${time}`);
        reportesClass.DTLoad();

        // Botón buscar → recarga dashboard + tabla
        const btnBuscar = document.getElementById("btnBuscar");
        if (btnBuscar) {
          btnBuscar.addEventListener("click", () => {
            reportesClass.recargarTabla();
          });
        }

        // Botón exportar Excel
        const btnExcelExport = document.getElementById("btnExcelExport");
        if (btnExcelExport) {
          btnExcelExport.addEventListener("click", () => {
            reportesClass.exportarExcel();
          });
        }

        // Actualizar estados cuando cambia la entidad
        const selectEntidad = document.getElementById("entidad");
        if (selectEntidad) {
          reportesClass.actualizarEstados("siniestros");
          selectEntidad.addEventListener("change", function () {
            reportesClass.actualizarEstados(this.value);
          });
        }
        break;
      case URLactual.includes("/administracion/permisos"):
        const permisosModule = await import(`../functions/permisos.js?${time}`);
        permisosModule.initPermisos();
        break;

      default:
        break;
    }
  }
}
export default App;
