# Manual de Usuario

## Sistema Gestión de Siniestros

Este manual describe el uso general del sistema **Gestión de Siniestros** con base en los módulos visibles en la aplicación. Algunas opciones pueden cambiar según el **perfil activo** y los **permisos** asignados a cada usuario.

## 1. Objetivo del sistema

El sistema permite dar seguimiento al ciclo operativo de un siniestro, desde su registro hasta la gestión de presupuestos, vales, entradas, albaranes, facturas, reportes y control administrativo.

## 2. Acceso al sistema

### Inicio de sesión

1. Ingrese a la pantalla de acceso.
2. Capture su **usuario**.
3. Capture su **contraseña**.
4. Haga clic en **Acceder al sistema**.

Si las credenciales son correctas, el sistema lo llevará a la pantalla principal.

### Cierre de sesión

1. Haga clic en el ícono de usuario ubicado en la parte superior derecha.
2. Seleccione **Cerrar sesión**.

## 3. Selección de perfil

Después de iniciar sesión, el sistema puede mostrar uno o varios perfiles disponibles.

Ejemplos de perfiles:

- Talleres o áreas operativas
- Refacciones
- Otros perfiles configurados por la empresa

Para continuar:

1. Seleccione el perfil con el que desea trabajar.
2. El sistema abrirá los módulos disponibles para ese perfil.

Importante:

- El menú cambia según el perfil activo.
- También puede cambiar de perfil desde el menú del usuario cuando tenga más de uno asignado.

## 4. Estructura general de la pantalla

En la parte superior se encuentra la barra principal del sistema. Desde ahí podrá acceder a:

- Siniestros
- Presupuestos
- Vales
- Entradas
- Albaranes
- Facturas
- Reportes
- Proceso de vehículos
- Seguimiento de trabajos
- Administración

Además, en la esquina superior derecha encontrará:

- Usuario activo
- Departamento
- Cambio de perfil
- Cierre de sesión

## 5. Consideraciones generales de uso

En la mayoría de los módulos encontrará:

- Un campo de **búsqueda**
- Un botón de **Filtros**
- Una tabla con los registros
- Acciones disponibles según permisos

Recomendaciones:

- Use la búsqueda para localizar rápidamente un registro por número, nombre o dato relacionado.
- Use los filtros para acotar resultados por estado.
- Revise siempre el perfil activo antes de capturar información.

## 6. Módulo de Siniestros

El módulo **Siniestros** concentra el registro y consulta de casos.

### Acciones principales

- Consultar siniestros
- Filtrar por estado
- Crear siniestros
- Cancelar siniestros
- Reabrir siniestros
- Cerrar siniestros
- Notificar cancelaciones

### Estados disponibles

- Abiertos
- Completados
- Cerrados
- Cancelados

### Flujo básico

1. Entre al módulo **Siniestros**.
2. Revise la tabla de registros.
3. Si tiene permisos, haga clic en **Crear siniestros** para registrar uno nuevo.
4. Use filtros para consultar únicamente los casos del estado requerido.
5. Abra el registro correspondiente para consultar su detalle o ejecutar acciones.

### Configuración relacionada

Dentro de este módulo también puede existir una opción de **Configuración** para catálogos relacionados, como marcas u otros elementos de apoyo al registro.

## 7. Módulo de Presupuestos

El módulo **Presupuestos** permite generar, consultar, cotizar y exportar presupuestos ligados a un siniestro.

### Opciones principales

- Crear presupuesto
- Ver presupuestos
- Cotizar presupuesto
- Descargar presupuesto en Excel
- Agregar vale desde un presupuesto
- Enviar evidencias
- Enviar correo de cotización

### Estados frecuentes

- Pendiente
- Sin cotizar
- Cotizado
- Cancelado

### Flujo para crear un presupuesto

1. Entre a **Presupuestos**.
2. Seleccione **Crear**.
3. Capture el **número de orden**.
4. El sistema completará datos relacionados cuando existan:
   - Número de siniestro
   - Cliente o aseguradora
   - VIN
   - Vehículo
   - Marca
   - Modelo
5. Seleccione el **proveedor**, cuando aplique.
6. Capture la información económica o técnica requerida.
7. Guarde el registro.

### Flujo para cotizar

1. Entre a **Presupuestos**.
2. Abra un presupuesto pendiente o sin cotizar.
3. Capture los datos de cotización.
4. Adjunte evidencias si su rol lo permite.
5. Envíe la cotización o genere el correo correspondiente.

## 8. Módulo de Vales

El módulo **Vales** controla la emisión y seguimiento de vales asociados a presupuestos.

### Acciones principales

- Consultar vales
- Crear o agregar vale desde un presupuesto
- Ver detalle del vale
- Asignar entrada
- Asignar albarán
- Agregar complemento
- Notificar creación de vale
- Solicitar modificación de partes
- Solicitar eliminación de partes
- Cancelar vales
- Exportar vale

### Estados disponibles

- Abierto
- Completado
- Cerrado
- Cancelado

### Flujo básico

1. Genere o abra un vale desde el presupuesto correspondiente.
2. Revise las piezas disponibles.
3. Asigne la **entrada** o el **albarán** cuando corresponda.
4. Registre complementos si hacen falta piezas adicionales.
5. Notifique a los responsables cuando el proceso lo requiera.

