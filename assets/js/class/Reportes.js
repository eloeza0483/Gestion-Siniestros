import {
  Perfil,
  UrlProyecto,
  DT
} from "../functions/const.js";
import General from "./General.js";

const COLORES = [
  "#2563eb", "#7c3aed", "#059669", "#d97706", "#dc2626",
  "#0891b2", "#db2777", "#0d9488", "#ea580c", "#4f46e5"
];

const ENTIDADES = {
  siniestros: "Siniestros",
  presupuestos: "Presupuestos",
  vales: "Vales",
  entradas: "Entradas",
  albaranes: "Albaranes"
};

let chartEstados = null;
let chartTalleres = null;
let chartMeses = null;

const numberFormatter = new Intl.NumberFormat("es-MX");
const moneyFormatter = new Intl.NumberFormat("es-MX", {
  style: "currency",
  currency: "MXN",
  minimumFractionDigits: 2,
  maximumFractionDigits: 2
});

const fmt = (n) => numberFormatter.format(Number(n || 0));
const fmtMoney = (n) => moneyFormatter.format(Number(n || 0));
const pct = (value, total) => total > 0 ? `${Math.round((value / total) * 100)}%` : "0%";

const setText = (id, value) => {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
};

const hasValues = (values) => values.some(value => Number(value) > 0);

const toggleEmpty = (emptyId, canvasId, hasData) => {
  const empty = document.getElementById(emptyId);
  const canvas = document.getElementById(canvasId);
  if (empty) {
    empty.classList.toggle("hidden", hasData);
    empty.classList.toggle("grid", !hasData);
  }
  if (canvas) canvas.classList.toggle("opacity-0", !hasData);
};

const moneyColumn = (value) => fmtMoney(value);

class Reportes extends General {
  constructor () {
    super();
    this.table = null;
    this.url = `${UrlProyecto}/${Perfil}/reportes`;
  }

