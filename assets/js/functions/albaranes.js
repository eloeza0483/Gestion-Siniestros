import Albaranes from '../class/Albaranes.js';
import { UrlProyecto, Perfil, swalQuestion, token } from '../functions/const.js';

const albaranesClass = new Albaranes();

export const DTLoad = async () => {
   return albaranesClass.DTable();
}

export const recargarTabla = async () => {
   $('#albaranesTable').DataTable().ajax.reload();
}

export const limpiarEstado = async () => {
   $('input[name="filtroEstado"]').prop('checked', false);
   await recargarTabla();
}

export const buscar = async (e) => {
   $('#albaranesTable').DataTable().search(e.target.value).draw();
}

export const findAlbaranByNumero = async (numeroAlbaran) => {
   try {
      const response = await fetch(`${UrlProyecto}/${Perfil}/albaranes/${numeroAlbaran}`);
      if (!response.ok) {
         throw new Error('Error al obtener el albaran');
      }
      const result = await response.json();
      const datos = result.albaran;
      console.log(datos);

      const piezas = result.piezas;



      const inputs = {
         numero_siniestro: datos.siniestros.numero_siniestro, aseguradora: datos.siniestros.vehiculo_info.aseguradora, importe: datos.importe, vehiculo: datos.siniestros.vehiculo_info.vehiculo, modelo: datos.siniestros.vehiculo_info.modelo, estado: datos.estado
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

      const tbody = document.querySelector('#verAlbaranTable tbody');

      tbody.innerHTML = '';

      let permisos = '';
      const span = document.getElementById('permisosSpan');
      if (span) {
         permisos = span.textContent || '';
      }

      // Función para verificar permisos
      const puedeLiberarPartes = permisos.includes('partes.liberar');

      let sumaImporte = 0;
      let sumaPiezas = 0;
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
            <td class="px-2 py-1">
               <span class="text-sm font-medium me-2 px-2.5 py-0.5 text-gray-900 dark:text-white w-full">${pieza.piezas.descripcion_w32 ?? ''}</span>
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
         sumaImporte += Number(pieza.IMPIVA1) || 0;
      });

      // document.getElementById("importe").value = sumaImporte.toFixed(2);

   } catch (error) {
      console.error('Error:', error);
   }
}

export const liberarPartes = async (e) => {
   const idParte = e.dataset.id;
   const urlParams = new URLSearchParams(window.location.search);
   const numAlbaran = urlParams.get('numAlbaran');

   swalQuestion("La pieza se desactivara del albaran por completo.", {
      title: '¿Estás seguro de liberar esta pieza?',
      text: "La pieza se desactivará del albarán por completo.",
      icon: 'question',
      confirmButtonText: 'Sí, liberar',
      cancelButtonText: 'Cancelar'
   }).then(async (result) => {
      if (result.isConfirmed) {
         try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const formData = new FormData();
            formData.append('numAlbaran', numAlbaran);
            formData.append('idParte', idParte);

            const response = await fetch(`${UrlProyecto}/${Perfil}/albaranes/liberar-parte`, {
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

            if (data.success === true) {
               Swal.fire({
                  title: data.title,
                  text: data.message,
                  icon: data.icon,
                  timer: 2000,
                  showConfirmButton: false,
                  theme: 'auto'
               });
               setTimeout(() => {
                  location.reload();
               }, 2000);
            }

         } catch (error) {
            console.error('Error al liberar la parte:', error);
            Swal.fire({
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
