@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-[1600px] px-4 lg:px-10 pb-12">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div
                    class="mb-2 inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
                    <i class="fa-solid fa-shield-halved"></i>
                    Administracion
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                    Panel de permisos
                </h1>
                <p class="mt-1 max-w-3xl text-sm text-gray-600 dark:text-gray-400">
                    Roles principales, roles secundarios y permisos adicionales por usuario y sucursal.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-2 text-center sm:min-w-[360px]">
                <div class="rounded-lg border border-gray-200 bg-white/75 px-3 py-2 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $usuarios->count() }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Usuarios</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white/75 px-3 py-2 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $perfiles->count() }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Sucursales</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white/75 px-3 py-2 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $roles->count() }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Roles</p>
                </div>
            </div>
        </div>

        <div id="roles-data" class="hidden"
            data-roles="{{ json_encode($roles->map(fn($r) => ['id' => $r->id, 'rol' => $r->rol, 'id_perfil' => $r->id_perfil])) }}">
        </div>

        <!-- Buscador de Usuario Horizontal -->
        <div class="mb-6 rounded-xl border border-gray-200/80 bg-white/80 px-5 py-4 shadow-sm backdrop-blur dark:border-gray-700/70 dark:bg-gray-900/60 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex items-center gap-3 shrink-0">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-600/10 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <i class="fa-solid fa-user text-sm"></i>
                </span>
                <div class="leading-tight">
                    <label for="buscar_usuario" class="block text-sm font-bold text-gray-900 dark:text-white leading-tight">
                        Buscar usuario
                    </label>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Escribe el nombre completo para consultar y gestionar sus permisos.</p>
                </div>
            </div>
            <div class="relative w-full sm:max-w-sm ml-auto">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="buscar_usuario" name="buscar_usuario" list="usuarios"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 py-2.5 pl-9 pr-3 text-sm text-gray-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                    placeholder="Ej. Ernesto Loeza Camargo" autocomplete="off">
                <datalist id="usuarios">
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->fullname }}" data-id="{{ $usuario->id }}"></option>
                    @endforeach
                </datalist>
            </div>
        </div>

        <!-- Contenedor principal de permisos -->
        <div class="w-full">
            <!-- Estado vacío -->
            <div id="seccion-permisos-vacia" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white/50 p-12 text-center dark:border-gray-700 dark:bg-gray-900/30">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <i class="fa-solid fa-user-gear text-xl"></i>
                </div>
                <h3 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">Sin usuario seleccionado</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm">Selecciona un usuario en la barra superior para consultar y gestionar sus permisos actuales en el sistema.</p>
            </div>

            <!-- Contenedor principal de permisos (2 cards side by side) -->
            <div id="seccion-permisos-activos" class="hidden">
                <div id="grid-permisos" class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                    <!-- ── Card Izquierda: Rol Principal ── -->
                    <div class="flex flex-col rounded-xl border border-yellow-300/60 bg-white/85 shadow-sm dark:border-yellow-700/40 dark:bg-gray-900/60 overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between gap-2 px-4 pt-4 pb-3 border-b border-yellow-200/60 dark:border-yellow-800/40">
                            <div class="min-w-0">
                                <div class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
                                    Rol Principal
                                </div>
                                <h3 id="panel-principal-rol" class="mt-1.5 truncate text-sm font-bold text-gray-900 dark:text-white">Cargando...</h3>
                                <p id="panel-principal-sucursal" class="text-xs text-gray-500 dark:text-gray-400 truncate"></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="shrink-0 inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500 mr-1">
                                    <span class="h-2 w-2 rounded-full bg-yellow-400"></span>
                                    Activo
                                </span>
                                <button type="button" id="btnEditarPrincipal"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-purple-300 bg-purple-50 px-3 py-1.5 text-xs font-semibold text-purple-700 transition hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-300 dark:border-purple-700 dark:bg-purple-950/40 dark:text-purple-300 dark:hover:bg-purple-900/50">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Editar
                                </button>
                                <button type="button" id="btnAsignarPrincipal"
                                    class="hidden inline-flex items-center gap-1.5 rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <i class="fa-solid fa-user-shield"></i>
                                    Asignar Rol Principal
                                </button>
                            </div>
                        </div>

                        <!-- Spinner de carga -->
                        <div id="principal-permisos-loading" class="py-6 flex justify-center items-center">
                            <div role="status" class="flex flex-col items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="h-6 w-6 animate-spin text-gray-200 fill-yellow-500 dark:text-gray-700" viewBox="0 0 100 101" fill="none">
                                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908Z" fill="currentColor"/>
                                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.579 24.5408 89.0397 20.9546C85.3262 17.1545 80.8661 14.0894 75.9186 12.0182C71.2175 10.1031 66.1856 9.07803 60.9832 9.07803C58.4668 9.07803 56.0318 9.28078 53.6814 9.6734C51.0823 10.1031 49.2524 12.7141 49.7827 15.3083C50.3129 17.8983 53.2083 19.6283 55.7977 19.1226C57.7153 18.7383 59.7013 18.5403 61.7244 18.5403C65.2545 18.5403 68.7161 19.2098 71.9644 20.5098C75.4244 21.8953 78.5571 24.0042 81.196 26.7358C83.7032 29.3213 85.7038 32.4419 86.9925 35.8705C87.9021 38.2247 91.5422 39.678 93.9676 39.0409Z" fill="currentFill"/>
                                </svg>
                                <span>Cargando...</span>
                            </div>
                        </div>

                        <!-- Lista de permisos principales (scrollable) -->
                        <div class="flex-1 overflow-y-auto px-4 py-3">
                            <div id="principal-permisos-lista" class="flex flex-wrap gap-1.5 min-h-[40px] text-gray-500"></div>
                        </div>
                    </div>

                    <!-- ── Card Derecha: Roles Secundarios ── -->
                    <div id="card-secundarios" class="flex flex-col rounded-xl border border-emerald-300/60 bg-white/85 shadow-sm dark:border-emerald-700/40 dark:bg-gray-900/60 overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between gap-2 px-4 pt-4 pb-3 border-b border-emerald-200/60 dark:border-emerald-800/40">
                            <div class="min-w-0">
                                <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Roles Secundarios
                                </div>
                                <h3 class="mt-1.5 text-sm font-bold text-gray-900 dark:text-white">Permisos Adicionales</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Otros perfiles asignados.</p>
                            </div>
                            <button type="button" id="btnAgregarSecundario"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                Agregar Rol
                            </button>
                        </div>

                        <!-- Contenedor scrollable de roles secundarios -->
                        <div class="flex-1 overflow-y-auto px-4 py-3">
                            <div id="secundarios-roles-contenedor" class="grid grid-cols-1 gap-3 sm:grid-cols-2"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- MODAL DE SELECCIÓN DE SUCURSAL Y ROL PARA NUEVA ASIGNACIÓN -->
        <div id="modal-seleccionar-rol" class="fixed inset-0 z-50 hidden overflow-y-auto overflow-x-hidden bg-gray-950/80 backdrop-blur-sm transition-all duration-300">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="relative w-full max-w-md rounded-xl border border-gray-800 bg-gray-900 p-6 text-left shadow-2xl transition-all my-8 flex flex-col">
                    <!-- Header -->
                    <div class="flex items-start justify-between border-b border-gray-800 pb-4">
                        <div>
                            <h3 id="modal-seleccionar-titulo" class="text-lg font-bold text-white">Asignar Rol</h3>
                            <p class="mt-1 text-xs text-gray-400">Selecciona la sucursal y el rol para continuar.</p>
                        </div>
                        <button type="button" id="modal-seleccionar-cerrar-x" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-800 hover:text-white transition">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    
                    <!-- Body -->
                    <div class="mt-4 space-y-4">
                        <!-- Sucursal -->
                        <div>
                            <label for="buscar_perfil" class="block text-sm font-semibold text-gray-300 mb-2">
                                Sucursal / Perfil
                            </label>
                            <select id="buscar_perfil" name="buscar_perfil"
                                class="block w-full rounded-lg border border-gray-700 bg-gray-800 p-2.5 text-sm text-white transition focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Selecciona una sucursal</option>
                                @foreach ($perfiles as $perfil)
                                    <option value="{{ $perfil->nombre }}" data-id="{{ $perfil->id }}">{{ $perfil->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rol -->
                        <div>
                            <label for="buscar_rol" class="block text-sm font-semibold text-gray-300 mb-2">
                                Rol
                            </label>
                            <select id="buscar_rol" name="buscar_rol"
                                class="block w-full rounded-lg border border-gray-700 bg-gray-800 p-2.5 text-sm text-white transition focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                                disabled>
                                <option value="">Selecciona un rol</option>
                            </select>
                            <p id="lbl-rol-hint" class="mt-1.5 text-[11px] text-gray-500">Selecciona sucursal primero.</p>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="mt-6 flex justify-end gap-2 border-t border-gray-800 pt-4">
                        <button type="button" id="modal-seleccionar-cancelar" class="rounded-lg border border-gray-700 bg-transparent px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-gray-800 hover:text-white transition">
                            Cancelar
                        </button>
                        <button type="button" id="modal-seleccionar-continuar" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            Continuar
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE EDICIÓN DE PERMISOS DETALLADOS -->
        <div id="modal-editar-permisos" class="fixed inset-0 z-50 hidden overflow-y-auto overflow-x-hidden bg-gray-950/80 backdrop-blur-sm transition-all duration-300">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="relative w-full max-w-5xl rounded-xl border border-gray-800 bg-gray-900 p-6 text-left shadow-2xl transition-all my-8 flex flex-col max-h-[90vh]">
                    <!-- Header -->
                    <div class="flex items-start justify-between border-b border-gray-800 pb-4">
                        <div>
                            <h3 id="modal-titulo-usuario" class="text-lg font-bold text-white">Editar Permisos Detallados</h3>
                            <p id="modal-subtitulo-rol" class="mt-1 text-xs text-gray-455" style="color: #94a3b8;">Usuario / Sucursal / Rol</p>
                        </div>
                        <button type="button" id="modal-btn-cerrar-x" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-800 hover:text-white transition">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    
                    <!-- Body (Scrollable) -->
                    <div class="mt-4 flex-1 overflow-y-auto pr-2">
                        <div class="mb-4 flex flex-wrap gap-2 text-xs text-gray-400 bg-gray-950/50 p-2.5 rounded-lg border border-gray-800/60">
                            <span class="inline-flex items-center gap-1 rounded bg-blue-950/40 px-2 py-0.5 font-medium text-blue-300 border border-blue-900/60">
                                <i class="fa-solid fa-lock"></i>
                                Heredado / Fijo (Del Rol)
                            </span>
                            <span class="inline-flex items-center gap-1 rounded bg-emerald-950/40 px-2 py-0.5 font-medium text-emerald-300 border border-emerald-900/60">
                                <i class="fa-solid fa-check"></i>
                                Activo / Adicional (Extra)
                            </span>
                            <span class="inline-flex items-center gap-1 rounded bg-gray-800 px-2 py-0.5 font-medium text-gray-300 border border-gray-700">
                                <i class="fa-solid fa-eye-slash"></i>
                                Inactivo (Haz clic para ver y activar)
                            </span>
                        </div>

                        <!-- Cargando del modal -->
                        <div id="modal-permisos-loading" class="flex flex-col items-center justify-center py-12 text-sm text-gray-450" style="color: #94a3b8;">
                            <svg class="h-8 w-8 animate-spin text-gray-700 fill-blue-500" viewBox="0 0 100 101" fill="none">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908Z" fill="currentColor"/>
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.579 24.5408 89.0397 20.9546C85.3262 17.1545 80.8661 14.0894 75.9186 12.0182C71.2175 10.1031 66.1856 9.07803 60.9832 9.07803C58.4668 9.07803 56.0318 9.28078 53.6814 9.6734C51.0823 10.1031 49.2524 12.7141 49.7827 15.3083C50.3129 17.8983 53.2083 19.6283 55.7977 19.1226C57.7153 18.7383 59.7013 18.5403 61.7244 18.5403C65.2545 18.5403 68.7161 19.2098 71.9644 20.5098C75.4244 21.8953 78.5571 24.0042 81.196 26.7358C83.7032 29.3213 85.7038 32.4419 86.9925 35.8705C87.9021 38.2247 91.5422 39.678 93.9676 39.0409Z" fill="currentFill"/>
                            </svg>
                            <span class="mt-3">Cargando matriz de permisos...</span>
                        </div>
                        
                        <!-- Grid de Permisos -->
                        <div id="modal-permisos-grid" class="space-y-4 hidden"></div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-gray-800 pt-4">
                        <button type="button" id="modal-btn-desmarcar" class="inline-flex items-center gap-2 rounded-lg border border-gray-700 bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-750 transition">
                            <i class="fa-solid fa-square-minus text-red-500"></i>
                            Desmarcar adicionales
                        </button>
                        <div class="flex gap-2">
                            <button type="button" id="modal-btn-cancelar" class="rounded-lg border border-gray-700 bg-transparent px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-gray-800 hover:text-white transition">
                                Cancelar
                            </button>
                            <button type="button" id="modal-btn-guardar" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                                <i class="fa-solid fa-save"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