  // Obtiene la configuración de columnas de DataTables para cada entidad.
  _getColumns (entidad) {
    if (entidad === "siniestros") {
      return [
        { title: "N&deg; Orden", data: "numero_orden" },
        { title: "N&deg; Siniestro", data: "numero_siniestro" },
        { title: "Aseguradora", data: "aseguradora" },
        { title: "VIN", data: "vin" },
        { title: "Marca", data: "marca" },
        { title: "Vehículo", data: "vehiculo" },
        { title: "Modelo", data: "modelo" },
        { title: "Taller", data: "taller" },
        { title: "Importe Autorizado", data: "importe_autorizado", render: moneyColumn },
        { title: "Importe Recibido", data: "importe_recibido", render: moneyColumn },
        { title: "Registro", data: "registro" },
        { title: "Actualización", data: "actualizacion" },
        { title: "Estado", data: "estado" }
      ];
    } else if (entidad === "presupuestos") {
      return [
        { title: "N&deg; Presupuesto", data: "numero_presupuesto" },
        { title: "Total s/IVA", data: "subtotal", render: moneyColumn },
        { title: "Total c/IVA", data: "total", render: moneyColumn },
        { title: "N&deg; Siniestro", data: "numero_siniestro" },
        { title: "N&deg; Parte", data: "numero_parte" },
        { title: "Descripción", data: "descripcion" },
        { title: "Descripción W32", data: "descripcion_w32" },
        { title: "Cantidad", data: "numero_pzas_presupuesto" },
        { title: "Importe Unitario", data: "importe_unitario", render: moneyColumn },
        { title: "Importe Total", data: "importe_total", render: moneyColumn },
        { title: "Registro", data: "registro" },
        { title: "Actualización", data: "actualizacion" },
        { title: "Estado", data: "estado" },
        { title: "Taller", data: "taller" }
      ];
    } else if (entidad === "vales") {
      return [
        { title: "N&deg; Vale", data: "numero_vale" },
        { title: "N&deg; Siniestro", data: "numero_siniestro" },
        { title: "N&deg; Orden", data: "numero_orden" },
        { title: "N&deg; Presupuesto", data: "numero_presupuesto" },
        { title: "Total s/IVA", data: "subtotal", render: moneyColumn },
        { title: "Total c/IVA", data: "total", render: moneyColumn },
        { title: "Fecha Vale", data: "fecha_vale" },
        { title: "Fecha Promesa", data: "fecha_promesa" },
        { title: "Registro", data: "registro" },
        { title: "Actualización", data: "actualizacion" },
        { title: "Estado", data: "estado" },
        { title: "Taller", data: "taller" }
      ];
    } else if (entidad === "entradas") {
      return [
        { title: "N&deg; Entrada", data: "numero_entrada" },
        { title: "N&deg; Orden", data: "numero_orden" },
        { title: "N&deg; Siniestro", data: "numero_siniestro" },
        { title: "N&deg; Vale", data: "numero_vale" },
        { title: "Importe", data: "importe", render: moneyColumn },
        { title: "Registro", data: "registro" },
        { title: "Actualización", data: "actualizacion" },
        { title: "Estado", data: "estado" },
        {
          title: "Opciones",
          data: null,
          className: "text-center",
          render: (data) => `<a href="${UrlProyecto}/${Perfil}/entradas/detalle/${data.id}" target="_entrada_${data.id}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-gray-700 transition hover:bg-gray-800 hover:text-white dark:border-gray-600 dark:text-gray-200"><i class="fa-solid fa-door-open"></i></a>`
        }
      ];
    } else if (entidad === "albaranes") {
      return [
        { title: "N&deg; Albarán", data: "numero_albaran" },
        { title: "N&deg; Orden", data: "numero_orden" },
        { title: "N&deg; Siniestro", data: "numero_siniestro" },
        { title: "N&deg; Vale", data: "numero_vale" },
        { title: "Importe", data: "importe", render: moneyColumn },
        { title: "Registro", data: "registro" },
        { title: "Actualización", data: "actualizacion" },
        { title: "Estado", data: "estado" },
        {
          title: "Opciones",
          data: null,
          className: "text-center",
          render: (data) => `<a href="${UrlProyecto}/${Perfil}/albaranes/detalle/${data.id}" target="_albaran_${data.id}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-gray-700 transition hover:bg-gray-800 hover:text-white dark:border-gray-600 dark:text-gray-200"><i class="fa-solid fa-door-open"></i></a>`
        }
      ];
    }
    return [];
  }

  DTable () {
    const entidad = document.querySelector('select[name="entidad"]')?.value ?? "siniestros";
    const obj2 = {
      columns: this._getColumns(entidad),
      autoWidth: false,
      scrollX: true
    };

    this.table = DT({
      idTable: "reportesTable",
      obj2,
      url: "reportes/get"
    });

    return this.table;
  }

  _getParams () {
    const get = (id) => document.getElementById(id)?.value ?? "";
    const ignorarFechasCheckbox = document.getElementById("ignorar_fechas");
    return {
      entidad: get("entidad"),
      estado: get("estado"),
      taller: get("taller"),
      fecha_inicio: get("fecha_inicio"),
      fecha_final: get("fecha_final"),
      ignorar_fechas: ignorarFechasCheckbox && ignorarFechasCheckbox.checked ? 1 : 0
    };
  }

  _setDashboardLoading () {
    [
      "kpi-siniestros", "kpi-vales", "kpi-entradas", "kpi-albaranes", "kpi-importe",
      "insight-promedio-vale", "insight-ratio-entradas", "insight-estado-dominante", "insight-taller-top"
    ].forEach(id => setText(id, "-"));
  }

