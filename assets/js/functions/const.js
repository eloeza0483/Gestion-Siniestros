export const urlDom = window.location.origin;
export const URLactual = window.location.pathname;
export const UrlSplit = URLactual.split("/");
export const ultimoParametro = UrlSplit[UrlSplit.length - 1];
export const Principal = UrlSplit[1];
export const Perfil = UrlSplit[2];
//maybe pueda agregar  a la urlproyecto el perfil????
export const UrlProyecto = `${urlDom}/${Principal}`;
export const folio = new URLSearchParams(window.location.search).get("folio");
export const id_perfil = document.querySelector('#perfil_id')?.value;

export const genRuta = (...param) => `/${Principal}/${Perfil}/${param.join("/")}`;

const delegacionEvento = (clase, evento = "click", fun, element = null) =>
  (element ?? document).addEventListener(evento, (e) =>
    e.target.closest(`.${clase}`) ? fun(e.target.closest(`.${clase}`)) : false
  );


export const token = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute("content");

export const dataTableLanguageEs = {
  decimal: "",
  emptyTable: "No hay datos disponibles en la tabla",
  info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
  infoEmpty: "Mostrando 0 a 0 de 0 registros",
  infoFiltered: "(filtrado de _MAX_ registros totales)",
  infoPostFix: "",
  thousands: ",",
  lengthMenu: "Mostrar _MENU_ registros",
  loadingRecords: "Cargando...",
  processing: "Procesando...",
  search: "Buscar:",
  zeroRecords: "No se encontraron resultados",
  paginate: {
    first: "Primero",
    last: "Último",
    next: "Siguiente",
    previous: "Anterior",
  },
  aria: {
    sortAscending: ": activar para ordenar la columna de manera ascendente",
    sortDescending: ": activar para ordenar la columna de manera descendente",
  },
};

if (window.$?.fn?.dataTable) {
  window.$.extend(true, window.$.fn.dataTable.defaults, {
    language: dataTableLanguageEs,
  });
}

export const showLoading = (message = "Cargando ...", obj = {}) => {
  const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
  const o1 = {
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timerProgressBar: true,
    background: isDark ? '#1e293b' : '#ffffff',
    color: isDark ? '#f8fafc' : '#1e293b',
    customClass: {
      popup: "border border-slate-200 dark:border-slate-800 shadow-xl",
      title: "text-sm font-medium"
    },
    didOpen: () => {
      Swal.showLoading();
    },
  };
  const nobj = { ...o1, ...obj };
  return Swal.mixin(nobj).fire({ title: message });
};

export const showSendingMailAlert = (message = "Enviando correo...") =>
  showLoading(message);

export const loadingError = (
  message = "No se pudieron cargar los datos",
  obj = {}
) => {
  const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
  const o1 = {
    title: "Error",
    icon: "error",
    background: isDark ? '#1e293b' : '#ffffff',
    color: isDark ? '#f8fafc' : '#1e293b',
    customClass: {
      popup: "bg-white dark:bg-[#1e293b] rounded-2xl border border-rose-200 dark:border-rose-900 shadow-2xl p-6",
      title: "text-xl font-bold text-slate-800 dark:text-white"
    },
    didOpen: () => {
      Swal.showLoading();
    },
  };
  const nobj = { ...o1, ...obj };
  return Swal.mixin(nobj).fire({ title: message });
};

export const ToastLoad = (message = "Cargando ...", obj = {}) => {
  const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
  const o1 = {
    toast: true,
    position: "bottom-end",
    showConfirmButton: false,
    timer: 300000,
    background: isDark ? '#1e293b' : '#ffffff',
    color: isDark ? '#f8fafc' : '#1e293b',
    customClass: {
      popup: "border border-slate-200 dark:border-slate-800 shadow-xl",
      title: "text-sm font-medium"
    },
    didOpen: (toast) => {
      Swal.showLoading();
    },
  };
  const nobj = { ...o1, ...obj };
  return Swal.mixin(nobj).fire({ title: message, className: "toastLoad" });
};

export const Toast = (message = "", icon = "success", obj = {}) => {
  const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
  const o1 = {
    toast: true,
    position: "bottom-end",
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true,
    background: isDark ? '#1e293b' : '#ffffff',
    color: isDark ? '#f8fafc' : '#1e293b',
    customClass: {
      container: "!z-[99999]",
      popup: "border border-slate-200 dark:border-slate-800 shadow-xl rounded-lg",
      title: "text-sm font-medium"
    },
  };
  const nobj = { ...o1, ...obj };
  return Swal.mixin(nobj).fire({ icon, title: message });
};

