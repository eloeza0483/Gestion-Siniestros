import ProcesosVehiculos from '../class/ProcesosVehiculos.js';
import { UrlProyecto, swalQuestion, token, swalAlert } from "../functions/const.js";

const procesosVehiculosClass = new ProcesosVehiculos();

export const DTLoad = async () => {
   return procesosVehiculosClass.DTable();
}

export const recargarTabla = async () => {
   $('#procesosVehiculosTable').DataTable().ajax.reload();
}

export const limpiarEstado = async () => {
   $('input[name="filtroEstado"]').prop('checked', false);
   await recargarTabla();
}

export const cambiarEstadoProceso = (event) => {
   const id = event.target.dataset.id;
   const estado = event.target.dataset.estado;
   let text = "";
   let confirmButtonText = "";
   switch (estado) {
      case "EnProceso":
         text = "¿Quieres iniciar este proceso?";
         confirmButtonText = "Sí, Iniciar";
         break;
      case "Pausado":
         text = "¿Quieres pausar este proceso?";
         confirmButtonText = "Sí, Pausar";
         break;
      case "Finalizado":
         text = "¿Quieres finalizar este proceso?";
         confirmButtonText = "Sí, Finalizar";
         break;
   }
   let sweetAlertConfig = {
      title: '¿Estás seguro?',
      text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText,
      cancelButtonText: 'No, descartar',
      theme: 'auto',
      buttonsStyling: true
   };

   // Si el estado es pausado, agregamos un input tipo textarea para solicitar el motivo
   if (estado === "Pausado") {
      sweetAlertConfig.input = 'textarea';
      sweetAlertConfig.inputPlaceholder = 'Ingresa el motivo de la pausa...';
      sweetAlertConfig.inputAttributes = {
         'aria-label': 'Ingresa el motivo de la pausa'
      };
      sweetAlertConfig.inputValidator = (value) => {
         if (!value) {
            return '¡Debes ingresar un motivo para pausar!';
         }
      };
   }

   swalAlert(sweetAlertConfig).then(async (result) => {
      if (result.isConfirmed) {
         let motivo = estado === "Pausado" ? result.value : null;
         let resp = await procesosVehiculosClass.cambiarEstadoProceso(id, estado, 0, motivo);

         if (!resp.success && resp.requireAdelanto) {
            swalAlert({
               title: resp.title,
               text: resp.message,
               icon: resp.icon,
               showCancelButton: true,
               confirmButtonText: 'Sí, adelantarlo',
               cancelButtonText: 'No, cancelar',
               theme: 'auto',
               buttonsStyling: true
            }).then(async (res2) => {
               if (res2.isConfirmed) {
                  resp = await procesosVehiculosClass.cambiarEstadoProceso(id, estado, 1, motivo);
                  swalAlert({
                     title: resp.title, text: resp.message, icon: resp.icon,
                     confirmButtonText: 'Aceptar',
                     theme: 'auto',
                     timer: 2000
                  });
               }
            });
            return;
         }

         swalAlert({
            title: resp.title, text: resp.message, icon: resp.icon,
            confirmButtonText: 'Aceptar',
            theme: 'auto',
            timer: 2000
         })
      }
   });
}

// export const buscar = async (e) => {
//    $('#procesosVehiculosTable').DataTable().search(e.target.value).draw();
// }