  _updateContext (params) {
    const entidad = ENTIDADES[params.entidad] ?? "Registros";
    const taller = params.taller && params.taller !== "Todos" ? params.taller : "todos los talleres";
    const estado = params.estado && params.estado.toLowerCase() !== "todos" ? ` con estado ${params.estado}` : "";
    const periodo = params.fecha_inicio && params.fecha_final
      ? `${params.fecha_inicio} a ${params.fecha_final}`
      : "periodo disponible";

    setText("dashboardSummary", `Indicadores generales de ${taller}. Periodo: ${periodo}. Detalle: ${entidad}${estado}.`);
    setText("lastUpdated", `Actualizado ${new Date().toLocaleTimeString("es-MX", { hour: "2-digit", minute: "2-digit" })}`);
  }

  _updateKpis (data) {
    const kpis = data.kpis ?? {};
    const siniestros = Number(kpis.siniestros || 0);
    const vales = Number(kpis.vales || 0);
    const entradas = Number(kpis.entradas || 0);
    const albaranes = Number(kpis.albaranes || 0);
    const importe = Number(kpis.importe || 0);

    setText("kpi-siniestros", fmt(siniestros));
    setText("kpi-vales", fmt(vales));
    setText("kpi-entradas", fmt(entradas));
    setText("kpi-albaranes", fmt(albaranes));
    setText("kpi-importe", fmtMoney(importe));

    setText("kpi-siniestros-help", siniestros === 1 ? "1 caso registrado." : `${fmt(siniestros)} casos registrados.`);
    setText("kpi-vales-help", vales === 1 ? "1 vale emitido." : `${fmt(vales)} vales emitidos.`);
    setText("kpi-entradas-help", `${pct(entradas, vales)} contra vales emitidos.`);
    setText("kpi-albaranes-help", `${pct(albaranes, entradas)} contra entradas registradas.`);
    setText("kpi-importe-help", vales > 0 ? `${fmtMoney(importe / vales)} promedio por vale.` : "Sin vales con importe.");

    setText("insight-promedio-vale", vales > 0 ? fmtMoney(importe / vales) : "$0.00");
    setText("insight-ratio-entradas", `${pct(entradas, vales)} (${fmt(entradas)} de ${fmt(vales)})`);
  }

  _updateInsights (data) {
    const estados = data.por_estado ?? [];
    const talleres = data.por_taller ?? [];
    const estadoTop = [...estados].sort((a, b) => Number(b.total || 0) - Number(a.total || 0))[0];
    const tallerTop = [...talleres].sort((a, b) => Number(b.total || 0) - Number(a.total || 0))[0];

    setText("insight-estado-dominante", estadoTop ? `${estadoTop.estado ?? "Sin estado"} (${fmt(estadoTop.total)})` : "Sin datos");
    setText("insight-taller-top", tallerTop ? `${tallerTop.taller ?? "Sin taller"} (${fmt(tallerTop.total)})` : "Sin datos");
  }

  _chartOptions (isDark) {
    const textColor = isDark ? "#cbd5e1" : "#475569";
    const gridColor = isDark ? "rgba(255,255,255,0.08)" : "rgba(15,23,42,0.08)";

    return { textColor, gridColor };
  }

