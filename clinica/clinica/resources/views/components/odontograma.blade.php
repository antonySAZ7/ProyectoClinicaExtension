@props([
    'consultaId',
    'readonly' => false,
])

<div
    x-data="odontograma({
        consultaId: {{ $consultaId }},
        readonly: @json((bool) $readonly),
        baseUrl: '{{ url('/consultas/'.$consultaId.'/odontograma') }}',
        csrfToken: document.querySelector('meta[name=\'csrf-token\']')?.content || '',
    })"
    x-init="cargar()"
    class="relative"
>
    {{-- Loader inicial --}}
    <div x-show="cargando && piezas.length === 0" x-cloak
        class="rounded-xl border border-[var(--brand-border)] bg-white px-6 py-12 text-center text-sm text-[var(--brand-muted)]">
        Cargando odontograma...
    </div>

    <div x-show="piezas.length > 0" x-cloak class="space-y-6">

        {{-- Leyenda --}}
        <div class="flex flex-wrap items-center" style="gap: 8px 14px;">
            <template x-for="(meta, key) in estadosMeta" :key="key">
                <span class="inline-flex items-center text-xs text-[var(--brand-muted)]" style="gap: 6px;">
                    <span :style="`display:inline-block; width:14px; height:14px; border-radius:3px; background:${meta.color}; border:1px solid rgba(0,0,0,0.1);`"></span>
                    <span x-text="meta.label"></span>
                </span>
            </template>
        </div>

        {{-- Tabla dental --}}
        <div class="rounded-xl border border-[var(--brand-border)] bg-white p-4 sm:p-6">
            <p class="mb-1 text-xs uppercase tracking-[0.18em] text-[var(--brand-muted)]">Superior</p>
            <div class="grid grid-cols-2 gap-x-3 sm:gap-x-6">
                {{-- Q1: 18 → 11 (derecha del paciente) --}}
                <div class="flex flex-row-reverse justify-end gap-2">
                    <template x-for="pieza in cuadrante(1)" :key="pieza.id">
                        <button type="button"
                            @click="seleccionar(pieza)"
                            :title="`Pieza ${pieza.numero} — ${estadosMeta[pieza.estado].label}`"
                            :style="`background:${estadosMeta[pieza.estado].color};`"
                            :class="claseBoton(pieza)"
                        >
                            <span x-text="pieza.numero" class="relative z-10"></span>
                            <span x-show="pieza.estado === 'ausente'" class="absolute inset-0 flex items-center justify-center text-xl text-gray-500">✕</span>
                        </button>
                    </template>
                </div>

                {{-- Q2: 21 → 28 (izquierda del paciente) --}}
                <div class="flex flex-row justify-start gap-2">
                    <template x-for="pieza in cuadrante(2)" :key="pieza.id">
                        <button type="button"
                            @click="seleccionar(pieza)"
                            :title="`Pieza ${pieza.numero} — ${estadosMeta[pieza.estado].label}`"
                            :style="`background:${estadosMeta[pieza.estado].color};`"
                            :class="claseBoton(pieza)"
                        >
                            <span x-text="pieza.numero" class="relative z-10"></span>
                            <span x-show="pieza.estado === 'ausente'" class="absolute inset-0 flex items-center justify-center text-xl text-gray-500">✕</span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="my-4 border-t border-dashed border-[var(--brand-border)]"></div>

            <p class="mb-1 text-xs uppercase tracking-[0.18em] text-[var(--brand-muted)]">Inferior</p>
            <div class="grid grid-cols-2 gap-x-3 sm:gap-x-6">
                {{-- Q4: 48 → 41 (derecha del paciente) --}}
                <div class="flex flex-row-reverse justify-end gap-2">
                    <template x-for="pieza in cuadrante(4)" :key="pieza.id">
                        <button type="button"
                            @click="seleccionar(pieza)"
                            :title="`Pieza ${pieza.numero} — ${estadosMeta[pieza.estado].label}`"
                            :style="`background:${estadosMeta[pieza.estado].color};`"
                            :class="claseBoton(pieza)"
                        >
                            <span x-text="pieza.numero" class="relative z-10"></span>
                            <span x-show="pieza.estado === 'ausente'" class="absolute inset-0 flex items-center justify-center text-xl text-gray-500">✕</span>
                        </button>
                    </template>
                </div>

                {{-- Q3: 31 → 38 (izquierda del paciente) --}}
                <div class="flex flex-row justify-start gap-2">
                    <template x-for="pieza in cuadrante(3)" :key="pieza.id">
                        <button type="button"
                            @click="seleccionar(pieza)"
                            :title="`Pieza ${pieza.numero} — ${estadosMeta[pieza.estado].label}`"
                            :style="`background:${estadosMeta[pieza.estado].color};`"
                            :class="claseBoton(pieza)"
                        >
                            <span x-text="pieza.numero" class="relative z-10"></span>
                            <span x-show="pieza.estado === 'ausente'" class="absolute inset-0 flex items-center justify-center text-xl text-gray-500">✕</span>
                        </button>
                    </template>
                </div>
            </div>

            <p class="mt-4 text-center text-[10px] uppercase tracking-widest text-[var(--brand-muted)]">
                Derecha del paciente &nbsp; · &nbsp; Izquierda del paciente
            </p>
        </div>

        @unless ($readonly)
            <p class="text-xs text-[var(--brand-muted)]">
                Click sobre una pieza para registrar su estado.
            </p>
        @endunless

        {{-- Resumen de piezas con tratamiento --}}
        <div x-show="resumenPiezas.length > 0" x-cloak
            class="rounded-xl border border-[var(--brand-border)] bg-[var(--brand-soft)]/40 p-4">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-[var(--brand-muted)]">
                Piezas con registro (<span x-text="resumenPiezas.length"></span>)
            </p>
            <ul class="space-y-1.5">
                <template x-for="pieza in resumenPiezas" :key="pieza.id">
                    <li class="flex items-start gap-2 text-sm">
                        <span :style="`display:inline-block; width:12px; height:12px; border-radius:3px; background:${estadosMeta[pieza.estado].color}; margin-top:4px;`"></span>
                        <span>
                            <span class="font-semibold tabular-nums" x-text="pieza.numero"></span>
                            <span class="text-[var(--brand-muted)]"> — </span>
                            <span x-text="estadosMeta[pieza.estado].label"></span>
                            <span x-show="pieza.observaciones" class="text-xs text-[var(--brand-muted)]">
                                · <span x-text="pieza.observaciones"></span>
                            </span>
                        </span>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    {{-- Backdrop drawer --}}
    @unless ($readonly)
        <div
            x-show="drawerAbierto"
            x-cloak
            x-transition.opacity
            @click="cerrarDrawer()"
            class="fixed inset-0 z-40 bg-black/30"
        ></div>

        {{-- Drawer lateral --}}
        <aside
            x-show="drawerAbierto"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col bg-white shadow-2xl"
        >
            <header class="flex items-start justify-between gap-3 border-b border-[var(--brand-border)] px-6 py-5">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-[var(--brand-muted)]">Pieza dental</p>
                    <p class="mt-1 font-display text-3xl text-[var(--brand-primary)]" x-text="selected?.numero ?? ''"></p>
                    <p class="mt-1 text-sm text-[var(--brand-muted)]" x-text="selected ? `Cuadrante ${selected.cuadrante} · Posición ${selected.posicion}` : ''"></p>
                </div>
                <button type="button" @click="cerrarDrawer()" class="rounded-full p-1.5 text-[var(--brand-muted)] hover:bg-[var(--brand-soft)] hover:text-[var(--brand-primary)]" aria-label="Cerrar">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                    </svg>
                </button>
            </header>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <label class="mb-2 block text-sm font-medium text-[var(--brand-primary)]">Estado clínico</label>
                <div class="grid grid-cols-2 gap-2">
                    <template x-for="(meta, key) in estadosMeta" :key="key">
                        <button type="button"
                            @click="estadoEdit = key"
                            :class="estadoEdit === key
                                ? 'border-[var(--brand-primary)] ring-2 ring-[var(--brand-primary)]/30'
                                : 'border-[var(--brand-border)] hover:border-[var(--brand-primary)]'"
                            class="flex items-center gap-2 rounded-lg border bg-white px-3 py-2 text-left text-sm transition"
                        >
                            <span :style="`display:inline-block; width:16px; height:16px; border-radius:3px; background:${meta.color}; border:1px solid rgba(0,0,0,0.1); flex-shrink:0;`"></span>
                            <span x-text="meta.label" class="text-[var(--brand-primary)]"></span>
                        </button>
                    </template>
                </div>

                <label for="odontograma-obs" class="mt-6 mb-2 block text-sm font-medium text-[var(--brand-primary)]">
                    Observaciones <span class="text-xs font-normal text-[var(--brand-muted)]">(opcional)</span>
                </label>
                <textarea id="odontograma-obs" x-model="observacionesEdit" rows="5" maxlength="1000"
                    placeholder="Detalles del hallazgo o tratamiento..."
                    class="block w-full rounded-md border-[var(--brand-border)] text-sm shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]"></textarea>
                <p class="mt-1 text-right text-[11px] text-[var(--brand-muted)]">
                    <span x-text="observacionesEdit?.length ?? 0"></span>/1000
                </p>

                <div x-show="error" x-cloak
                    class="mt-4 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"
                    x-text="error"></div>
            </div>

            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--brand-border)] bg-[var(--brand-soft)]/40 px-6 py-4">
                <button type="button" @click="reset()" :disabled="guardando"
                    class="text-sm font-medium text-[var(--brand-muted)] hover:text-rose-600 disabled:opacity-50">
                    Quitar registro
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="cerrarDrawer()" :disabled="guardando"
                        class="rounded-md border border-[var(--brand-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--brand-primary)] hover:bg-[var(--brand-soft)] disabled:opacity-50">
                        Cancelar
                    </button>
                    <button type="button" @click="guardar()" :disabled="guardando"
                        class="rounded-md bg-[var(--brand-primary)] px-4 py-2 text-sm font-semibold text-[var(--brand-contrast)] hover:bg-[var(--brand-primary-strong)] disabled:opacity-50">
                        <span x-show="! guardando">Guardar</span>
                        <span x-show="guardando" x-cloak>Guardando...</span>
                    </button>
                </div>
            </footer>
        </aside>
    @endunless