export const hideLoading = () => {
  Swal.close(); // Cierra cualquier toast abierto
};

export const DT = ({ idTable, titleExcel = null, obj2, url }) => {
  const obj = {
    language: dataTableLanguageEs,
    // searching: false,

    ajax: {
      url: `${UrlProyecto}/${Perfil}/${url}`,
      data: function (d) {
        // Verificar si el switch "Ver todos" está activado
        const switchVerTodos = document.querySelector("#switchVerTodos");
        if (switchVerTodos && switchVerTodos.checked) {
          d.verTodos = true;
        }

        const filtroEstado = document.querySelector(
          'input[name="filtroEstado"]:checked'
        );
        if (filtroEstado) {
          d.estado = filtroEstado.value;
        }
        //fix :(
        const modulos = ['siniestros', 'presupuestos', 'vales', 'entradas', 'albaranes', 'facturas', 'reportes', 'vehiculos', 'marcas', 'talleres', 'aseguradoras', 'procesos-vehiculos', 'seguimiento-trabajos', 'refacciones'];

        if (ultimoParametro && !modulos.includes(ultimoParametro)) {
          d.taller = ultimoParametro.replace(/_/g, ' ').toUpperCase();
        } else {
          const tallerSelect = document.querySelector('select[name="taller"], select[name="taller[]"]');
          if (tallerSelect && tallerSelect.value !== "" && tallerSelect.value !== "Selecciona un taller") {
            if (tallerSelect.multiple) {
              d.taller = Array.from(tallerSelect.selectedOptions).map(opt => opt.value);
            } else {
              d.taller = tallerSelect.value;
            }
          }
        }

        const entidadSelect = document.querySelector('select[name="entidad"]');
        if (
          entidadSelect &&
          entidadSelect.value !== "" &&
          entidadSelect.value !== "Entidad"
        ) {
          d.entidad = entidadSelect.value;
        }

        const fechaInicioInput = document.querySelector(
          'input[name="fecha_inicio"]'
        );
        if (fechaInicioInput && fechaInicioInput.value !== "") {
          d.fecha_inicio = fechaInicioInput.value;
        }

        const fechaFinalInput = document.querySelector(
          'input[name="fecha_final"]'
        );
        if (fechaFinalInput && fechaFinalInput.value !== "") {
          d.fecha_final = fechaFinalInput.value;
        }

        const ignorarFechasCheckbox = document.querySelector(
          'input[name="ignorar_fechas"]'
        );
        if (ignorarFechasCheckbox) {
          d.ignorar_fechas = ignorarFechasCheckbox.checked ? 1 : 0;
        }

        const estadosSelect = document.querySelector('select[name="estado"]');
        if (
          estadosSelect &&
          estadosSelect.value !== "" &&
          estadosSelect.value !== "Estados"
        ) {
          d.estado = estadosSelect.value;
        }

        const tipoRegistroRadio = document.querySelector(
          'input[name="tipo_registro"]:checked'
        );
        if (tipoRegistroRadio) {
          d.tipo_registro = tipoRegistroRadio.value;
        }
      },
      dataSrc: function (result) {
        return result;
      },
      beforeSend: function () {
        showLoading(); // Mostrar toast de carga antes de hacer la solicitud
      },
      complete: function () {
        hideLoading(); // Ocultar toast de carga después de recibir la respuesta
      },
      error: function () {
        hideLoading(); // Ocultar toast de carga en caso de error
        loadingError();
      },
    },
    order: [[0, "asc"]],
    aLengthMenu: [
      [25, 50, 100, 500, -1],
      [25, 50, 100, 500, "Todos"],
    ],
  };
  // console.log(obj2);
  // console.log(obj);
  // console.log(url);

  const new_obj = { ...obj, ...obj2 };

  return $(`#${idTable}`).DataTable(new_obj);
};

export function swalQuestion(message, obj = {}) {
  return swalAlert({
    html: message,
    icon: "question",
    confirmButtonText: "Continuar",
    cancelButtonText: "Cancelar",
    showCancelButton: true,
    ...obj,
  });
}

