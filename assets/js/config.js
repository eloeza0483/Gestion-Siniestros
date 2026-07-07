const urlDom = window.location.origin;
const URLactual = window.location.pathname;
const UrlSplit = URLactual.split('/');
const UrlProyecto = `${urlDom}/${UrlSplit[1]}`;

// Exportar las variables para que estén disponibles en otros archivos
export { urlDom, URLactual, UrlSplit, UrlProyecto };