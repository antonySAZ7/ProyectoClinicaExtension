@php
    $editable = ! $isPortal && ! $consulta->presupuesto_aceptado_en;

    $itemsInicial = $consulta->presupuestoItems->map(fn ($item) => [
        'id' => $item->id,
        'pieza_id' => $item->pieza_id,
        'pieza_numero' => $item->pieza?->numero,
        'diagnostico' => $item->diagnostico,
        'tratamiento' => $item->tratamiento,
        'precio_unitario' => (float) $item->precio_unitario,
        'cantidad' => (int) $item->cantidad,
        'subtotal' => (float) $item->subtotal,
    ])->values();
@endphp

<x-card class="p-6">
    @if ($editable)
        <div
            x-data="presupuestoEditor({
                items: @js($itemsInicial),
                piezas: @js($piezasCatalogo),
                tarifas: @js($tarifasCatalogo),
                aceptadoEn: null,
                endpoints: {
                    store: '{{ route('consultas.presupuesto.store', $consulta) }}',
                    update: '{{ url('consultas/'.$consulta->id.'/presupuesto') }}',
                    destroy: '{{ url('consultas/'.$consulta->id.'/presupuesto') }}',
                    sugerencias: '{{ route('consultas.presupuesto.sugerencias', $consulta) }}',
                    aceptar: '{{ route('consultas.presupuesto.aceptar', $consulta) }}',
                },
            })"
            @odontograma-changed.window="actualizarEstadosPiezas($event.detail.piezas)"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-brand-primary">Presupuesto</h3>
                    <p class="mt-1 text-sm text-brand-muted">
                        <template x-if="! aceptadoEn">
                            <span>Pendiente de aceptación. Los ítems se pueden editar libremente.</span>
                        </template>
                        <template x-if="aceptadoEn">
                            <span>Aceptado el <span x-text="aceptadoEn"></span>. Los ítems quedan congelados.</span>
                        </template>
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-md border border-brand-border bg-white px-3 py-2 text-xs font-semibold text-brand-primary transition hover:bg-brand-soft disabled:opacity-50"
                        :disabled="generandoOdontograma || aceptadoEn"
                        @click="generarDesdeOdontograma()"
                    >
                        <x-lucide-wand-sparkles class="h-4 w-4" />
                        <span x-text="generandoOdontograma ? 'Generando…' : 'Generar desde odontograma'"></span>
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                        :disabled="aceptando || aceptadoEn || items.length === 0"
                        @click="marcarAceptado()"
                        x-text="aceptando ? 'Aceptando…' : 'Marcar aceptado'"
                    ></button>
                </div>
            </div>

            <template x-if="mensaje.texto">
                <div
                    class="mt-3 rounded-md border px-4 py-2 text-sm"
                    :class="mensaje.tipo === 'error'
                        ? 'border-rose-200 bg-rose-50 text-rose-700'
                        : 'border-emerald-200 bg-emerald-50 text-emerald-700'"
                    x-text="mensaje.texto"
                ></div>
            </template>

            <datalist id="presupuesto-tratamientos">
                <template x-for="t in tarifas" :key="t.nombre_legible">
                    <option :value="t.nombre_legible"></option>
                </template>
            </datalist>

            {{-- Tabla de items existentes --}}
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-brand-border text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">
                            <th class="py-2 pr-2 w-40">Pieza</th>
                            <th class="px-2 py-2">Diagnóstico</th>
                            <th class="px-2 py-2">Tratamiento</th>
                            <th class="px-2 py-2 w-24 text-right">Precio (Q)</th>
                            <th class="px-2 py-2 w-16">Cant.</th>
                            <th class="px-2 py-2 w-24 text-right">Subtotal</th>
                            <th class="px-2 py-2 w-32 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border">
                        <template x-for="(item, idx) in items" :key="item.id">
                            <tr>
                                <td class="py-2 pr-2">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex min-w-[2.5rem] items-center justify-center rounded-md bg-sky-100 px-2 py-1 text-sm font-bold text-sky-700"
                                                x-text="piezaNumero(item.pieza_id) ?? '—'"
                                                :class="piezaNumero(item.pieza_id) === null ? 'bg-gray-100 text-gray-400' : ''"
                                            ></span>
                                            <span class="text-xs text-brand-muted truncate" x-text="piezaNombre(item.pieza_id) ?? ''"></span>
                                        </div>
                                        <select
                                            class="w-full rounded-md border-brand-border text-xs shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                            x-model.number="item.pieza_id"
                                        >
                                            <option :value="null">Sin pieza</option>
                                            <template x-for="p in piezas" :key="p.id">
                                                <option :value="p.id" x-text="p.numero + ' · ' + p.nombre"></option>
                                            </template>
                                        </select>
                                    </div>
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        type="text"
                                        class="w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                        x-model="item.diagnostico"
                                        maxlength="255"
                                    >
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        type="text"
                                        list="presupuesto-tratamientos"
                                        class="w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                        x-model="item.tratamiento"
                                        maxlength="255"
                                        @change="autoCompletarDesdeTratamiento(item)"
                                    >
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full rounded-md border-brand-border text-right text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                        x-model.number="item.precio_unitario"
                                    >
                                </td>
                                <td class="px-2 py-2">
                                    <input
                                        type="number"
                                        min="1"
                                        max="999"
                                        class="w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                        x-model.number="item.cantidad"
                                    >
                                </td>
                                <td class="px-2 py-2 text-right font-medium text-brand-primary" x-text="formatQ((item.precio_unitario || 0) * (item.cantidad || 0))"></td>
                                <td class="px-2 py-2 text-right">
                                    <div class="flex justify-end gap-1">
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-2 py-1 text-xs font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                                            :disabled="item._guardando || ! itemValido(item)"
                                            @click="guardarItem(idx)"
                                            x-text="item._guardando ? '…' : 'Guardar'"
                                        ></button>
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-md border border-rose-300 bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                            @click="eliminarItem(idx)"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-if="items.length === 0">
                            <tr>
                                <td colspan="7" class="py-4 text-center text-brand-muted">
                                    Esta consulta todavía no tiene ítems de presupuesto. Agregue una línea o use "Generar desde odontograma".
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="pt-3 text-right text-brand-primary">Total</th>
                            <th class="pt-3 text-right text-base text-brand-primary" x-text="formatQ(totalEnVivo)"></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Form de nueva línea --}}
            <div class="mt-5 border-t border-brand-border pt-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Agregar línea</p>

                <div class="mt-3 grid gap-2 sm:grid-cols-[140px_minmax(0,1fr)_minmax(0,1fr)_110px_80px_auto]">
                    <select
                        class="rounded-md border-brand-border text-right text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        x-model.number="nuevo.pieza_id"
                        @change="autoCompletarDesdePieza()"
                    >
                        <option :value="null">Sin pieza</option>
                        <template x-for="p in piezas" :key="p.id">
                            <option
                                :value="p.id"
                                x-text="p.numero + ' · ' + p.nombre + (p.estado_consulta && p.estado_consulta !== 'sana' ? ' (' + p.estado_consulta + ')' : '')"
                            ></option>
                        </template>
                    </select>

                    <input
                        type="text"
                        class="rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        placeholder="Diagnóstico *"
                        maxlength="255"
                        x-model="nuevo.diagnostico"
                    >

                    <input
                        type="text"
                        list="presupuesto-tratamientos"
                        class="rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        placeholder="Tratamiento *"
                        maxlength="255"
                        x-model="nuevo.tratamiento"
                        @change="autoCompletarDesdeTratamiento(nuevo)"
                    >

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        placeholder="Precio *"
                        x-model.number="nuevo.precio_unitario"
                    >

                    <input
                        type="number"
                        min="1"
                        max="999"
                        class="rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        x-model.number="nuevo.cantidad"
                    >

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                        :disabled="guardandoNuevo || ! nuevoValido"
                        @click="agregarItem()"
                        x-text="guardandoNuevo ? 'Agregando…' : 'Agregar'"
                    ></button>
                </div>

                <p class="mt-2 text-xs text-brand-muted">
                    Si selecciona una pieza con estado distinto a "sana", el diagnóstico, tratamiento y precio se autocompletan desde el catálogo de tarifas (puede editarlos antes de agregar).
                </p>
            </div>
        </div>
    @else
        {{-- Read-only fallback (portal o presupuesto ya aceptado) --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-brand-primary">Presupuesto</h3>
                <p class="mt-1 text-sm text-brand-muted">
                    @if ($consulta->presupuesto_aceptado_en)
                        Aceptado el {{ $consulta->presupuesto_aceptado_en->format('d/m/Y H:i') }}.
                    @else
                        Pendiente de aceptación.
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">
                        <th class="py-2 pr-3">Pieza</th>
                        <th class="px-3 py-2">Diagnóstico</th>
                        <th class="px-3 py-2">Tratamiento</th>
                        <th class="px-3 py-2 text-right">Precio</th>
                        <th class="px-3 py-2">Cant.</th>
                        <th class="px-3 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border">
                    @forelse ($consulta->presupuestoItems as $item)
                        <tr>
                            <td class="py-2 pr-3 text-brand-primary">{{ $item->pieza?->numero ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-brand-primary">{{ $item->diagnostico }}</td>
                            <td class="px-3 py-2 text-brand-primary">{{ $item->tratamiento }}</td>
                            <td class="px-3 py-2 text-right text-brand-primary">Q{{ number_format((float) $item->precio_unitario, 2) }}</td>
                            <td class="px-3 py-2 text-brand-primary">{{ $item->cantidad }}</td>
                            <td class="px-3 py-2 text-right text-brand-primary">Q{{ number_format((float) $item->subtotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-brand-muted">
                                Esta consulta no tiene ítems de presupuesto.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="pt-3 text-right text-brand-primary">Total</th>
                        <th class="pt-3 text-right text-brand-primary">
                            Q{{ number_format((float) $consulta->presupuesto_total, 2) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</x-card>

@if ($editable)
    <script>
        function presupuestoEditor(initial) {
            return {
                items: initial.items.map(i => ({ ...i, _guardando: false })),
                piezas: initial.piezas,
                tarifas: initial.tarifas.map(t => ({ ...t, precio_sugerido: Number(t.precio_sugerido) })),
                aceptadoEn: initial.aceptadoEn,
                endpoints: initial.endpoints,
                nuevo: { pieza_id: null, diagnostico: '', tratamiento: '', precio_unitario: 0, cantidad: 1 },
                guardandoNuevo: false,
                generandoOdontograma: false,
                aceptando: false,
                mensaje: { texto: '', tipo: 'success' },

                get totalEnVivo() {
                    return this.items.reduce((acc, i) => acc + (Number(i.precio_unitario) || 0) * (Number(i.cantidad) || 0), 0);
                },

                get nuevoValido() {
                    return this.nuevo.diagnostico.trim().length > 0
                        && this.nuevo.tratamiento.trim().length > 0
                        && Number(this.nuevo.precio_unitario) >= 0
                        && Number(this.nuevo.cantidad) >= 1;
                },

                itemValido(item) {
                    return (item.diagnostico || '').trim().length > 0
                        && (item.tratamiento || '').trim().length > 0
                        && Number(item.precio_unitario) >= 0
                        && Number(item.cantidad) >= 1;
                },

                formatQ(n) {
                    return 'Q' + (Number(n) || 0).toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                piezaNumero(piezaId) {
                    if (! piezaId) return null;
                    const p = this.piezas.find(p => p.id === Number(piezaId));
                    return p ? p.numero : null;
                },

                piezaNombre(piezaId) {
                    if (! piezaId) return null;
                    const p = this.piezas.find(p => p.id === Number(piezaId));
                    return p ? p.nombre : null;
                },

                actualizarEstadosPiezas(piezasFrescas) {
                    // Sincroniza los estados desde el odontograma sin perder el resto
                    // de campos (numero, nombre, cuadrante) ya cargados al renderizar.
                    if (! Array.isArray(piezasFrescas)) return;
                    const mapa = Object.fromEntries(piezasFrescas.map(p => [p.id, p.estado]));
                    this.piezas = this.piezas.map(p => ({
                        ...p,
                        estado_consulta: mapa[p.id] !== undefined ? mapa[p.id] : p.estado_consulta,
                    }));
                    // Si el usuario tiene una pieza ya seleccionada en el form de nueva línea
                    // y su estado cambió, reintenta el auto-completar (solo si los campos
                    // están vacíos — respeta lo que el usuario ya escribió).
                    this.autoCompletarDesdePieza();
                },

                autoCompletarDesdePieza() {
                    if (! this.nuevo.pieza_id) return;
                    const pieza = this.piezas.find(p => p.id === Number(this.nuevo.pieza_id));
                    if (! pieza || ! pieza.estado_consulta || pieza.estado_consulta === 'sana') return;
                    const tarifa = this.tarifas.find(t => t.estado_pieza === pieza.estado_consulta);
                    if (! tarifa) return;
                    if (! this.nuevo.diagnostico) this.nuevo.diagnostico = tarifa.nombre_legible;
                    if (! this.nuevo.tratamiento) this.nuevo.tratamiento = tarifa.nombre_legible;
                    if (! this.nuevo.precio_unitario) this.nuevo.precio_unitario = tarifa.precio_sugerido;
                },

                autoCompletarDesdeTratamiento(target) {
                    // Cuando el usuario elige un tratamiento del datalist (o lo escribe
                    // exactamente igual al nombre de una tarifa), trae el precio sugerido
                    // del catálogo solo si el campo precio sigue vacío o en cero.
                    const nombre = (target.tratamiento || '').trim();
                    if (! nombre) return;
                    const tarifa = this.tarifas.find(t => t.nombre_legible === nombre);
                    if (! tarifa) return;
                    const precioActual = Number(target.precio_unitario) || 0;
                    if (precioActual === 0) {
                        target.precio_unitario = tarifa.precio_sugerido;
                    }
                    if (! (target.diagnostico || '').trim()) {
                        target.diagnostico = tarifa.nombre_legible;
                    }
                },

                csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },

                showMensaje(texto, tipo = 'success') {
                    this.mensaje.texto = texto;
                    this.mensaje.tipo = tipo;
                    setTimeout(() => {
                        if (this.mensaje.texto === texto) this.mensaje.texto = '';
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
                        let msg = 'Error ' + res.status;
                        try {
                            const data = await res.json();
                            if (data.message) msg = data.message;
                            if (data.errors) msg = Object.values(data.errors).flat().join(' · ');
                        } catch (e) {}
                        throw new Error(msg);
                    }
                    return res.json();
                },

                async agregarItem() {
                    this.guardandoNuevo = true;
                    try {
                        const payload = {
                            pieza_id: this.nuevo.pieza_id || null,
                            diagnostico: this.nuevo.diagnostico.trim(),
                            tratamiento: this.nuevo.tratamiento.trim(),
                            precio_unitario: Number(this.nuevo.precio_unitario) || 0,
                            cantidad: Number(this.nuevo.cantidad) || 1,
                        };
                        const data = await this.fetchJson(this.endpoints.store, {
                            method: 'POST',
                            body: JSON.stringify(payload),
                        });
                        this.items.push(this.normalizarItem(data.item));
                        this.nuevo = { pieza_id: null, diagnostico: '', tratamiento: '', precio_unitario: 0, cantidad: 1 };
                        this.showMensaje('Ítem agregado.');
                    } catch (e) {
                        this.showMensaje(e.message, 'error');
                    } finally {
                        this.guardandoNuevo = false;
                    }
                },

                async guardarItem(idx) {
                    const item = this.items[idx];
                    item._guardando = true;
                    try {
                        const payload = {
                            pieza_id: item.pieza_id || null,
                            diagnostico: (item.diagnostico || '').trim(),
                            tratamiento: (item.tratamiento || '').trim(),
                            precio_unitario: Number(item.precio_unitario) || 0,
                            cantidad: Number(item.cantidad) || 1,
                        };
                        const data = await this.fetchJson(`${this.endpoints.update}/${item.id}`, {
                            method: 'PUT',
                            body: JSON.stringify(payload),
                        });
                        Object.assign(item, this.normalizarItem(data.item));
                        item._guardando = false;
                        this.showMensaje('Ítem actualizado.');
                    } catch (e) {
                        item._guardando = false;
                        this.showMensaje(e.message, 'error');
                    }
                },

                async eliminarItem(idx) {
                    const item = this.items[idx];
                    const ok = await window.confirmDialog({
                        title: '¿Eliminar línea del presupuesto?',
                        message: `Estás a punto de eliminar "${item.diagnostico || 'sin diagnóstico'}". Esta acción no se puede deshacer.`,
                        confirmText: 'Eliminar',
                        variant: 'danger',
                    });
                    if (! ok) return;
                    try {
                        await this.fetchJson(`${this.endpoints.destroy}/${item.id}`, { method: 'DELETE' });
                        this.items.splice(idx, 1);
                        this.showMensaje('Ítem eliminado.');
                    } catch (e) {
                        this.showMensaje(e.message, 'error');
                    }
                },

                async generarDesdeOdontograma() {
                    this.generandoOdontograma = true;
                    try {
                        const data = await this.fetchJson(this.endpoints.sugerencias);
                        const sugerencias = data.items || [];
                        if (sugerencias.length === 0) {
                            this.showMensaje('No hay piezas con estado distinto a "sana" en el odontograma.', 'error');
                            return;
                        }
                        let agregados = 0;
                        for (const sug of sugerencias) {
                            try {
                                const res = await this.fetchJson(this.endpoints.store, {
                                    method: 'POST',
                                    body: JSON.stringify({
                                        pieza_id: sug.pieza_id,
                                        diagnostico: sug.diagnostico,
                                        tratamiento: sug.tratamiento,
                                        precio_unitario: Number(sug.precio_unitario) || 0,
                                        cantidad: 1,
                                    }),
                                });
                                this.items.push(this.normalizarItem(res.item));
                                agregados++;
                            } catch (e) {
                                // continúa con el resto si una sugerencia falla
                            }
                        }
                        this.showMensaje(`${agregados} ítem(s) agregados desde el odontograma.`);
                    } catch (e) {
                        this.showMensaje(e.message, 'error');
                    } finally {
                        this.generandoOdontograma = false;
                    }
                },

                async marcarAceptado() {
                    const ok = await window.confirmDialog({
                        title: '¿Aceptar el presupuesto?',
                        message: 'Al aceptarlo, los ítems quedan congelados y ya no se podrán editar. Esta acción no se puede deshacer.',
                        confirmText: 'Aceptar presupuesto',
                        variant: 'warning',
                    });
                    if (! ok) return;
                    this.aceptando = true;
                    try {
                        const data = await this.fetchJson(this.endpoints.aceptar, { method: 'POST' });
                        if (data.presupuesto_aceptado_en) {
                            const d = new Date(data.presupuesto_aceptado_en);
                            this.aceptadoEn = d.toLocaleString('es-GT', { dateStyle: 'short', timeStyle: 'short' });
                        }
                        this.showMensaje('Presupuesto aceptado.');
                        setTimeout(() => location.reload(), 800);
                    } catch (e) {
                        this.showMensaje(e.message, 'error');
                    } finally {
                        this.aceptando = false;
                    }
                },

                normalizarItem(raw) {
                    return {
                        id: raw.id,
                        pieza_id: raw.pieza_id,
                        pieza_numero: raw.pieza?.numero ?? null,
                        diagnostico: raw.diagnostico,
                        tratamiento: raw.tratamiento,
                        precio_unitario: Number(raw.precio_unitario),
                        cantidad: Number(raw.cantidad),
                        subtotal: Number(raw.subtotal),
                        _guardando: false,
                    };
                },
            };
        }
    </script>
@endif