export const estadosPorEntidad = {
  siniestros: [
    { value: "Abierto", text: "Abierto" },
    { value: "Completado", text: "Completado" },
    { value: "Cerrado", text: "Cerrado" },
    { value: "Cancelado", text: "Cancelado" },
  ],
  presupuestos: [
    { value: "SinCotizar", text: "Sin Cotizar" },
    { value: "Pendiente", text: "Pendiente" },
    { value: "Cotizado", text: "Cotizado" },
    { value: "Cancelado", text: "Cancelado" },
  ],
  vales: [
    { value: "Abierto", text: "Abierto" },
    { value: "Completado", text: "Completado" },
    { value: "Cerrado", text: "Cerrado" },
    { value: "Cancelado", text: "Cancelado" },
  ],
  facturas: [
    { value: "Activo", text: "Activo" },
    { value: "Facturado", text: "Facturado" },
    { value: "Cancelado", text: "Cancelado" },
  ],
  entradas: [
    { value: "Activo", text: "Activo" },
    { value: "Facturado", text: "Facturado" },
    { value: "Cancelado", text: "Cancelado" },
  ],
  albaranes: [
    { value: "Activo", text: "Activo" },
    { value: "Facturado", text: "Facturado" },
    { value: "Cancelado", text: "Cancelado" },
  ],

};

export const swalAlert = (titleOrObj, htmlOrUndefined, iconStr = "success", objConfig = {}) => {
  let title = titleOrObj;
  let html = htmlOrUndefined;
  let icon = iconStr;
  let obj = { ...objConfig };

  if (typeof titleOrObj === 'object' && titleOrObj !== null) {
    obj = { ...titleOrObj };
    title = obj.title || '';
    html = obj.html || obj.text || '';
    icon = obj.icon || 'success';
    delete obj.title;
    delete obj.html;
    delete obj.text;
    delete obj.icon;
  }

  const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');

  const theme = {
    success: { btn: "bg-emerald-600 hover:bg-emerald-500 shadow-emerald-500/30", icon: "#10b981" },
    error: { btn: "bg-rose-600 hover:bg-rose-500 shadow-rose-500/30", icon: "#f43f5e" },
    question: { btn: "bg-blue-600 hover:bg-blue-500 shadow-blue-500/30", icon: "#3b82f6" },
    info: { btn: "bg-sky-600 hover:bg-sky-500 shadow-sky-500/30", icon: "#0ea5e9" },
    warning: { btn: "bg-amber-500 hover:bg-amber-600 shadow-amber-500/30", icon: "#f59e0b" }
  };
  const activeTheme = theme[icon] || theme.success;

  return Swal.fire({
    title: title ? `<div class="text-4xl font-black text-slate-800 dark:text-white pt-8 tracking-tighter">${title}</div>` : '',
    html: html ? `<div class="text-lg text-slate-500 dark:text-slate-400 font-medium pt-4 px-8">${html}</div>` : '',
    icon: icon,
    iconColor: activeTheme.icon,
    showCancelButton: obj.showCancelButton !== undefined ? obj.showCancelButton : (icon === 'question' || icon === 'warning'),
    confirmButtonText: obj.confirmButtonText || "Aceptar",
    cancelButtonText: obj.cancelButtonText || "Cancelar",
    buttonsStyling: false,
    customClass: {
      confirmButton: `${activeTheme.btn} text-white font-bold py-4 px-16 rounded-full mx-4 transition-all duration-300 shadow-xl active:scale-95 transform hover:-translate-y-1`,
      cancelButton: "bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold py-4 px-8 rounded-full mx-4 transition-all active:scale-95",
      popup: "bg-white dark:bg-[#1e293b] rounded-[4rem] border border-slate-200 dark:border-slate-800 shadow-[0_30px_70px_-15px_rgba(0,0,0,0.4)] p-12",
      htmlContainer: "pb-12", // Espacio extra bajo el texto
      actions: "pt-10 pb-4",
      icon: "scale-125 mb-4 border-none",
      ...obj.customClass
    },
    background: isDark ? '#1e293b' : '#ffffff',
    color: isDark ? '#f8fafc' : '#1e293b',
    backdrop: `rgba(15, 23, 42, 0.85) backdrop-blur-[10px]`,
    showClass: {
      popup: 'animate__animated animate__zoomIn animate__faster'
    },
    hideClass: {
      popup: 'animate__animated animate__zoomOut animate__faster'
    },
    heightAuto: false,
    ...obj
  });
};

export { delegacionEvento };
