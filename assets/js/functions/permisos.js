import { UrlProyecto, Perfil, swalAlert, showLoading, hideLoading, token } from "./const.js";

/**
 * Obtiene el ID correspondiente a la opción seleccionada en un datalist o en un select.
 */
function getIdFromDatalist(inputId, datalistId) {
    const input = document.getElementById(inputId);
    if (!input) return null;

    // Si es un <select>, leer directamente el value y el dataset del option seleccionado
    if (input.tagName === "SELECT") {
        const selectedOpt = input.options[input.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) return null;
        return parseInt(selectedOpt.dataset.id) || null;
    }

    // Si es un <input list="...">, buscar coincidencia exacta en el datalist
    const datalist = document.getElementById(datalistId);
    if (!datalist) return null;
    const valor = input.value.trim();
    if (!valor) return null;
    for (const opt of datalist.options) {
        if (opt.value === valor) return parseInt(opt.dataset.id);
    }
    return null;
}

/**
 * Filtra el select de roles según el perfil seleccionado.
 * Acepta un idPerfilForzado para usarlo directamente sin leer el datalist
 * (útil cuando el campo fue rellenado programáticamente).
 */
function filtrarRolesPorPerfil(idPerfilForzado = null) {
    const idPerfil = idPerfilForzado ?? getIdFromDatalist("buscar_perfil", "perfiles");
    const selectRol = document.getElementById("buscar_rol");
    const hint = document.getElementById("lbl-rol-hint");

    // Leer todos los roles desde el JSON embebido en el DOM
    const rolesData = JSON.parse(
        document.getElementById("roles-data")?.dataset.roles || "[]"
    );

    // Limpiar opciones actuales y ocultar sección de permisos solo si el cambio viene de fuera del modal
    selectRol.innerHTML = '<option value="">Selecciona un rol</option>';
    const modalSeleccionar = document.getElementById("modal-seleccionar-rol");
    const modalEstaAbierto = modalSeleccionar && !modalSeleccionar.classList.contains("hidden");
    if (idPerfilForzado === null && !modalEstaAbierto) {
        document.getElementById("seccion-permisos-activos")?.classList.add("hidden");
        document.getElementById("seccion-permisos-vacia")?.classList.remove("hidden");
    }

    if (!idPerfil) {
        selectRol.disabled = true;
        if (hint) hint.textContent = "Selecciona sucursal primero.";
        return;
    }

    // Filtrar roles del perfil y poblar el select
    const rolesFiltrados = rolesData.filter(r => String(r.id_perfil) === String(idPerfil));

    rolesFiltrados.forEach(r => {
        const opt = document.createElement("option");
        opt.value = r.rol;
        opt.dataset.id = r.id;
        opt.textContent = r.rol;
        selectRol.appendChild(opt);
    });

    selectRol.disabled = false;
    if (hint) hint.textContent = `${rolesFiltrados.length} roles disponibles.`;
}

/**
 * Alterna las clases visuales del label contenedor según el estado del checkbox.
 */
window.toggleLabelActivo = function (checkbox) {
    const label = checkbox.closest("label");
    const badge = label.querySelector(".permiso-estado-badge");
    const name = label.querySelector(".permiso-nombre");

    if (checkbox.checked) {
        label.classList.remove("bg-gray-900", "border-gray-800", "text-gray-400");
        if (name) name.classList.remove("text-gray-400");

        label.classList.add("bg-blue-950/60", "border-blue-500", "text-slate-50");
        if (name) name.classList.add("text-slate-50");

        if (badge) {
            badge.className = "permiso-estado-badge ml-auto inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300";
            badge.innerHTML = '<i class="fa-solid fa-check"></i>Extra';
        }
    } else {
        label.classList.remove("bg-blue-950/60", "border-blue-500", "text-slate-50");
        if (name) name.classList.remove("text-slate-50");

        label.classList.add("bg-gray-900", "border-gray-800", "text-gray-400");
        if (name) name.classList.add("text-gray-400");

        if (badge) {
            badge.className = "permiso-estado-badge ml-auto inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold bg-gray-800 text-gray-400 dark:bg-gray-850/80 dark:text-gray-400";
            badge.textContent = "Off";
        }
    }
}

/**
 * Consulta el rol actualmente asignado al usuario seleccionado,
 * muestra el banner informativo y auto-completa los campos de Perfil y Rol.
 */
// Variables de estado del módulo
let estadoEdicionModal = { idUsuario: null, idRol: null, idPerfil: null, modo: 'principal' };
let datosUsuarioActual = { rolPrincipal: null, rolesSecundarios: [] };

/**
 * Consulta el rol actualmente asignado al usuario seleccionado,
 * muestra el banner informativo y auto-completa los campos de Perfil y Rol.
 */