## 9. Módulo de Entradas

El módulo **Entradas** permite revisar y administrar las entradas relacionadas con vales o piezas.

### Acciones principales

- Consultar entradas
- Ver detalle de entrada
- Consultar información en W32
- Notificar entrada asignada
- Liberar partes
- Notificar liberación de partes

### Estados visibles

- Abiertos
- Cerrados
- Cancelados

### Flujo básico

1. Entre a **Entradas**.
2. Busque el registro requerido.
3. Abra el detalle para validar información.
4. Si aplica, libere partes o envíe la notificación correspondiente.

## 10. Módulo de Albaranes

El módulo **Albaranes** concentra la consulta y administración de albaranes vinculados al proceso.

### Acciones principales

- Consultar albaranes
- Ver detalle
- Consultar información en W32
- Notificar albarán asignado

### Estados visibles

- Abiertos
- Cerrados
- Facturados
- Cancelados

### Flujo básico

1. Entre a **Albaranes**.
2. Use búsqueda o filtros para localizar el albarán.
3. Revise el detalle.
4. Si corresponde, envíe la notificación de asignación.

## 11. Módulo de Facturas

El módulo **Facturas** sirve para consultar facturas registradas en el sistema.

### Acciones principales

- Consultar facturas
- Buscar por datos del registro

### Uso general

1. Entre a **Facturas**.
2. Use el campo de búsqueda.
3. Revise la tabla con la información disponible.

## 12. Módulo de Reportes

El módulo **Reportes** permite consultar información consolidada por entidad y por rango de fechas.

### Criterios disponibles

- Entidad:
  - Siniestros
  - Presupuestos
  - Vales
  - Facturas
  - Entradas
- Fecha inicial
- Fecha final
- Tipo de registro:
  - Registro
  - Actualización
- Estado
- Taller o perfil

### Flujo básico

1. Entre a **Reportes**.
2. Seleccione la entidad a consultar.
3. Defina el rango de fechas.
4. Indique el tipo de registro.
5. Si lo requiere, filtre por estado o taller.
6. Haga clic en el botón de búsqueda.
7. Revise los resultados en la tabla.

## 13. Módulo de Proceso de Vehículos

Este módulo permite dar seguimiento al estado operativo de los vehículos dentro del proceso.

### Estados disponibles

- Pendiente
- En proceso
- Pausado
- Finalizado

### Acciones principales

- Consultar procesos
- Buscar registros
- Filtrar por estado
- Cambiar estado del proceso, según permisos

## 14. Módulo de Seguimiento de Trabajos

Este módulo facilita el seguimiento general de actividades o trabajos en curso.

### Estados disponibles

- Pendiente
- En proceso
- Pausado
- Finalizado
- Casos especiales

### Uso general

1. Entre a **Seguimiento de trabajos**.
2. Use búsqueda o filtros.
3. Revise los trabajos activos y su estado actual.

## 15. Módulo de Administración

El módulo **Administración** está orientado al control de permisos.

### Funciones principales

- Buscar usuario
- Buscar rol
- Asignar rol

### Flujo básico

1. Entre a **Administración**.
2. Capture el nombre o correo del usuario.
3. Busque el rol que desea asignar.
4. Haga clic en **Asignar Rol**.

Importante:

- Este módulo debe ser utilizado únicamente por personal autorizado.

## 16. Uso de filtros y búsquedas

En varios módulos existe un botón de **Filtros**. Su objetivo es mostrar únicamente los registros que cumplan una condición, normalmente por **estado**.

Recomendaciones:

- Si no encuentra un registro, primero limpie los filtros.
- Después use el campo de búsqueda.
- Finalmente verifique que esté trabajando en el perfil correcto.

## 17. Permisos y restricciones

El sistema trabaja con permisos por usuario y por perfil. Por ello:

- Algunos usuarios solo pueden consultar información.
- Otros pueden crear, editar, cotizar, cancelar o notificar.
- No todos los módulos aparecen para todos los usuarios.

Si una opción no está visible, es posible que su usuario no tenga autorización para usarla.

## 18. Buenas prácticas

- Verifique siempre el perfil activo antes de iniciar.
- Capture correctamente números de orden, siniestro, vale y documentos relacionados.
- Use filtros para evitar trabajar sobre registros incorrectos.
- Antes de cerrar o cancelar un registro, confirme la información.
- Si una pantalla no responde como espera, recargue la consulta y valide los datos capturados.

## 19. Solución básica de problemas

### No puedo entrar al sistema

- Verifique usuario y contraseña.
- Confirme que su cuenta siga activa.
- Si el problema continúa, contacte a sistemas.

### No veo un módulo en el menú

- Revise el perfil activo.
- Verifique si su usuario tiene permisos.

### No encuentro un registro

- Limpie filtros.
- Use la búsqueda.
- Revise el estado del registro.
- Verifique que esté en el perfil correcto.

### No puedo realizar una acción

- Es probable que su rol no tenga permisos.
- También puede deberse al estado actual del registro.

## 20. Soporte

Si necesita apoyo:

- Consulte a su responsable de área.
- Contacte al equipo de sistemas.
- Reporte el módulo, el número de registro y el problema detectado para una atención más rápida.
