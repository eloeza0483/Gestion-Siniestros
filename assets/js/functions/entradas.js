import Entradas from '../class/Entradas.js';
import { UrlProyecto, Perfil, swalQuestion, token , swalAlert, showSendingMailAlert } from "../functions/const.js";

const entradasClass = new Entradas();

export const DTLoad = async () => {
   return entradasClass.DTable();
}

export const recargarTabla = async () => {
   $('#entradasTable').DataTable().ajax.reload();
}

export const findEntradaByNumero = async (numeroEntrada) => {
   try {
      const response = await fetch(`${UrlProyecto}/${Perfil}/entradas/${numeroEntrada}`);
      if (!response.ok) {
         throw new Error('Error al obtener la entrada');
      }
      const result = await response.json();
      console.log(result);

      const datos = result.entrada;
      const piezas = result.piezas;



      const inputs = {
         numero_siniestro: datos.siniestros.numero_siniestro, aseguradora: datos.siniestros.vehiculo_info.aseguradora, vehiculo: datos.siniestros.vehiculo_info.vehiculo, modelo: datos.siniestros.vehiculo_info.modelo, estado: datos.estado, id_vale: datos.id_vale
      }

      Object.entries(inputs).forEach(([key, val]) => {
         const inputElement = document.querySelector(`#${key}`);
         if (inputElement) {
            inputElement.value = val || ''; // Asignar el valor (con valor por defecto)
            inputElement.setAttribute('readonly', true); // Hacer el input readonly
         } else {
            console.warn(`Input con id #${key} no encontrado`);
         }
      });

      const tbody = document.querySelector('#verEntradaTable tbody');

      tbody.innerHTML = '';

      let sumaImporte = 0;
      let sumaPiezas = 0;

      let permisos = '';
      const span = document.getElementById('permisosSpan');
      if (span) {
         permisos = span.textContent || '';
      }

      // Función para verificar permisos
      const puedeLiberarPartes = permisos.includes('partes.liberar');

      piezas.forEach((pieza, index) => {
         const nuevaFila = tbody.insertRow();

         let botonLiberar = '';
         if (puedeLiberarPartes) {
            botonLiberar = `
               <button 
                  type="button" 
                  class="liberarParteButton bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded" 
                  title="Liberar parte" 
                  data-index="${index}"
                  data-id="${pieza.piezas.id ?? ''}"
                  data-numpartes="${pieza.piezas.numero_pzas_presupuesto ?? ''}"
                  data-nparte="${pieza.piezas.numero_parte ?? ''}"
               >
                  <i class="fa-solid fa-unlock"></i>
               </button>
            `;
         }

         nuevaFila.innerHTML = `
            <td class="px-2 py-1">
               <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">${pieza.piezas.numero_parte ?? ''}</span>
            </td>
            <td class="px-2 py-1" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
               <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full" title="${pieza.piezas.descripcion_w32 ?? ''}">${pieza.piezas.descripcion_w32 ?? ''}</span>
            </td>
            <td class="px-2 py-1">
               <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">${pieza.cantidad ?? 0}</span>
            </td>
            <td class="px-2 py-1">
               <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">${pieza.piezas.importe_unitario ?? 0}</span>
            </td>
            <td class="px-2 py-1">
               <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">${((pieza.cantidad * pieza.piezas.importe_unitario) ?? 0).toFixed(2)}</span>
            </td>
            <td class="px-2 py-1 text-center">
               ${botonLiberar}
            </td>
         `;
         sumaImporte += Number(pieza.IMP) || 0;
      });

      document.getElementById("importe").value = sumaImporte.toFixed(2);

   } catch (error) {
      console.error('Error:', error);
   }
}