async function consultarRolActualUsuario() {
    const idUsuario = getIdFromDatalist("buscar_usuario", "usuarios");
    const banner = document.getElementById("banner-rol-actual");
    const seccionVacia = document.getElementById("seccion-permisos-vacia");
    const seccionActivos = document.getElementById("seccion-permisos-activos");

    if (!idUsuario) {
        if (banner) {
            banner.classList.add("hidden");
            banner.classList.remove("flex");
            banner.dataset.rolPrincipal = "";
            banner.dataset.rolesSecundarios = "";
        }
        if (seccionVacia) seccionVacia.classList.remove("hidden");
        if (seccionActivos) seccionActivos.classList.add("hidden");

        document.getElementById("btnAsignarPrincipal")?.classList.add("hidden");
        document.getElementById("btnEditarPrincipal")?.classList.add("hidden");
        return;
    }

    try {
        const baseUrl = (Perfil && Perfil !== 'administracion') ? `${UrlProyecto}/${Perfil}` : `${UrlProyecto}`;
        const url = `${baseUrl}/administracion/permisos/get-rol-actual-usuario?id_usuario=${idUsuario}`;
        const res = await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } });
        const data = await res.json();
        console.log("Rol actual consultado:", data);

        if (data.rol) {
            datosUsuarioActual.rolPrincipal = data.rol;
            datosUsuarioActual.rolesSecundarios = data.rol.rolesSecundarios || [];

            if (banner) {
                const bannerRolNombre = document.getElementById("banner-rol-nombre");
                if (bannerRolNombre) bannerRolNombre.textContent = data.rol.nombre;
                const bannerRolPerfil = document.getElementById("banner-rol-perfil");
                if (bannerRolPerfil) bannerRolPerfil.textContent = data.rol.perfil ?? "Sin sucursal";

                const contenedorSecundarios = document.getElementById("banner-roles-secundarios-container");
                const spanSecundarios = document.getElementById("banner-roles-secundarios");
                if (data.rol.rolesSecundarios && data.rol.rolesSecundarios.length > 0) {
                    const htmlSecundarios = data.rol.rolesSecundarios.map(r => {
                        const sucursal = r.perfil ?? "Sin sucursal";
                        return `<span class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2 py-0.5 text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
                            <span class="font-semibold">${r.nombre}</span>
                            <span class="text-blue-400 dark:text-blue-600">/</span>
                            <span class="opacity-90">${sucursal}</span>
                        </span>`;
                    }).join('');
                    if (spanSecundarios) spanSecundarios.innerHTML = htmlSecundarios;
                    if (contenedorSecundarios) {
                        contenedorSecundarios.classList.remove("hidden");
                        contenedorSecundarios.classList.add("flex");
                    }
                } else {
                    if (spanSecundarios) spanSecundarios.innerHTML = "";
                    if (contenedorSecundarios) {
                        contenedorSecundarios.classList.add("hidden");
                        contenedorSecundarios.classList.remove("flex");
                    }
                }

                banner.dataset.rolPrincipal = data.rol.id;
                banner.dataset.rolesSecundarios = data.rol.rolesSecundarios ? data.rol.rolesSecundarios.map(r => r.id).join(',') : '';
                banner.classList.remove("hidden");
                banner.classList.add("flex");
            }

            if (seccionVacia) seccionVacia.classList.add("hidden");
            if (seccionActivos) seccionActivos.classList.remove("hidden");

            document.getElementById("panel-principal-rol").textContent = data.rol.nombre;
            document.getElementById("panel-principal-sucursal").textContent = `Sucursal: ${data.rol.perfil ?? "Sin sucursal"}`;

            document.getElementById("btnEditarPrincipal")?.classList.remove("hidden");
            document.getElementById("btnAsignarPrincipal")?.classList.add("hidden");

            // Habilitar botón de agregar secundarios
            const btnAgregarSec = document.getElementById("btnAgregarSecundario");
            if (btnAgregarSec) {
                btnAgregarSec.disabled = false;
                btnAgregarSec.title = "";
                btnAgregarSec.classList.remove("opacity-50", "cursor-not-allowed");
            }

            renderizarPermisosActuales(idUsuario, data.rol, data.rol.rolesSecundarios || []);

        } else {
            datosUsuarioActual.rolPrincipal = null;
            datosUsuarioActual.rolesSecundarios = [];

            if (banner) {
                banner.dataset.rolPrincipal = "";
                banner.dataset.rolesSecundarios = "";
                banner.classList.add("hidden");
                banner.classList.remove("flex");
            }

            if (seccionVacia) seccionVacia.classList.add("hidden");
            if (seccionActivos) seccionActivos.classList.remove("hidden");

            document.getElementById("panel-principal-rol").textContent = "Sin Rol Principal Asignado";
            document.getElementById("panel-principal-sucursal").textContent = "Asigna un rol principal al usuario.";
            document.getElementById("principal-permisos-lista").innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center w-full min-h-[220px]">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-yellow-500/10 text-yellow-500 dark:bg-yellow-950/40 dark:text-yellow-400 mb-4 shadow-inner ring-1 ring-yellow-500/20">
                        <i class="fa-solid fa-user-shield text-2xl animate-pulse"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Sin Permisos Base</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs leading-relaxed">
                        Debe tener un rol principal asignado para poseer y visualizar los permisos base.
                    </p>
                </div>
            `;
            document.getElementById("principal-permisos-loading").classList.add("hidden");

            // Ocultar card secundarios y expandir principal al haber sin rol
            const cardSecundarios = document.getElementById("card-secundarios");
            const gridPermisos = document.getElementById("grid-permisos");
            if (cardSecundarios) cardSecundarios.classList.add("hidden");
            if (gridPermisos) {
                gridPermisos.classList.remove("lg:grid-cols-2");
            }
            document.getElementById("secundarios-permisos-loading")?.classList.add("hidden");

            document.getElementById("btnEditarPrincipal")?.classList.add("hidden");
            document.getElementById("btnAsignarPrincipal")?.classList.remove("hidden");

            // Deshabilitar botón de agregar secundarios hasta que exista un rol principal
            const btnAgregarSec = document.getElementById("btnAgregarSecundario");
            if (btnAgregarSec) {
                btnAgregarSec.disabled = true;
                btnAgregarSec.title = "Primero debes asignar un rol principal al usuario.";
                btnAgregarSec.classList.add("opacity-50", "cursor-not-allowed");
            }
        }

    } catch (err) {
        if (banner) {
            banner.classList.add("hidden");
            banner.classList.remove("flex");
            banner.dataset.rolPrincipal = "";
            banner.dataset.rolesSecundarios = "";
        }
        console.error("Error consultando rol actual:", err);
    }
}

/**
 * Renderiza los permisos activos principales y secundarios en formato compacto.
 */
async function renderizarPermisosActuales(idUsuario, rolPrincipal, rolesSecundarios) {
    const loadingPrincipal = document.getElementById("principal-permisos-loading");
    const listaPrincipal = document.getElementById("principal-permisos-lista");

    if (loadingPrincipal) loadingPrincipal.classList.remove("hidden");
    if (listaPrincipal) listaPrincipal.innerHTML = "";

    try {
        if (rolPrincipal) {
            const data = await obtenerPermisosActivosDeCombinacion(idUsuario, rolPrincipal.id, rolPrincipal.id_perfil);
            if (loadingPrincipal) loadingPrincipal.classList.add("hidden");

            renderizarBadgesCompactos(data.permisos, listaPrincipal);

            const btnEditarPrincipal = document.getElementById("btnEditarPrincipal");
            if (btnEditarPrincipal) {
                btnEditarPrincipal.onclick = () => abrirModalEdicion(idUsuario, rolPrincipal.id, rolPrincipal.id_perfil, 'principal');
            }
        } else {
            if (loadingPrincipal) loadingPrincipal.classList.add("hidden");
            if (listaPrincipal) {
                listaPrincipal.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-16 px-4 text-center w-full min-h-[220px]">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-yellow-500/10 text-yellow-500 dark:bg-yellow-950/40 dark:text-yellow-400 mb-4 shadow-inner ring-1 ring-yellow-500/20">
                            <i class="fa-solid fa-user-shield text-2xl"></i>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Sin Rol Principal</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs leading-relaxed">
                            No se ha asignado un rol principal al usuario seleccionado.
                        </p>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error("Error al renderizar permisos principales:", error);
        if (loadingPrincipal) loadingPrincipal.classList.add("hidden");
        if (listaPrincipal) listaPrincipal.innerHTML = '<span class="text-xs text-red-500">Error al cargar permisos principales.</span>';
    }

    const loadingSecundarios = document.getElementById("secundarios-permisos-loading");
    const contenedorSecundarios = document.getElementById("secundarios-roles-contenedor");

    if (loadingSecundarios) loadingSecundarios.classList.remove("hidden");
    if (contenedorSecundarios) contenedorSecundarios.innerHTML = "";

    try {
        if (rolesSecundarios.length === 0) {
            if (loadingSecundarios) loadingSecundarios.classList.add("hidden");

            // Mostrar card de secundarios y grid de 2 columnas
            const cardSecundarios = document.getElementById("card-secundarios");
            const gridPermisos = document.getElementById("grid-permisos");
            if (cardSecundarios) cardSecundarios.classList.remove("hidden");
            if (gridPermisos) {
                gridPermisos.classList.add("lg:grid-cols-2");
            }

            if (contenedorSecundarios) {
                contenedorSecundarios.innerHTML = `
                    <div class="col-span-full flex flex-col items-center justify-center py-12 px-4 text-center w-full min-h-[220px]">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-500 dark:bg-emerald-950/40 dark:text-emerald-400 mb-4 shadow-inner ring-1 ring-emerald-500/20">
                            <i class="fa-solid fa-user-plus text-2xl animate-pulse"></i>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Sin Roles Secundarios</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs leading-relaxed">
                            El usuario no tiene accesos en otras sucursales. Haz clic en "Agregar Rol" para asignarle uno.
                        </p>
                    </div>
                `;
            }
            return;
        }

        // Hay roles secundarios: mostrar card y restaurar grid de 2 columnas
        const cardSecundariosVisible = document.getElementById("card-secundarios");
        const gridPermisosVisible = document.getElementById("grid-permisos");
        if (cardSecundariosVisible) cardSecundariosVisible.classList.remove("hidden");
        if (gridPermisosVisible) {
            gridPermisosVisible.classList.add("lg:grid-cols-2");
        }

        const promesas = rolesSecundarios.map(r => obtenerPermisosActivosDeCombinacion(idUsuario, r.id, r.id_perfil));
        const resultados = await Promise.all(promesas);

        if (loadingSecundarios) loadingSecundarios.classList.add("hidden");

        rolesSecundarios.forEach((sec, index) => {
            const data = resultados[index];
            const divRolSecundario = document.createElement("div");
            divRolSecundario.className = "rounded-lg border border-emerald-200/50 bg-emerald-50/30 p-3 dark:border-emerald-800/40 dark:bg-emerald-950/10";

            divRolSecundario.innerHTML = `
                <div class="header-rol-secundario-click rounded p-1 mb-2 flex flex-wrap items-start justify-between gap-2 border-b border-emerald-200/50 pb-2 dark:border-emerald-800/30">
                    <div class="min-w-0 flex-1">
                        <h4 class="text-[11px] font-bold text-gray-900 dark:text-white uppercase tracking-wide truncate">${sec.nombre}</h4>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">Sucursal: ${sec.perfil ?? "Sin sucursal"}</p>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <button type="button" class="btn-editar-secundario inline-flex items-center gap-1 rounded border border-purple-300 bg-purple-50 px-2 py-0.5 text-[10px] font-semibold text-purple-700 hover:bg-purple-100 dark:bg-purple-950/40 dark:border-purple-700 dark:text-purple-300 dark:hover:bg-purple-900/50 transition">
                            <i class="fa-solid fa-pen-to-square"></i> Editar
                        </button>
                        <button type="button" class="btn-quitar-secundario inline-flex items-center gap-1 rounded border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700 hover:bg-red-100 dark:bg-red-950/40 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-900/50 transition">
                            <i class="fa-solid fa-trash-can"></i> Quitar
                        </button>
                    </div>
                </div>
                <div class="secundario-permisos-lista flex flex-wrap gap-1 min-h-[24px]"></div>
            `;

            const listaPermisosSecundario = divRolSecundario.querySelector(".secundario-permisos-lista");
            renderizarBadgesCompactos(data.permisos, listaPermisosSecundario);

            divRolSecundario.querySelector(".btn-editar-secundario").onclick = () => abrirModalEdicion(idUsuario, sec.id, sec.id_perfil, 'secundario');
            divRolSecundario.querySelector(".btn-quitar-secundario").onclick = () => quitarRol(sec.id, sec.id_perfil, sec.nombre);

            contenedorSecundarios.appendChild(divRolSecundario);
        });

    } catch (error) {
        console.error("Error al renderizar permisos secundarios:", error);
        if (loadingSecundarios) loadingSecundarios.classList.add("hidden");
        if (contenedorSecundarios) contenedorSecundarios.innerHTML = '<span class="text-xs text-red-500">Error al cargar permisos secundarios.</span>';
    }
}

/**
 * Consulta de forma asíncrona la lista de permisos de una combinación específica.
 */
async function obtenerPermisosActivosDeCombinacion(idUsuario, idRol, idPerfil) {
    const baseUrl = (Perfil && Perfil !== 'administracion') ? `${UrlProyecto}/${Perfil}` : `${UrlProyecto}`;
    const url = `${baseUrl}/administracion/permisos/get-permisos-rol-usuario?id_usuario=${idUsuario}&id_rol=${idRol}&id_perfil=${idPerfil}`;
    const res = await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } });
    return await res.json();
}

/**
 * Renderiza los permisos en formato de badges compactos agrupados por sección
 */
function renderizarBadgesCompactos(permisos, contenedorElemento) {
    if (!permisos || permisos.length === 0) {
        const esPrincipal = contenedorElemento.id === "principal-permisos-lista";
        if (esPrincipal) {
            contenedorElemento.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center w-full min-h-[220px]">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-500/10 text-gray-500 dark:bg-gray-800 dark:text-gray-400 mb-4 shadow-inner ring-1 ring-gray-500/20">
                        <i class="fa-solid fa-triangle-exclamation text-2xl animate-pulse"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Sin Permisos Disponibles</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs leading-relaxed">
                        No hay permisos registrados en este rol o perfil seleccionado.
                    </p>
                </div>
            `;
        } else {
            contenedorElemento.innerHTML = '<p class="text-xs text-gray-500 italic py-2">Sin permisos asignados.</p>';
        }
        return;
    }

    // Mapa de secciones
    const SECCIONES = [
        { prefijos: ['siniestros'], label: 'Siniestros' },
        { prefijos: ['vales', 'ver.vales'], label: 'Vales' },
        { prefijos: ['presupuestos', 'cotizar.presupuestos', 'presupuestos.cotizar'], label: 'Presupuestos' },
        { prefijos: ['entradas'], label: 'Entradas' },
        { prefijos: ['albaranes'], label: 'Albaranes' },
        { prefijos: ['facturas'], label: 'Facturas' },
        { prefijos: ['partes', 'modificacion'], label: 'Partes' },
        { prefijos: ['evidencias'], label: 'Evidencias' },
        { prefijos: ['procesosVehiculos', 'procesoVehiculo'], label: 'Procesos de Vehiculos' },
        { prefijos: ['reportes'], label: 'Reportes' },
        { prefijos: ['seguimientoTrabajo'], label: 'Seguimiento de Trabajo' },
        { prefijos: ['ver.talleres', 'pensiones.vertaller', 'periferico.vertaller'], label: 'Acceso a Talleres' },
        { prefijos: ['descripcionw32'], label: 'Consultas W32' },
        { prefijos: ['autocar', 'refacciones', 'compras'], label: 'Perfil / Accesos Especiales' },
    ];

    function getSeccion(slug) {
        for (const sec of SECCIONES) {
            if (sec.prefijos.some(p => slug === p || slug.startsWith(p + '.'))) {
                return sec.label;
            }
        }
        return 'Otros';
    }

    // Agrupar permisos activos por sección
    const gruposActivos = {};
    let totalActivos = 0;

    permisos.forEach(p => {
        if (p.activo) {
            const sec = getSeccion(p.slug);
            if (!gruposActivos[sec]) gruposActivos[sec] = [];
            gruposActivos[sec].push(p);
            totalActivos++;
        }
    });

    if (totalActivos === 0) {
        const esPrincipal = contenedorElemento.id === "principal-permisos-lista";
        if (esPrincipal) {
            contenedorElemento.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center w-full min-h-[220px]">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-500/10 text-gray-500 dark:bg-gray-800 dark:text-gray-400 mb-4 shadow-inner ring-1 ring-gray-500/20">
                        <i class="fa-solid fa-ban text-2xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Sin Permisos Activos</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs leading-relaxed">
                        Todos los permisos asignados a esta combinación de perfil y rol están inactivos.
                    </p>
                </div>
            `;
        } else {
            contenedorElemento.innerHTML = '<p class="text-xs text-gray-500 italic py-2">Sin permisos heredados ni adicionales activos.</p>';
        }
        return;
    }

    contenedorElemento.innerHTML = "";
    const ordenSecciones = SECCIONES.map(s => s.label).concat(['Otros']);

    ordenSecciones.forEach(nombreSec => {
        const permisosSec = gruposActivos[nombreSec];
        if (!permisosSec || permisosSec.length === 0) return;

        const divSeccion = document.createElement("div");
        divSeccion.className = "w-full mb-3 last:mb-0";
        divSeccion.innerHTML = `
            <h5 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-blue-500/80"></span>
                ${nombreSec}
            </h5>
            <div class="flex flex-wrap gap-1.5 pl-3">
                ${permisosSec.map(p => {
            if (p.es_del_rol) {
                return `<span class="inline-flex items-center gap-1 rounded bg-slate-100 border border-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-700 dark:bg-slate-800/50 dark:border-slate-700/80 dark:text-slate-200" title="Permiso heredado del rol (Fijo)">
                            <i class="fa-solid fa-lock text-[9px] text-slate-400 dark:text-slate-400"></i> ${p.nombre}
                        </span>`;
            } else {
                return `<span class="inline-flex items-center gap-1 rounded bg-blue-50 border border-blue-200 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-950/60 dark:border-blue-800/50 dark:text-blue-200" title="Permiso adicional/extra (Modificable)">
                            <i class="fa-solid fa-check text-[9px] text-blue-500 dark:text-blue-400"></i> ${p.nombre}
                        </span>`;
            }
        }).join('')}
            </div>
        `;
        contenedorElemento.appendChild(divSeccion);
    });
}

/**
 * Abre el modal de edición interactiva de permisos
 */
async function abrirModalEdicion(idUsuario, idRol, idPerfil, modo) {
    const modal = document.getElementById("modal-editar-permisos");
    const loader = document.getElementById("modal-permisos-loading");
    const grid = document.getElementById("modal-permisos-grid");

    if (!modal) return;

    // Guardar estado actual de edición
    estadoEdicionModal = { idUsuario, idRol, idPerfil, modo };

    // Mostrar modal
    modal.classList.remove("hidden");
    loader.classList.remove("hidden");
    loader.classList.add("flex");
    grid.classList.add("hidden");
    grid.innerHTML = "";

    // Configurar información en la cabecera
    const nombreUsuario = document.getElementById("buscar_usuario")?.value || "Usuario";

    // Obtener nombre del rol
    let nombreRol = "Rol";
    const selectRol = document.getElementById("buscar_rol");
    if (selectRol) {
        for (const opt of selectRol.options) {
            if (parseInt(opt.dataset.id) === idRol) {
                nombreRol = opt.value;
                break;
            }
        }
    }

    // Obtener nombre de la sucursal
    let nombreSucursal = "Sucursal";
    const selectPerfil = document.getElementById("buscar_perfil");
    if (selectPerfil) {
        const selectedOpt = selectPerfil.options[selectPerfil.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            nombreSucursal = selectedOpt.value;
        }
    }

    document.getElementById("modal-titulo-usuario").textContent = `Editar Permisos de ${nombreUsuario}`;
    document.getElementById("modal-subtitulo-rol").textContent = `Sucursal: ${nombreSucursal} / Rol: ${nombreRol} (${modo === 'principal' ? 'Rol Principal' : 'Rol Secundario'})`;

    try {
        const data = await obtenerPermisosActivosDeCombinacion(idUsuario, idRol, idPerfil);

        loader.classList.add("hidden");
        loader.classList.remove("flex");
        grid.classList.remove("hidden");

        if (!data.permisos || data.permisos.length === 0) {
            grid.innerHTML = `
                <div class="rounded-lg border border-dashed border-gray-700 p-8 text-center bg-gray-900/50">
                    <i class="fa-solid fa-key text-2xl text-gray-600"></i>
                    <p class="mt-2 text-sm font-semibold text-gray-200">Sin permisos disponibles</p>
                    <p class="mt-1 text-xs text-gray-400">No hay matriz de permisos para esta combinación.</p>
                </div>`;
            return;
        }

        // Mapa de secciones
        const SECCIONES = [
            { prefijos: ['siniestros'], label: 'Siniestros', icono: 'fa-car-burst' },
            { prefijos: ['vales', 'ver.vales'], label: 'Vales', icono: 'fa-file-invoice' },
            { prefijos: ['presupuestos', 'cotizar.presupuestos', 'presupuestos.cotizar'], label: 'Presupuestos', icono: 'fa-calculator' },
            { prefijos: ['entradas'], label: 'Entradas', icono: 'fa-boxes-stacked' },
            { prefijos: ['albaranes'], label: 'Albaranes', icono: 'fa-clipboard-list' },
            { prefijos: ['facturas'], label: 'Facturas', icono: 'fa-file-invoice-dollar' },
            { prefijos: ['partes', 'modificacion'], label: 'Partes', icono: 'fa-puzzle-piece' },
            { prefijos: ['evidencias'], label: 'Evidencias', icono: 'fa-camera' },
            { prefijos: ['procesosVehiculos', 'procesoVehiculo'], label: 'Procesos de Vehiculos', icono: 'fa-gears' },
            { prefijos: ['reportes'], label: 'Reportes', icono: 'fa-chart-bar' },
            { prefijos: ['seguimientoTrabajo'], label: 'Seguimiento de Trabajo', icono: 'fa-list-check' },
            { prefijos: ['ver.talleres', 'pensiones.vertaller', 'periferico.vertaller'], label: 'Acceso a Talleres', icono: 'fa-warehouse' },
            { prefijos: ['descripcionw32'], label: 'Consultas W32', icono: 'fa-database' },
            { prefijos: ['autocar', 'refacciones', 'compras'], label: 'Perfil / Accesos Especiales', icono: 'fa-shield-halved' },
        ];

        function getSeccion(slug) {
            for (const sec of SECCIONES) {
                if (sec.prefijos.some(p => slug === p || slug.startsWith(p + '.'))) {
                    return sec.label;
                }
            }
            return 'Otros';
        }

        // Función para renderizar un checkbox interactivo de permiso
        function renderPermiso(permiso) {
            const esDelRol = permiso.es_del_rol;
            const checked = permiso.activo ? 'checked' : '';
            const disabled = esDelRol ? 'disabled' : '';
            const titulo = esDelRol ? 'title="Permiso heredado del rol (fijo)"' : '';

            const bgClass = permiso.activo
                ? 'bg-blue-950/60 border-blue-500 text-slate-50'
                : 'bg-gray-900 border-gray-800 text-gray-400';

            const extraClass = esDelRol ? 'opacity-90 cursor-not-allowed' : 'cursor-pointer active:scale-[0.99]';
            const onChange = !esDelRol ? `onchange="toggleLabelActivo(this)"` : '';

            const stateIcon = esDelRol
                ? '<span class="permiso-estado-badge ml-auto inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300"><i class="fa-solid fa-lock"></i>Rol</span>'
                : permiso.activo
                    ? '<span class="permiso-estado-badge ml-auto inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"><i class="fa-solid fa-check"></i>Extra</span>'
                    : '<span class="permiso-estado-badge ml-auto inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold bg-gray-800 text-gray-400 dark:bg-gray-800/80 dark:text-gray-400">Off</span>';

            const textColor = permiso.activo ? 'text-slate-50' : 'text-gray-400';
            const visibilityClass = !permiso.activo ? 'permiso-inactivo hidden' : 'permiso-activo';

            return `<label for="perm_${permiso.id}" ${titulo}
                class="flex min-h-[58px] items-center gap-3 rounded-lg border p-3 transition-all ${extraClass} ${visibilityClass} ${bgClass}">
                <input type="checkbox" id="perm_${permiso.id}" name="permisos[]"
                    value="${permiso.id}" ${checked} ${disabled}
                    class="h-5 w-5 rounded border-gray-300 bg-white text-blue-600 focus:ring-blue-500 disabled:opacity-90 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-900"
                    ${onChange}>
                <span class="permiso-nombre min-w-0 flex-1 text-sm font-semibold leading-snug ${textColor}">${permiso.nombre}</span>
                ${stateIcon}
            </label>`;
        }

        const grupos = {};
        data.permisos.forEach(p => {
            const sec = getSeccion(p.slug);
            if (!grupos[sec]) grupos[sec] = [];
            grupos[sec].push(p);
        });

        const ordenSecciones = SECCIONES.map(s => s.label).concat(['Otros']);

        ordenSecciones.forEach(nombreSec => {
            if (!grupos[nombreSec] || grupos[nombreSec].length === 0) return;

            const secInfo = SECCIONES.find(s => s.label === nombreSec);
            const icono = secInfo ? secInfo.icono : 'fa-circle-dot';
            const permisosSec = grupos[nombreSec];

            const activosCount = permisosSec.filter(p => p.activo).length;
            const heredadosCount = permisosSec.filter(p => p.es_del_rol).length;
            const inactivosCount = permisosSec.length - activosCount;

            const btnVerMas = inactivosCount > 0
                ? `<button type="button" onclick="togglePermisosInactivos(this)" class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-semibold transition bg-blue-600/10 border-blue-500/20 text-blue-400 hover:bg-blue-600/20">
                     <span>Ver inactivos (${inactivosCount})</span> <i class="fa-solid fa-chevron-down"></i>
                   </button>`
                : '';

            grid.insertAdjacentHTML('beforeend', `
                <div class="seccion-permisos rounded-lg border p-4 bg-gray-950/30 border-gray-800">
                    <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-600/10 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                                <i class="fa-solid ${icono} text-sm"></i>
                            </span>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-semibold uppercase tracking-wide text-white">${nombreSec}</h3>
                                <p class="text-xs text-gray-400">${activosCount} activos / ${heredadosCount} heredados / ${permisosSec.length} totales</p>
                            </div>
                        </div>
                        ${btnVerMas}
                    </div>
                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        ${permisosSec.map(renderPermiso).join('')}
                    </div>
                </div>
            `);
        });

    } catch (err) {
        loader.classList.add("hidden");
        loader.classList.remove("flex");
        grid.classList.remove("hidden");
        grid.innerHTML = '<div class="rounded-lg border border-red-800 bg-red-950/40 p-4 text-sm font-medium text-red-300">Error al cargar la matriz de permisos.</div>';
        console.error(err);
    }
}

/**
 * Cierra el modal de permisos
 */
function cerrarModalPermisos() {
    const modal = document.getElementById("modal-editar-permisos");
    if (modal) {
        modal.classList.add("hidden");
    }
    // Resetear estado
    estadoEdicionModal = { idUsuario: null, idRol: null, idPerfil: null, modo: 'principal' };
}

/**
 * Guarda los permisos modificados desde el modal
 */
async function guardarPermisosModal() {
    const { idUsuario, idRol, idPerfil, modo } = estadoEdicionModal;

    if (!idUsuario || !idPerfil || !idRol) {
        swalAlert("Error", "Faltan parámetros de edición.", "error");
        return;
    }

    const grid = document.getElementById("modal-permisos-grid");
    const checkboxes = grid.querySelectorAll('input[name="permisos[]"]:checked:not(:disabled)');
    const permisos = Array.from(checkboxes).map(cb => cb.value);

    const esPrincipal = modo === 'principal';
    const titulo = esPrincipal ? "¿Guardar como Rol Principal?" : "¿Agregar como Rol Secundario?";
    const texto = esPrincipal
        ? "Este rol se asignará como el rol principal del usuario en GTI y se actualizarán sus permisos."
        : "Este rol se agregará como secundario (campo opciones en GTI) sin reemplazar el rol principal.";
    const btnConfirmar = esPrincipal ? "Sí, guardar como principal" : "Sí, agregar como secundario";
    const btnColor = esPrincipal ? undefined : "#6366f1";

    Swal.fire({
        title: titulo,
        text: texto,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: btnConfirmar,
        confirmButtonColor: btnColor,
        cancelButtonText: "Cancelar"
    }).then(async (result) => {
        if (result.isConfirmed) {
            showLoading("Guardando permisos...");
            try {
                const baseUrl = (Perfil && Perfil !== 'administracion')
                    ? `${UrlProyecto}/${Perfil}`
                    : `${UrlProyecto}`;

                const res = await fetch(`${baseUrl}/administracion/permisos/guardar-permisos`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        id_usuario: idUsuario,
                        id_rol: idRol,
                        id_perfil: idPerfil,
                        permisos: permisos,
                        modo: modo
                    })
                });

                const data = await res.json();
                hideLoading();

                if (data.success) {
                    swalAlert("¡Éxito!", data.message, "success");
                    cerrarModalPermisos();
                    // Refrescar el estado en el dashboard
                    consultarRolActualUsuario();
                } else {
                    swalAlert("Error", data.message, "error");
                }
            } catch (err) {
                hideLoading();
                swalAlert("Error", "No se pudo conectar con el servidor.", "error");
                console.error(err);
            }
        }
    });
}

/**
 * Controla la visualización del panel de acciones en la columna izquierda
 * en base a la combinación de sucursal y rol seleccionados.
 */
function actualizarAccionesIzquierda() {
    const cardAcciones = document.getElementById("card-acciones-izquierda");
    const btnAsignarPrincipal = document.getElementById("btnAsignarPrincipal");
    const btnAsignarSecundario = document.getElementById("btnAsignarSecundario");
    const btnQuitarRol = document.getElementById("btnQuitarRol");

    if (!cardAcciones) return;

    const idUsuario = getIdFromDatalist("buscar_usuario", "usuarios");
    const idRol = getIdFromDatalist("buscar_rol", "roles");
    const idPerfil = getIdFromDatalist("buscar_perfil", "perfiles");

    if (!idUsuario || !idRol || !idPerfil) {
        cardAcciones.classList.add("hidden");
        return;
    }

    cardAcciones.classList.remove("hidden");

    // Verificar el estado de la combinación contra los datos del usuario actual
    const rolActualId = datosUsuarioActual.rolPrincipal ? datosUsuarioActual.rolPrincipal.id : null;
    const rolesSecundariosIds = datosUsuarioActual.rolesSecundarios.map(r => r.id);

    const esPrincipal = (rolActualId == idRol);
    const esSecundario = rolesSecundariosIds.includes(idRol);

    // Ocultar todos los botones primero
    btnAsignarPrincipal.classList.add("hidden");
    btnAsignarPrincipal.classList.remove("inline-flex", "w-full", "justify-center");
    btnAsignarSecundario.classList.add("hidden");
    btnAsignarSecundario.classList.remove("inline-flex", "w-full", "justify-center");
    btnQuitarRol.classList.add("hidden");
    btnQuitarRol.classList.remove("inline-flex", "w-full", "justify-center");

    if (esPrincipal) {
        // Es el rol principal actual. Se puede editar o quitar.
        btnAsignarPrincipal.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Configurar Permisos del Rol';
        btnAsignarPrincipal.classList.remove("hidden");
        btnAsignarPrincipal.classList.add("inline-flex", "w-full", "justify-center");

        btnQuitarRol.classList.remove("hidden");
        btnQuitarRol.classList.add("inline-flex", "w-full", "justify-center");
    } else if (esSecundario) {
        // Es un rol secundario actual. Se puede editar o quitar.
        btnAsignarSecundario.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Configurar Permisos del Rol';
        btnAsignarSecundario.classList.remove("hidden");
        btnAsignarSecundario.classList.add("inline-flex", "w-full", "justify-center");

        btnQuitarRol.classList.remove("hidden");
        btnQuitarRol.classList.add("inline-flex", "w-full", "justify-center");
    } else {
        // Es un nuevo rol en la sucursal. Se puede asignar como principal o como secundario.
        btnAsignarPrincipal.innerHTML = '<i class="fa-solid fa-user-shield"></i> Asignar como Principal';
        btnAsignarPrincipal.classList.remove("hidden");
        btnAsignarPrincipal.classList.add("inline-flex", "w-full", "justify-center");

        // Solo permitir agregar como secundario si ya tiene un principal
        if (rolActualId) {
            btnAsignarSecundario.innerHTML = '<i class="fa-solid fa-circle-plus"></i> Agregar como Secundario';
            btnAsignarSecundario.classList.remove("hidden");
            btnAsignarSecundario.classList.add("inline-flex", "w-full", "justify-center");
        }
    }
}

/**
 * Inicializa los listeners del panel de permisos.
 * El flujo es: Usuario → Perfil (filtra roles) → Rol.
 */
let modoAsignacionActual = 'principal';

function abrirModalSeleccionarRol(modo) {
    modoAsignacionActual = modo;
    const modal = document.getElementById("modal-seleccionar-rol");
    const titulo = document.getElementById("modal-seleccionar-titulo");

    if (titulo) {
        titulo.textContent = modo === 'principal' ? 'Asignar Rol Principal' : 'Agregar Rol Secundario';
    }

    const selectPerfil = document.getElementById("buscar_perfil");
    const selectRol = document.getElementById("buscar_rol");
    const hint = document.getElementById("lbl-rol-hint");

    if (selectPerfil) selectPerfil.value = "";
    if (selectRol) {
        selectRol.innerHTML = '<option value="">Selecciona un rol</option>';
        selectRol.disabled = true;
    }
    if (hint) hint.textContent = "Selecciona sucursal primero.";

    if (modal) {
        modal.classList.remove("hidden");
    }
}

function cerrarModalSeleccionarRol() {
    const modal = document.getElementById("modal-seleccionar-rol");
    if (modal) {
        modal.classList.add("hidden");
    }
}

function continuarAsignacionRol() {
    const idUsuario = getIdFromDatalist("buscar_usuario", "usuarios");
    const idPerfil = getIdFromDatalist("buscar_perfil", "perfiles");
    const idRol = getIdFromDatalist("buscar_rol", "roles");

    if (!idUsuario) {
        swalAlert("Error", "Debe seleccionar un usuario primero.", "error");
        return;
    }
    if (!idPerfil) {
        swalAlert("Error", "Debe seleccionar una sucursal.", "warning");
        return;
    }
    if (!idRol) {
        swalAlert("Error", "Debe seleccionar un rol.", "warning");
        return;
    }

    cerrarModalSeleccionarRol();
    abrirModalEdicion(idUsuario, idRol, idPerfil, modoAsignacionActual);
}

export function initPermisos() {
    document.getElementById("buscar_perfil")?.addEventListener("change", () => {
        filtrarRolesPorPerfil();
    });

    document.getElementById("buscar_usuario")?.addEventListener("change", () => {
        consultarRolActualUsuario();
    });

    document.getElementById("btnAsignarPrincipal")?.addEventListener("click", () => {
        abrirModalSeleccionarRol('principal');
    });

    document.getElementById("btnAgregarSecundario")?.addEventListener("click", () => {
        if (!datosUsuarioActual.rolPrincipal) {
            swalAlert(
                "Rol principal requerido",
                "Primero debes asignar un rol principal al usuario antes de agregar roles secundarios.",
                "warning"
            );
            return;
        }
        abrirModalSeleccionarRol('secundario');
    });

    document.getElementById("modal-seleccionar-cerrar-x")?.addEventListener("click", cerrarModalSeleccionarRol);
    document.getElementById("modal-seleccionar-cancelar")?.addEventListener("click", cerrarModalSeleccionarRol);
    document.getElementById("modal-seleccionar-continuar")?.addEventListener("click", continuarAsignacionRol);

    document.getElementById("modal-btn-cerrar-x")?.addEventListener("click", cerrarModalPermisos);
    document.getElementById("modal-btn-cancelar")?.addEventListener("click", cerrarModalPermisos);
    document.getElementById("modal-btn-guardar")?.addEventListener("click", guardarPermisosModal);

    document.getElementById("modal-btn-desmarcar")?.addEventListener("click", () => {
        const grid = document.getElementById("modal-permisos-grid");
        const checkboxes = grid.querySelectorAll('input[name="permisos[]"]:not(:disabled)');
        let desmarcados = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                cb.checked = false;
                window.toggleLabelActivo(cb);
                desmarcados++;
            }
        });

        if (desmarcados > 0) {
            swalAlert("Permisos desmarcados", `Se han desmarcado ${desmarcados} permisos. Recuerda presionar "Guardar Cambios" para aplicar los cambios en el sistema.`, "info");
        } else {
            swalAlert("Atención", "No hay permisos adicionales seleccionados para desmarcar.", "warning");
        }
    });

    // Iniciar efecto de máquina de escribir en el buscador de usuarios
    iniciarEfectoPlaceholder("buscar_usuario", [
        "Ej. Victoria Baas",
        "Ej. Emmanuel Gonzales",
        "Ej. Cesarea Flores"
    ]);
}

/**
 * Crea un efecto de máquina de escribir en el placeholder de un input.
 */
function iniciarEfectoPlaceholder(inputId, palabras) {
    const input = document.getElementById(inputId);
    if (!input) return;

    let indexPalabra = 0;
    let indexLetra = 0;
    let borrando = false;

    function escribir() {
        // Pausar y restablecer al original si el input está enfocado para no interrumpir al usuario
        if (document.activeElement === input) {
            input.placeholder = palabras[0];
            setTimeout(escribir, 1000);
            return;
        }

        const palabraActual = palabras[indexPalabra];

        if (!borrando) {
            input.placeholder = palabraActual.substring(0, indexLetra + 1);
            indexLetra++;

            if (indexLetra === palabraActual.length) {
                borrando = true;
                setTimeout(escribir, 2200); // Pausa de lectura de 2.2 segundos
                return;
            }
        } else {
            input.placeholder = palabraActual.substring(0, indexLetra - 1);
            indexLetra--;

            if (indexLetra === 0) {
                borrando = false;
                indexPalabra = (indexPalabra + 1) % palabras.length;
            }
        }

        const velocidad = borrando ? 25 : 55;
        setTimeout(escribir, velocidad);
    }

    escribir();
}

/**
 * Muestra/oculta los permisos inactivos de una sección
 */
window.togglePermisosInactivos = function (btn) {
    const section = btn.closest('.seccion-permisos');
    const inactivos = section.querySelectorAll('.permiso-inactivo');
    if (inactivos.length === 0) return;

    const isHidden = inactivos[0].classList.contains('hidden');
    const count = inactivos.length;

    inactivos.forEach(el => {
        if (isHidden) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });

    btn.innerHTML = isHidden
        ? `<span>Ocultar inactivos</span> <i class="fa-solid fa-chevron-up ml-1"></i>`
        : `<span>Ver inactivos (${count})</span> <i class="fa-solid fa-chevron-down ml-1"></i>`;
};

/**
 * Petición para quitar el rol seleccionado del usuario.
 */
function quitarRol(idRolForzado = null, idPerfilForzado = null, nombreRolForzado = null) {
    const idUsuario = getIdFromDatalist("buscar_usuario", "usuarios");
    const idRol = idRolForzado ?? getIdFromDatalist("buscar_rol", "roles");
    const idPerfil = idPerfilForzado ?? getIdFromDatalist("buscar_perfil", "perfiles");

    if (!idUsuario || !idRol || !idPerfil) {
        swalAlert("Faltan datos", "Debe seleccionar usuario, perfil y rol a quitar.", "warning");
        return;
    }

    let nombreRol = nombreRolForzado;
    if (!nombreRol) {
        const selectorRol = document.getElementById("buscar_rol");
        nombreRol = selectorRol.options[selectorRol.selectedIndex]?.text || "este rol";
    }

    Swal.fire({
        title: '¿Quitar Rol?',
        text: `¿Estás seguro de que deseas quitarle el rol "${nombreRol}" a este usuario? Se perderán los permisos asignados en este perfil.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, quitar rol',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading("Quitando rol...");

            // Obtener ruta baseUrl
            const baseUrl = (Perfil && Perfil !== 'administracion') ? `${UrlProyecto}/${Perfil}` : `${UrlProyecto}`;

            fetch(`${baseUrl}/administracion/permisos/quitar-rol`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    id_usuario: idUsuario,
                    id_rol: idRol,
                    id_perfil: idPerfil
                })
            })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        swalAlert("Éxito", data.message, "success").then(() => {
                            // Limpiar variables de estado locales
                            if (datosUsuarioActual.rolPrincipal && datosUsuarioActual.rolPrincipal.id == idRol) {
                                datosUsuarioActual.rolPrincipal = null;
                            } else {
                                datosUsuarioActual.rolesSecundarios = datosUsuarioActual.rolesSecundarios.filter(r => r.id != idRol);
                            }
                            // Recargar vista consultando rol
                            consultarRolActualUsuario();
                        });
                    } else {
                        swalAlert("Error", data.message || "Ocurrió un error al quitar el rol", "error");
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error("Error:", error);
                    swalAlert("Error", "Error de red al quitar el rol.", "error");
                });
        }
    });
}

