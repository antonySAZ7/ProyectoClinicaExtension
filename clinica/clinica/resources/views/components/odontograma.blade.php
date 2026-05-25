@props([
    'consultaId',
    'viewOnly' => false,
])

<div
    x-data="odontograma({
        consultaId: {{ $consultaId }},
        readonly: @json((bool) $viewOnly),
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

    {{-- Vista para paciente (solo lectura, solo piezas con registro) --}}
    @if ($viewOnly)
        <div x-show="piezas.length > 0" x-cloak class="space-y-4">
            <template x-if="resumenPiezas.length === 0">
                <div class="rounded-xl border border-[var(--brand-border)] bg-white px-6 py-10 text-center">
                    <p class="text-base font-medium text-[var(--brand-primary)]">No hay registros dentales en esta consulta.</p>
                    <p class="mt-1 text-sm text-[var(--brand-muted)]">El doctor no marcó ningún hallazgo o tratamiento en tus piezas dentales.</p>
                </div>
            </template>

            <template x-if="resumenPiezas.length > 0">
                <ul class="space-y-3">
                    <template x-for="pieza in resumenPiezas" :key="pieza.id">
                        <li class="overflow-hidden rounded-xl border border-[var(--brand-border)] bg-white shadow-sm">
                            <div class="flex items-stretch gap-0">
                                <span :style="`display:block; width:8px; flex-shrink:0; background:${estadosMeta[pieza.estado].color};`"></span>

                                <div class="flex-1 px-5 py-4">
                                    <p class="font-display text-xl text-[var(--brand-primary)]"
                                        x-text="nombreCompleto(pieza)"
                                    ></p>

                                    <p class="mt-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                                        :style="`background:${estadosMeta[pieza.estado].color}; color:${textoSobreColor(estadosMeta[pieza.estado].color)};`"
                                    >
                                        <span x-text="estadosMeta[pieza.estado].label"></span>
                                    </p>

                                    <div x-show="pieza.observaciones" x-cloak class="mt-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-[var(--brand-muted)]">Notas del doctor</p>
                                        <p class="mt-1 whitespace-pre-line text-sm text-[var(--brand-primary)]"
                                            x-text="pieza.observaciones"
                                        ></p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
            </template>
        </div>
    @else

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

        @unless ($viewOnly)
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
    @endif

    {{-- Drawer (teleport al body para evitar conflictos con padres transform/overflow) --}}
    @unless ($viewOnly)
    <template x-teleport="body">
        <div x-show="drawerAbierto" style="position: fixed; inset: 0; z-index: 9999;">
            {{-- Backdrop --}}
            <div
                @click="cerrarDrawer()"
                style="position: absolute; inset: 0; background: rgba(0,0,0,0.35);"
            ></div>

            {{-- Drawer lateral --}}
            <aside
                @click.stop
                style="position: absolute; top: 0; right: 0; bottom: 0; width: 100%; max-width: 28rem; background: white; box-shadow: -10px 0 30px rgba(0,0,0,0.15); display: flex; flex-direction: column;"
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
        </div>
    </template>
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

            nombreCompleto(pieza) {
                const ubicaciones = {
                    1: 'superior derecho',
                    2: 'superior izquierdo',
                    3: 'inferior izquierdo',
                    4: 'inferior derecho',
                };
                const ubicacion = ubicaciones[pieza.cuadrante] || '';
                return `${pieza.nombre} ${ubicacion}`.trim();
            },

            textoSobreColor(hex) {
                if (! hex) return '#1f2937';
                const c = hex.replace('#', '');
                const r = parseInt(c.substr(0, 2), 16);
                const g = parseInt(c.substr(2, 2), 16);
                const b = parseInt(c.substr(4, 2), 16);
                const luminancia = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                return luminancia > 0.6 ? '#1f2937' : '#ffffff';
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
