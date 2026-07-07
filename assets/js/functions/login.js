import { swalAlert, UrlProyecto } from "./const.js";

export async function login(event) {
   event.preventDefault();

   const usuario = document.getElementById("usuario").value;
   const contrasena = document.getElementById("password").value;

   const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
   const searchingToast = Swal.mixin({
      toast: true,
      position: 'bottom-end',
      showConfirmButton: false,
      background: isDark ? '#1e293b' : '#ffffff',
      color: isDark ? '#f8fafc' : '#1e293b',
      customClass: {
         popup: "border border-slate-200 dark:border-slate-800 shadow-xl",
         title: "text-sm font-medium"
      },
      didOpen: (toast) => {
         toast.addEventListener('mouseenter', Swal.stopTimer);
         toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
   });

   searchingToast.fire({
      icon: 'info',
      title: 'Ingresando...',
   });

   try {
      const response = await fetch(UrlProyecto + "/login-api", {
         method: "POST",
         headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
         },
         body: JSON.stringify({
            usuario: usuario,
            password: contrasena,
         }),
      });

      const responseData = await response.json();

      if (responseData.success) {
         swalAlert({
            icon: 'success',
            title: responseData.message,
            showConfirmButton: false,
            theme: 'auto',
            timer: 3000,
         }).then(() => {
            window.location.href = responseData.urlintended;
         });
      } else {
         swalAlert({
            icon: responseData.icon || 'error',
            title: 'Error de acceso',
            text: responseData.message,
            theme: 'auto'
         });
      }
   } catch (error) {
      console.error("Error:", error);
   }
}