export const liberarPartes = async (e) => {
   const idParte = e.dataset.id;
   const numeroParte = e.dataset.nparte;
   const numPartes = parseInt(e.dataset.numpartes) || 1;
   const idVale = document.getElementById("id_vale").value;

   // Obtener el número de entrada de la URL
   const urlParams = new URLSearchParams(window.location.search);
   // const numEntrada = urlParams.get('numEntrada');
   const numEntrada = document.querySelector(".numero-entrada").textContent;


   swalAlert({
      title: '¿Cuántas partes deseas liberar?',
      html: `
         <input 
            id="cantidad" 
            type="number" 
            min="1" 
            max="${numPartes}" 
            value="1" 
            class="swal2-input" 
            style="width: 100px; text-align: center;"
         >
         <div style="font-size: 0.9em; color: #888;">Máximo: ${numPartes}</div>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Liberar',
      cancelButtonText: 'Cancelar',
      theme: 'auto',
      preConfirm: () => {
         const cantidad = parseInt(document.getElementById('cantidad').value, 10);
         if (isNaN(cantidad) || cantidad < 1 || cantidad > numPartes) {
            Swal.showValidationMessage(`Por favor ingresa un valor entre 1 y ${numPartes}`);
            return false;
         }
         return cantidad;
      },
      buttonsStyling: true
   }).then(async (result) => {
      if (result.isConfirmed && result.value) {
         try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const cantidadLiberar = result.value;

            const formData = new FormData();
            formData.append('numEntrada', numEntrada);
            formData.append('idParte', idParte);
            formData.append('cantidad', cantidadLiberar);

            const response = await fetch(`${UrlProyecto}/${Perfil}/entradas/liberar-parte`, {
               method: 'POST',
               headers: {
                  'X-CSRF-TOKEN': csrfToken
               },
               body: formData
            });

            if (!response.ok) {
               throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();

            // Mostrar SweetAlert con la respuesta recibida del backend
            if (data.success === true) {
               swalAlert({
                  title: data.title,
                  text: data.message,
                  icon: data.icon,
                  timer: 3000,
                  timerProgressBar: true,
                  showConfirmButton: false,
                  allowOutsideClick: false,
                  allowEscapeKey: false,
                  theme: 'auto',
                  didOpen: () => {
                     Swal.showLoading();
                  }
               });

               setTimeout(async () => {
                  try {
                     const formDataMail = new FormData();
                     formDataMail.append('numEntrada', numEntrada);
                     formDataMail.append('numParte', numeroParte);
                     formDataMail.append('cantidad', cantidadLiberar);
                     formDataMail.append('idVale', idVale);
                     formDataMail.append('link', `${UrlProyecto}/${Perfil}/entradas/ver?numEntrada=${numEntrada}`);

                     showSendingMailAlert();
                     const responseMail = await fetch(`${UrlProyecto}/${Perfil}/entradas/mail-liberacion-partes`, {
                        method: 'POST',
                        headers: {
                           'X-CSRF-TOKEN': csrfToken
                        },
                        body: formDataMail
                     });

                     if (!responseMail.ok) {
                        // Si la respuesta del correo no es exitosa, mostrar error y NO recargar
                        swalAlert({
                           title: 'Error',
                           text: 'No se pudo enviar el correo de liberación de partes. Intenta nuevamente.',
                           icon: 'error',
                           confirmButtonText: 'Aceptar',
                           theme: 'auto'
                        });
                        return;
                     }

                     // Si el correo se envió correctamente, recargar
                     location.reload();
                  } catch (errorMail) {
                     // Si falla el fetch del correo, mostrar error y NO recargar
                     console.error('Error al enviar el correo de liberación de partes:', errorMail);
                     swalAlert({
                        title: 'Error',
                        text: 'Ocurrió un error al intentar enviar el correo de liberación de partes.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        theme: 'auto'
                     });
                  }
               }, 3000);
            }

         } catch (error) {
            console.error('Error al liberar la parte:', error);
            swalAlert({
               title: 'Error',
               text: 'No se pudo conectar con el servidor. Intenta nuevamente.',
               icon: 'error',
               confirmButtonText: 'Aceptar',
               theme: 'auto'
            });
         }
      }
   });
}
export const limpiarEstado = async () => {
   $('input[name="filtroEstado"]').prop('checked', false);
   await recargarTabla();
}

export const buscar = async (e) => {
   $('#entradasTable').DataTable().search(e.target.value).draw();
}
