// Función para cambiar entre modo claro y oscuro
const toggleButton = document.querySelector("#themeToggleButton");
if (toggleButton) {
   toggleButton.addEventListener("click", function () {
      const isDarkMode = document.documentElement.classList.toggle('dark');
      document.body.classList.toggle('dark', isDarkMode);
      localStorage.theme = isDarkMode ? 'dark' : 'light';
   });
}

// Aplicar el tema guardado al cargar la página
document.documentElement.classList.toggle(
   "dark",
   localStorage.theme === "dark" ||
   (!("theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches),
);