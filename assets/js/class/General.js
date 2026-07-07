import { UrlProyecto, Perfil, ToastLoad , swalAlert} from "../functions/const.js";

class General {
   constructor() {
      this.form;
   }

   setForm(form = null) {
      this.form = form ? new FormData(form) : new FormData();
      return this;
   }

   setAppendForm(obj) {
      Object.entries(obj).forEach(([k, v]) => {
         this.form.append(k, v);
      });
      return this;
   }

   async get(url = "") {
      try {
         const e = await fetch(`${UrlProyecto}/${Perfil}/${url}`);
         if (e.status == 419)
            return ToastLoad({
               icon: "error",
               title:
                  "Ha superado el tiempo de espera maximo. La pagina se actualizará",
               timer: 3000,
            }).then(() => window.location.reload());
         return await e.json();
      } catch (error) {
         swalAlert({
            title: "¡Oops!",
            text: "Ha ocurrido un error al intentar realizar la petición. Por favor, comuníquese con sistema para repartar el error.",
            icon: "error",
         });
         console.error(error);
      }
   }

   async post(url = "") {
      try {
         const e = await fetch(`${UrlProyecto}/${Perfil}/${url}`, {
            method: "POST",
            body: this.form,
         });
         if (e.status == 419)
            return ToastLoad({
               title:
                  "Ha superado el tiempo de espera maximo. La pagina se actualizará",
               timer: 3000,
            }).then(() => window.location.reload());
         return await e.json();
      } catch (error) {
         swalAlert({
            title: "¡Oops!",
            text: "Ha ocurrido un error al intentar realizar la petición. Por favor, comuníquese con sistema para reportar el error.",
            icon: "error",
         });
         console.error(error);
      }
   }
}
export default General;