  _renderCharts (data) {
    const isDark = document.documentElement.classList.contains("dark") || document.body.classList.contains("dark");
    const { textColor, gridColor } = this._chartOptions(isDark);

    const etiquetasEstado = (data.por_estado ?? []).map(r => r.estado ?? "Sin estado");
    const valoresEstado = (data.por_estado ?? []).map(r => Number(r.total || 0));
    const hayEstados = hasValues(valoresEstado);
    toggleEmpty("emptyEstados", "chartEstados", hayEstados);
    if (chartEstados) chartEstados.destroy();
    if (hayEstados) {
      chartEstados = new Chart(document.getElementById("chartEstados"), {
        type: "doughnut",
        data: {
          labels: etiquetasEstado,
          datasets: [{
            data: valoresEstado,
            backgroundColor: COLORES,
            borderWidth: 2,
            borderColor: isDark ? "#111827" : "#ffffff",
            hoverOffset: 5
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "62%",
          plugins: {
            legend: {
              position: "bottom",
              labels: { color: textColor, boxWidth: 10, padding: 12, font: { size: 11 } }
            }
          }
        }
      });
    }

    const etiquetasTaller = (data.por_taller ?? []).map(r => r.taller ?? "Sin taller");
    const valoresTaller = (data.por_taller ?? []).map(r => Number(r.total || 0));
    const hayTalleres = hasValues(valoresTaller);
    toggleEmpty("emptyTalleres", "chartTalleres", hayTalleres);
    if (chartTalleres) chartTalleres.destroy();
    if (hayTalleres) {
      chartTalleres = new Chart(document.getElementById("chartTalleres"), {
        type: "bar",
        data: {
          labels: etiquetasTaller,
          datasets: [{
            label: "Siniestros",
            data: valoresTaller,
            backgroundColor: COLORES.map(c => `${c}cc`),
            borderRadius: 6,
            borderSkipped: false,
            maxBarThickness: 22
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: "y",
          plugins: { legend: { display: false } },
          scales: {
            x: { ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } },
            y: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } }
          }
        }
      });
    }

    const etiquetasMes = (data.por_mes ?? []).map(r => r.mes);
    const valoresMes = (data.por_mes ?? []).map(r => Number(r.total || 0));
    const hayMeses = hasValues(valoresMes);
    toggleEmpty("emptyMeses", "chartMeses", hayMeses);
    if (chartMeses) chartMeses.destroy();
    if (hayMeses) {
      chartMeses = new Chart(document.getElementById("chartMeses"), {
        type: "line",
        data: {
          labels: etiquetasMes,
          datasets: [{
            label: "Vales",
            data: valoresMes,
            borderColor: "#059669",
            backgroundColor: "rgba(5,150,105,0.14)",
            fill: true,
            tension: 0.35,
            pointBackgroundColor: "#059669",
            pointBorderColor: isDark ? "#111827" : "#ffffff",
            pointBorderWidth: 2,
            pointRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false } },
            y: { ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } }
          }
        }
      });
    }
  }

  async cargarDashboard () {
    const params = this._getParams();
    const qs = new URLSearchParams(params).toString();

    this._setDashboardLoading();
    this._updateContext(params);

    try {
      const res = await fetch(`${this.url}/stats?${qs}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      this._updateKpis(data);
      this._updateInsights(data);
      this._renderCharts(data);
    } catch (err) {
      console.error("Error cargando stats:", err);
      setText("dashboardSummary", "No se pudieron cargar los indicadores del dashboard.");
      toggleEmpty("emptyEstados", "chartEstados", false);
      toggleEmpty("emptyTalleres", "chartTalleres", false);
      toggleEmpty("emptyMeses", "chartMeses", false);
    }
  }

  exportarExcel () {
    if (!this.table) return;

    const entidad = document.querySelector('select[name="entidad"]')?.value ?? "reporte";
    const filas = this.table.rows({ search: "applied" }).data().toArray();
    const cols = this.table.settings()[0].aoColumns.filter(c => c.sTitle !== "Opciones");

    const encabezados = cols.map(c => c.sTitle.replace("&deg;", "°"));
    const datos = filas.map(fila =>
      cols.reduce((acc, col, index) => {
        const key = col.mData;
        acc[encabezados[index]] = typeof key === "string" ? (fila[key] ?? "") : "";
        return acc;
      }, {})
    );

    const hoja = XLSX.utils.json_to_sheet(datos, { header: encabezados });
    const libro = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(libro, hoja, entidad.charAt(0).toUpperCase() + entidad.slice(1));

    const fecha = new Date().toISOString().slice(0, 10);
    XLSX.writeFile(libro, `reporte_${entidad}_${fecha}.xlsx`);
  }
}

export default Reportes;
