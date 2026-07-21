<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-brand-primary leading-tight">Catálogo de precios</h2>
                <p class="mt-1 text-base text-brand-muted">
                    Precios sugeridos para servicios y tratamientos dentales. Los cambios no afectan presupuestos ya emitidos.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div
            class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8"
            x-data="catalogoPrecios({
                servicios: @js($servicios),
                tarifas: @js($tarifas),
                estadosOdontograma: @js($estadosOdontograma),
                endpoints: {
                    servicioStore: '{{ route('precios.servicios.store') }}',
                    servicioUpdate: '{{ url('precios/servicios') }}',
                    tarifaStore: '{{ route('precios.tarifas.store') }}',
                    tarifaUpdate: '{{ url('precios/tarifas') }}',
                    tarifaDestroy: '{{ url('precios/tarifas') }}',
                },
            })"
        >
            {{-- Servicios --}}
            <div class="space-y-3">
                <template x-if="mensajes.servicios.texto">
                    <div
                        class="rounded-md border px-4 py-3 text-sm"
                        :class="mensajes.servicios.tipo === 'error'
                            ? 'border-rose-200 bg-rose-50 text-rose-700'
                            : 'border-emerald-200 bg-emerald-50 text-emerald-700'"
                        x-text="mensajes.servicios.texto"
                    ></div>
                </template>

                <x-card class="overflow-hidden">
                <div class="border-b border-brand-border px-6 py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-brand-primary">Servicios</h3>
                            <p class="mt-1 text-sm text-brand-muted">
                                Nombre, descripción, duración, precio sugerido y disponibilidad. Los cambios de duración solo afectan agendas nuevas.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-brand-primary transition hover:bg-brand-soft"
                            @click="abrirNuevoServicio = ! abrirNuevoServicio"
                        >
                            <x-lucide-plus class="h-4 w-4" />
                            <span x-text="abrirNuevoServicio ? 'Cancelar' : 'Agregar servicio'"></span>
                        </button>
                    </div>
                </div>

                {{-- Formulario nuevo servicio --}}
                <div
                    x-show="abrirNuevoServicio"
                    x-cloak
                    x-transition
                    class="border-b border-brand-border bg-brand-soft px-6 py-5"
                >
                    <div class="grid gap-3 sm:grid-cols-[1fr_1fr_120px_140px_auto]">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted">Nombre</label>
                            <input
                                type="text"
                                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="Ej. Limpieza dental"
                                x-model="nuevoServicio.nombre"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted">Descripción</label>
                            <input
                                type="text"
                                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="Opcional"
                                x-model="nuevoServicio.descripcion"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted">Duración (min)</label>
                            <input
                                type="number"
                                min="5"
                                max="600"
                                step="5"
                                class="mt-1 block w-full rounded-md border-brand-border text-right text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                x-model="nuevoServicio.duracion_minutos"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted">Precio (Q)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full rounded-md border-brand-border text-right text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                x-model="nuevoServicio.precio_sugerido"
                            >
                        </div>

                        <div class="flex items-end">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                                :disabled="! nuevoServicioValido || guardandoNuevoServicio"
                                @click="agregarServicio()"
                                x-text="guardandoNuevoServicio ? 'Guardando…' : 'Agregar'"
                            ></button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border">
                        <thead class="bg-brand-soft">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Descripción</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-brand-muted">Duración (min)</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-brand-muted">Precio (Q)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-brand-muted">Activo</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-brand-muted">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border bg-brand-contrast">
                            <template x-for="(servicio, idx) in servicios" :key="servicio.id">
                                <tr>
                                    <td class="px-4 py-3 text-sm">
                                        <input
                                            type="text"
                                            class="block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                            x-model="servicio.nombre"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <input
                                            type="text"
                                            class="block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                            placeholder="Opcional"
                                            x-model="servicio.descripcion"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <input
                                            type="number"
                                            min="5"
                                            max="600"
                                            step="5"
                                            class="w-24 rounded-md border-brand-border text-right text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                            x-model="servicio.duracion_minutos"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-28 rounded-md border-brand-border text-right text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                            x-model="servicio.precio_sugerido"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm">
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-brand-border text-brand-primary focus:ring-brand-primary"
                                            x-model="servicio.activo"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-3 py-1.5 text-xs font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                                            :disabled="servicio.guardando"
                                            @click="guardarServicio(idx)"
                                            x-text="servicio.guardando ? 'Guardando…' : 'Guardar'"
                                        ></button>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="servicios.length === 0">
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-brand-muted">
                                        No hay servicios registrados. Agrega uno con el botón de arriba.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                </x-card>
            </div>

            {{-- Tarifas por tratamiento dental --}}
            <div class="space-y-3">
                <template x-if="mensajes.tarifas.texto">
                    <div
                        class="rounded-md border px-4 py-3 text-sm"
                        :class="mensajes.tarifas.tipo === 'error'
                            ? 'border-rose-200 bg-rose-50 text-rose-700'
                            : 'border-emerald-200 bg-emerald-50 text-emerald-700'"
                        x-text="mensajes.tarifas.texto"
                    ></div>
                </template>

                <x-card class="overflow-hidden">
                <div class="border-b border-brand-border px-6 py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-brand-primary">Tarifas por tratamiento dental</h3>
                            <p class="mt-1 text-sm text-brand-muted">
                                Una tarifa por estado del odontograma. Se usa como precio sugerido al generar el presupuesto desde el odontograma.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-brand-primary transition hover:bg-brand-soft"
                            @click="abrirNuevaTarifa = ! abrirNuevaTarifa"
                        >
                            <x-lucide-plus class="h-4 w-4" />
                            <span x-text="abrirNuevaTarifa ? 'Cancelar' : 'Agregar tarifa'"></span>
                        </button>
                    </div>
                </div>

                {{-- Formulario nueva tarifa --}}
                <div
                    x-show="abrirNuevaTarifa"
                    x-cloak
                    x-transition
                    class="border-b border-brand-border bg-brand-soft px-6 py-5"
                >
                    <div class="grid gap-3 sm:grid-cols-[180px_1fr_140px_auto]">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted">Estado de pieza</label>
                            <select
                                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                x-model="nueva.estado_pieza"
                            >
                                <option value="">Seleccionar…</option>
                                <template x-for="estado in estadosOdontograma" :key="estado">
                                    <option
                                        :value="estado"
                                        :disabled="estadosUsados.includes(estado)"
                                        x-text="estadoLabel(estado) + (estadosUsados.includes(estado) ? ' (ya existe)' : '')"
                                    ></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted">Nombre legible</label>
                            <input
                                type="text"
                                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="Ej. Tratamiento de caries"
                                x-model="nueva.nombre_legible"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-brand-muted">Precio (Q)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full rounded-md border-brand-border text-left text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                x-model="nueva.precio_sugerido"
                            >
                        </div>

                        <div class="flex items-end">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                                :disabled="! nuevaValida || guardandoNueva"
                                @click="agregarTarifa()"
                                x-text="guardandoNueva ? 'Guardando…' : 'Agregar'"
                            ></button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border">
                        <thead class="bg-brand-soft">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Nombre legible</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Precio (Q)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-brand-muted">Activa</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-brand-muted">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border bg-brand-contrast">
                            <template x-for="(tarifa, idx) in tarifas" :key="tarifa.id">
                                <tr>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex rounded-full bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700" x-text="estadoLabel(tarifa.estado_pieza)"></span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <input
                                            type="text"
                                            class="block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                            x-model="tarifa.nombre_legible"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-left text-sm">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-28 rounded-md border-brand-border text-left text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                            x-model="tarifa.precio_sugerido"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm">
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-brand-border text-brand-primary focus:ring-brand-primary"
                                            x-model="tarifa.activo"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-3 py-1.5 text-xs font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                                                :disabled="tarifa.guardando"
                                                @click="guardarTarifa(idx)"
                                                x-text="tarifa.guardando ? 'Guardando…' : 'Guardar'"
                                            ></button>
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-md border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                                @click="eliminarTarifa(idx)"
                                            >
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="tarifas.length === 0">
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-brand-muted">
                                        No hay tarifas registradas. Agrega una con el botón de arriba.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                </x-card>
            </div>
        </div>
    </div>

    <script>
        function catalogoPrecios(initial) {
            const labelsEstado = {
                sana: 'Sana',
                caries: 'Caries',
                obturada: 'Obturada',
                ausente: 'Ausente',
                extraccion: 'Extracción',
                corona: 'Corona',
                endodoncia: 'Endodoncia',
            };

            return {
                servicios: initial.servicios.map(s => ({
                    ...s,
                    precio_sugerido: Number(s.precio_sugerido),
                    duracion_minutos: Number(s.duracion_minutos),
                    activo: !! s.activo,
                    guardando: false,
                })),
                tarifas: initial.tarifas.map(t => ({
                    ...t,
                    precio_sugerido: Number(t.precio_sugerido),
                    activo: !! t.activo,
                    guardando: false,
                })),
                estadosOdontograma: initial.estadosOdontograma,
                endpoints: initial.endpoints,
                abrirNuevaTarifa: false,
                guardandoNueva: false,
                nueva: { estado_pieza: '', nombre_legible: '', precio_sugerido: '' },
                abrirNuevoServicio: false,
                guardandoNuevoServicio: false,
                nuevoServicio: { nombre: '', descripcion: '', duracion_minutos: 30, precio_sugerido: '', activo: true },
                mensajes: {
                    servicios: { texto: '', tipo: 'success' },
                    tarifas: { texto: '', tipo: 'success' },
                },

                get estadosUsados() {
                    return this.tarifas.map(t => t.estado_pieza);
                },

                get nuevoServicioValido() {
                    return this.nuevoServicio.nombre.trim().length > 0
                        && Number(this.nuevoServicio.duracion_minutos) >= 5
                        && this.nuevoServicio.precio_sugerido !== ''
                        && Number(this.nuevoServicio.precio_sugerido) >= 0;
                },

                get nuevaValida() {
                    return this.nueva.estado_pieza
                        && this.nueva.nombre_legible.trim().length > 0
                        && Number(this.nueva.precio_sugerido) >= 0
                        && this.nueva.precio_sugerido !== '';
                },

                estadoLabel(estado) {
                    return labelsEstado[estado] || estado;
                },

                csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },

                showMensaje(seccion, texto, tipo = 'success') {
                    if (! this.mensajes[seccion]) return;
                    this.mensajes[seccion].texto = texto;
                    this.mensajes[seccion].tipo = tipo;
                    setTimeout(() => {
                        if (this.mensajes[seccion].texto === texto) {
                            this.mensajes[seccion].texto = '';
                        }
                    }, 3500);
                },

                async fetchJson(url, options = {}) {
                    const res = await fetch(url, {
                        ...options,
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf(),
                            ...(options.headers || {}),
                        },
                    });

                    if (! res.ok) {
                        let mensaje = 'Error ' + res.status;
                        try {
                            const data = await res.json();
                            if (data.message) mensaje = data.message;
                            if (data.errors) {
                                mensaje = Object.values(data.errors).flat().join(' · ');
                            }
                        } catch (e) {}
                        throw new Error(mensaje);
                    }

                    return res.json();
                },

                async guardarServicio(idx) {
                    const s = this.servicios[idx];
                    s.guardando = true;
                    try {
                        const data = await this.fetchJson(`${this.endpoints.servicioUpdate}/${s.id}`, {
                            method: 'PATCH',
                            body: JSON.stringify({
                                nombre: (s.nombre || '').trim(),
                                descripcion: (s.descripcion || '').trim() || null,
                                duracion_minutos: Number(s.duracion_minutos),
                                precio_sugerido: Number(s.precio_sugerido),
                                activo: s.activo,
                            }),
                        });
                        Object.assign(s, {
                            ...data.servicio,
                            precio_sugerido: Number(data.servicio.precio_sugerido),
                            duracion_minutos: Number(data.servicio.duracion_minutos),
                            activo: !! data.servicio.activo,
                        });
                        this.showMensaje('servicios', `Servicio "${s.nombre}" actualizado.`);
                    } catch (e) {
                        this.showMensaje('servicios', e.message, 'error');
                    } finally {
                        s.guardando = false;
                    }
                },

                async agregarServicio() {
                    this.guardandoNuevoServicio = true;
                    try {
                        const data = await this.fetchJson(this.endpoints.servicioStore, {
                            method: 'POST',
                            body: JSON.stringify({
                                nombre: this.nuevoServicio.nombre.trim(),
                                descripcion: (this.nuevoServicio.descripcion || '').trim() || null,
                                duracion_minutos: Number(this.nuevoServicio.duracion_minutos),
                                precio_sugerido: Number(this.nuevoServicio.precio_sugerido),
                                activo: true,
                            }),
                        });
                        this.servicios.push({
                            ...data.servicio,
                            precio_sugerido: Number(data.servicio.precio_sugerido),
                            duracion_minutos: Number(data.servicio.duracion_minutos),
                            activo: !! data.servicio.activo,
                            guardando: false,
                        });
                        this.servicios.sort((a, b) => a.nombre.localeCompare(b.nombre));
                        this.nuevoServicio = { nombre: '', descripcion: '', duracion_minutos: 30, precio_sugerido: '', activo: true };
                        this.abrirNuevoServicio = false;
                        this.showMensaje('servicios', 'Servicio agregado.');
                    } catch (e) {
                        this.showMensaje('servicios', e.message, 'error');
                    } finally {
                        this.guardandoNuevoServicio = false;
                    }
                },

                async agregarTarifa() {
                    this.guardandoNueva = true;
                    try {
                        const data = await this.fetchJson(this.endpoints.tarifaStore, {
                            method: 'POST',
                            body: JSON.stringify({
                                estado_pieza: this.nueva.estado_pieza,
                                nombre_legible: this.nueva.nombre_legible.trim(),
                                precio_sugerido: Number(this.nueva.precio_sugerido),
                                activo: true,
                            }),
                        });
                        this.tarifas.push({
                            ...data.tarifa,
                            precio_sugerido: Number(data.tarifa.precio_sugerido),
                            activo: !! data.tarifa.activo,
                            guardando: false,
                        });
                        this.tarifas.sort((a, b) => a.estado_pieza.localeCompare(b.estado_pieza));
                        this.nueva = { estado_pieza: '', nombre_legible: '', precio_sugerido: '' };
                        this.abrirNuevaTarifa = false;
                        this.showMensaje('tarifas', 'Tarifa agregada.');
                    } catch (e) {
                        this.showMensaje('tarifas', e.message, 'error');
                    } finally {
                        this.guardandoNueva = false;
                    }
                },

                async guardarTarifa(idx) {
                    const t = this.tarifas[idx];
                    t.guardando = true;
                    try {
                        const data = await this.fetchJson(`${this.endpoints.tarifaUpdate}/${t.id}`, {
                            method: 'PATCH',
                            body: JSON.stringify({
                                estado_pieza: t.estado_pieza,
                                nombre_legible: t.nombre_legible,
                                precio_sugerido: Number(t.precio_sugerido),
                                activo: t.activo,
                            }),
                        });
                        Object.assign(t, {
                            ...data.tarifa,
                            precio_sugerido: Number(data.tarifa.precio_sugerido),
                            activo: !! data.tarifa.activo,
                        });
                        this.showMensaje('tarifas', `Tarifa "${t.nombre_legible}" actualizada.`);
                    } catch (e) {
                        this.showMensaje('tarifas', e.message, 'error');
                    } finally {
                        t.guardando = false;
                    }
                },

                async eliminarTarifa(idx) {
                    const t = this.tarifas[idx];
                    const ok = await window.confirmDialog({
                        title: '¿Eliminar tarifa?',
                        message: `Estás a punto de eliminar la tarifa "${t.nombre_legible}". Los presupuestos ya emitidos no se ven afectados (los precios se guardan como snapshot).`,
                        confirmText: 'Eliminar',
                        variant: 'danger',
                    });
                    if (! ok) return;
                    try {
                        await this.fetchJson(`${this.endpoints.tarifaDestroy}/${t.id}`, { method: 'DELETE' });
                        this.tarifas.splice(idx, 1);
                        this.showMensaje('tarifas', 'Tarifa eliminada.');
                    } catch (e) {
                        this.showMensaje('tarifas', e.message, 'error');
                    }
                },
            };
        }
    </script>
</x-app-layout>
