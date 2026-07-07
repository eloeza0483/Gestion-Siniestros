import Reportes from '../class/Reportes.js';
import { estadosPorEntidad } from '../functions/const.js';

const reportesClass = new Reportes();

// Carga inicial de DataTable y dashboard.
export const DTLoad = async () => {
   await reportesClass.cargarDashboard();
   return reportesClass.DTable();
};

// Destruye y recrea la DataTable, ademas de recargar los indicadores.
export const recargarTabla = async () => {
   if ($.fn.DataTable.isDataTable('#reportesTable')) {
      $('#reportesTable').DataTable().destroy();
      $('#reportesTable').empty();
   }
   await reportesClass.cargarDashboard();
   reportesClass.DTable();
};

// Actualiza el select de estados segun la entidad.
export const actualizarEstados = (entidad) => {
   const selectEstado = document.getElementById('estado');
   if (!selectEstado) return;

   const estados = estadosPorEntidad[entidad] || [];
   selectEstado.innerHTML = '<option selected value="Todos">Todos</option>';
   estados.forEach(estado => {
      const option = document.createElement('option');
      option.value = estado.value;
      option.textContent = estado.text;
      selectEstado.appendChild(option);
   });
};

// Exporta a Excel desde la clase.
export const exportarExcel = () => {
   reportesClass.exportarExcel();
};
