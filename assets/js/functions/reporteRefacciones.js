import { DT } from "./const.js";

const fmtMoney = (n) => new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" }).format(Number(n || 0));

document.addEventListener('DOMContentLoaded', function () {
   // Configurar fechas iniciales (primer día del mes actual hasta hoy)
   const hoy = new Date();
   const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
   const fmt = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
   
   const fechaInicioInput = document.getElementById('fecha_inicio');
   const fechaFinalInput = document.getElementById('fecha_final');

   if (fechaInicioInput) fechaInicioInput.value = fmt(inicioMes);
   if (fechaFinalInput) fechaFinalInput.value = fmt(hoy);

   // Inicializar Select2 para múltiples talleres
   if (window.jQuery && typeof $.fn.select2 !== 'undefined') {
      $('#taller').select2({
         placeholder: "Selecciona uno o varios talleres",
         allowClear: true,
         width: '100%'
      });
   }

   let table = null;
   
   const loadTable = () => {
      const obj2 = {
         columns: [
            { title: "FECHA COTIZACION", data: "fecha_cotizacion", defaultContent: "N/A" },
            { title: "VEHICULO", data: "vehiculo", defaultContent: "N/A" },
            { title: "AÑO", data: "anio", defaultContent: "N/A" },
            { title: "Nº PIEZAS COTIZADAS", data: "n_piezas_cotizadas", className: "text-center", defaultContent: "0" },
            { title: "IMPORTE COTIZADO", data: "importe_cotizado", render: fmtMoney, defaultContent: "$0.00" },
            { title: "SEGURO", data: "seguro", defaultContent: "N/A" },
            { title: "NUMERO DE SOLICITUD", data: "numero_solicitud", defaultContent: "N/A" },
            { title: "TALLER", data: "taller", defaultContent: "N/A" },
            { title: "FECHA DE VALE", data: "fecha_vale", defaultContent: "N/A" },
            { title: "Nº PIEZAS AUTORIZADAS", data: "n_piezas_autorizadas", className: "text-center", defaultContent: "0" },
            { title: "IMPORTE AUTORIZADO", data: "importe_autorizado", render: fmtMoney, defaultContent: "$0.00" }
         ],
         autoWidth: false,
         scrollX: false,
         order: [[8, "desc"]]
      };

      table = DT({
         idTable: "refaccionesTable",
         obj2,
         url: "reportes/refacciones/get"
      });

      const lastUpdatedEl = document.getElementById("lastUpdated");
      if (lastUpdatedEl) {
         const timeStr = new Date().toLocaleTimeString("es-MX", { hour: "2-digit", minute: "2-digit" });
         lastUpdatedEl.textContent = `Actualizado ${timeStr}`;
      }
   };

   loadTable();

   // Buscar registros
   const btnBuscar = document.getElementById('btnBuscar');
   if (btnBuscar) {
      btnBuscar.addEventListener('click', function () {
         if (table) {
            table.ajax.reload();
            const lastUpdatedEl = document.getElementById("lastUpdated");
            if (lastUpdatedEl) {
               const timeStr = new Date().toLocaleTimeString("es-MX", { hour: "2-digit", minute: "2-digit" });
               lastUpdatedEl.textContent = `Actualizado ${timeStr}`;
            }
         }
      });
   }
   
   // Exportación a Excel
   const btnExcelExport = document.getElementById('btnExcelExport');
   if (btnExcelExport) {
      btnExcelExport.addEventListener('click', function () {
         if (!table) return;
         const filas = table.rows({ search: "applied" }).data().toArray();
         const encabezados = [
            "FECHA COTIZACION", "VEHICULO", "AÑO", "Nº PIEZAS COTIZADAS", 
            "IMPORTE COTIZADO", "SEGURO", "NUMERO DE SOLICITUD", 
            "TALLER", "FECHA DE VALE", "Nº PIEZAS AUTORIZADAS", "IMPORTE AUTORIZADO"
         ];
         const datos = filas.map(fila => ({
            "FECHA COTIZACION": fila.fecha_cotizacion || "N/A",
            "VEHICULO": fila.vehiculo || "N/A",
            "AÑO": fila.anio || "N/A",
            "Nº PIEZAS COTIZADAS": fila.n_piezas_cotizadas || 0,
            "IMPORTE COTIZADO": fmtMoney(fila.importe_cotizado || 0),
            "SEGURO": fila.seguro || "N/A",
            "NUMERO DE SOLICITUD": fila.numero_solicitud || "N/A",
            "TALLER": fila.taller || "N/A",
            "FECHA DE VALE": fila.fecha_vale || "N/A",
            "Nº PIEZAS AUTORIZADAS": fila.n_piezas_autorizadas || 0,
            "IMPORTE AUTORIZADO": fmtMoney(fila.importe_autorizado || 0)
         }));

         const hoja = XLSX.utils.json_to_sheet(datos, { header: encabezados });
         const libro = XLSX.utils.book_new();
         XLSX.utils.book_append_sheet(libro, hoja, "Reporte Refacciones");
         const fecha = new Date().toISOString().slice(0, 10);
         XLSX.writeFile(libro, `reporte_refacciones_${fecha}.xlsx`);
      });
   }
});
