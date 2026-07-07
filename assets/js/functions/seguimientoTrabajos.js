import SeguimientoTrabajos from '../class/SeguimientoTrabajos.js';
import { UrlProyecto, swalQuestion, token } from '../functions/const.js';

const seguimientoTrabajosClass = new SeguimientoTrabajos();

export const DTLoad = async () => {
   return seguimientoTrabajosClass.DTable();
}

export const recargarTabla = async () => {
   $('#seguimientoTrabajosTable').DataTable().ajax.reload();
}

export const limpiarEstado = async () => {
   $('input[name="filtroEstado"]').prop('checked', false);
   await recargarTabla();
}