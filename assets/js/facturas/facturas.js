import { swalAlert, genRuta } from "../functions/const.js";

document.addEventListener('DOMContentLoaded', function () {

   let loadingToast;
   function showLoading() {
      loadingToast = swalAlert({
         title: 'Cargando...',
         toast: true,
         position: 'top-end',
         showConfirmButton: false,
         timerProgressBar: true,
         theme: 'auto',
         didOpen: () => {
            Swal.showLoading();
         }
      });
   }

   function hideLoading() {
      if (loadingToast) {
         loadingToast.close();
      }
   }

   const entradasTable = $('#facturasTable').DataTable({
      ajax: {
         url: genRuta('facturas', 'get'),
         dataSrc: '',
         beforeSend: function () {
            showLoading(); // Mostrar toast de carga antes de hacer la solicitud
         },
         complete: function () {
            hideLoading(); // Ocultar toast de carga después de recibir la respuesta
         },
         error: function () {
            hideLoading(); // Ocultar toast de carga en caso de error
            swalAlert({
               title: 'Error',
               text: 'No se pudieron cargar los datos',
               icon: 'error',
               theme: 'auto'
            });
            // Mostrar mensaje de error
         }
      },
      columns: [
         { title: 'N° Factura', data: 'numero_entrada' },
         { title: 'N° Orden', data: 'siniestros.numero_orden' },
         { title: 'N° Siniestro', data: 'siniestros.numero_siniestro' },
         { title: 'Aseguradora', data: 'siniestros.vehiculo_info.vin' },
         { title: 'Importe', data: 'importe' },
      ],
   });

});