</div>

<script>
    function odontograma(config) {
        return {
            consultaId: config.consultaId,
            esReadonly: config.readonly,
            baseUrl: config.baseUrl,
            csrfToken: config.csrfToken,
            piezas: [],
            selected: null,
            estadoEdit: 'sana',
            observacionesEdit: '',
            drawerAbierto: false,
            cargando: true,
            guardando: false,
            error: null,
            estadosMeta: {
                sana:       { label: 'Sana',       color: '#f8fafc' },
                caries:     { label: 'Caries',     color: '#fb923c' },
                obturada:   { label: 'Obturada',   color: '#3b82f6' },
                ausente:    { label: 'Ausente',    color: '#d1d5db' },
                extraccion: { label: 'Extracción', color: '#ef4444' },
                corona:     { label: 'Corona',     color: '#facc15' },
                endodoncia: { label: 'Endodoncia', color: '#a855f7' },
            },

            cuadrante(n) {
                return this.piezas
                    .filter(p => p.cuadrante === n)
                    .sort((a, b) => a.posicion - b.posicion);
            },

            get resumenPiezas() {
                return this.piezas
                    .filter(p => p.estado !== 'sana')
                    .sort((a, b) => a.numero - b.numero);
            },

            async cargar() {
                this.cargando = true;
                try {
                    const res = await fetch(this.baseUrl, { headers: { Accept: 'application/json' } });
                    if (! res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();
                    this.piezas = data.piezas || [];
                } catch (e) {
                    console.error('Error cargando odontograma:', e);
                } finally {
                    this.cargando = false;
                }
            },

            seleccionar(pieza) {
                if (this.esReadonly) return;
                this.selected = pieza;
                this.estadoEdit = pieza.estado;
                this.observacionesEdit = pieza.observaciones || '';
                this.error = null;
                this.drawerAbierto = true;
            },

            claseBoton(pieza) {
                const sel = this.selected?.id === pieza.id;
                const base = 'group relative h-16 w-11 sm:h-20 sm:w-14 rounded-md border border-gray-300 text-sm font-semibold text-gray-800 transition';
                if (this.esReadonly) return base + ' cursor-default';
                return base + ' hover:scale-105 hover:shadow' + (sel ? ' ring-2 ring-[var(--brand-primary)] scale-105' : '');
            },

            cerrarDrawer() {
                this.drawerAbierto = false;
                this.selected = null;
                this.error = null;
            },

            async guardar() {
                if (! this.selected || this.guardando) return;
                this.guardando = true;
                this.error = null;

                try {
                    const res = await fetch(`${this.baseUrl}/${this.selected.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                        body: JSON.stringify({
                            estado: this.estadoEdit,
                            observaciones: this.observacionesEdit || null,
                        }),
                    });

                    if (! res.ok) {
                        const body = await res.json().catch(() => ({}));
                        throw new Error(body.message || 'No se pudo guardar el registro.');
                    }

                    const data = await res.json();
                    this.piezas = data.piezas || [];
                    this.cerrarDrawer();
                } catch (e) {
                    this.error = e.message || 'Ocurrió un error al guardar.';
                } finally {
                    this.guardando = false;
                }
            },

            async reset() {
                if (! this.selected || this.guardando) return;
                if (! confirm('¿Quitar el registro de esta pieza? Volverá al estado "sana".')) return;
                this.guardando = true;
                this.error = null;

                try {
                    const res = await fetch(`${this.baseUrl}/${this.selected.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                    });

                    if (! res.ok) throw new Error('No se pudo quitar el registro.');

                    await this.cargar();
                    this.cerrarDrawer();
                } catch (e) {
                    this.error = e.message || 'Ocurrió un error al eliminar.';
                } finally {
                    this.guardando = false;
                }
            },
        };
    }
</script>
