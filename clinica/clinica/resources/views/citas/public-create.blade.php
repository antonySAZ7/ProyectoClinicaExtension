@php
    $brand = config('site.brand');
    $oldServicio = old('servicio_id');
    $oldFecha = old('fecha', $fechaSugerida);
    $oldHora = old('hora', '');
@endphp

<x-layouts.public
    title="Agendar cita | {{ $brand['legal_name'] }}"
    description="Agenda una cita en DENS32 desde el portal público."
>
    <section class="bg-[var(--brand-surface)] px-6 py-20 md:px-12">
        <div class="mx-auto max-w-5xl">

            <div class="mb-12 text-center">
                <p class="font-script text-4xl text-[var(--brand-muted)] md:text-5xl">Agenda tu cita</p>
                <h1 class="font-display text-5xl leading-none text-[var(--brand-primary)] md:text-6xl">
                    Reserva tu espacio en {{ $brand['name'] }}
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-base text-[var(--brand-muted)] md:text-lg">
                    Selecciona el servicio, la fecha y el horario que mejor te acomode. Te enviaremos
                    un correo de confirmación con todos los detalles.
                </p>
            </div>

            @guest
                <div class="mb-8 flex flex-wrap items-center justify-center gap-2 rounded-xl border border-[var(--brand-border)] bg-white px-5 py-3 text-center text-sm shadow-sm">
                    <span class="text-[var(--brand-muted)]">¿Ya tienes cuenta?</span>
                    <a href="{{ route('login') }}" class="font-semibold text-[var(--brand-primary)] underline-offset-2 hover:underline">
                        Inicia sesión aquí
                    </a>
                </div>
            @endguest

            @if (session('success'))
                <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 shadow-sm">
                    <p class="font-semibold">¡Listo!</p>
                    <p class="mt-1">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-8 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700 shadow-sm">
                    <p class="font-semibold">Revisa lo siguiente:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('public.citas.store') }}"
                class="space-y-10"
                x-data="agendamiento({
                    servicioInicial: {{ $oldServicio ? (int) $oldServicio : 'null' }},
                    fechaInicial: '{{ $oldFecha }}',
                    horaInicial: '{{ $oldHora }}',
                    endpoint: '{{ route('public.citas.disponibilidad') }}',
                })"
            >
                @csrf

                {{-- Paso 1 — Servicio --}}
                <section>
                    <header class="mb-5 flex items-baseline justify-between gap-3">
                        <h2 class="font-display text-3xl text-[var(--brand-primary)]">
                            1. Elige tu servicio
                            <span class="text-red-500" aria-hidden="true">*</span>
                        </h2>
                        <span class="text-xs uppercase tracking-[0.2em] text-[var(--brand-muted)]">
                            {{ $servicios->count() }} disponibles
                        </span>
                    </header>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($servicios as $servicio)
                            <label
                                class="cursor-pointer rounded-2xl border bg-white p-5 shadow-sm transition hover:border-[var(--brand-primary)] hover:shadow-md"
                                :class="servicioId === {{ $servicio->id }}
                                    ? 'border-[var(--brand-primary)] ring-2 ring-[var(--brand-primary)]/30'
                                    : 'border-[var(--brand-border)]'"
                            >
                                <input
                                    type="radio"
                                    name="servicio_id"
                                    value="{{ $servicio->id }}"
                                    class="sr-only"
                                    x-model.number="servicioId"
                                    @change="onChangeBase"
                                >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-display text-2xl text-[var(--brand-primary)]">{{ $servicio->nombre }}</h3>
                                        @if ($servicio->descripcion)
                                            <p class="mt-2 text-sm text-[var(--brand-muted)]">{{ $servicio->descripcion }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 rounded-full bg-[var(--brand-soft)] px-3 py-1 text-xs font-semibold text-[var(--brand-primary)]">
                                        {{ $servicio->duracion_minutos }} min
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- Paso 2 — Fecha y hora --}}
                <section x-show="servicioId" x-cloak x-transition>
                    <header class="mb-5">
                        <h2 class="font-display text-3xl text-[var(--brand-primary)]">
                            2. Selecciona fecha y horario
                            <span class="text-red-500" aria-hidden="true">*</span>
                        </h2>
                    </header>

                    <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
                        <div>
                            <label for="fecha" class="mb-2 block text-sm font-semibold text-[var(--brand-primary)]">Fecha</label>
                            <input
                                id="fecha"
                                type="date"
                                name="fecha"
                                x-model="fecha"
                                @change="onChangeBase"
                                min="{{ now()->toDateString() }}"
                                required
                                class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]"
                            >
                            <p class="mt-3 text-xs text-[var(--brand-muted)]" x-show="servicioNombre" x-cloak>
                                Servicio:
                                <span class="font-semibold text-[var(--brand-primary)]" x-text="servicioNombre"></span>
                            </p>
                        </div>

                        <div>
                            <p class="mb-2 text-sm font-semibold text-[var(--brand-primary)]">Horarios disponibles</p>

                            <div x-show="cargando" x-cloak
                                class="rounded-2xl border border-[var(--brand-border)] bg-white px-5 py-8 text-center text-sm text-[var(--brand-muted)]">
                                Cargando horarios...
                            </div>

                            <div x-show="! cargando && bloques.length === 0" x-cloak
                                class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-6 text-sm text-amber-800">
                                La clínica no atiende ese día. Prueba con otra fecha.
                            </div>

                            <div x-show="! cargando && bloques.length > 0" x-cloak class="space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-center text-xs text-[var(--brand-muted)]" style="gap: 4px 16px;">
                                        <span class="inline-flex items-center" style="gap: 6px;">
                                            <span style="display:inline-block; width:10px; height:10px; border-radius:2px; background:#10b981;"></span>
                                            <span>Disponible</span>
                                        </span>
                                        <span class="inline-flex items-center" style="gap: 6px;">
                                            <span style="display:inline-block; width:10px; height:10px; border-radius:2px; background:#fecdd3;"></span>
                                            <span>Ocupado</span>
                                        </span>
                                        <span class="inline-flex items-center" style="gap: 6px;" x-show="hayBloquesPasados">
                                            <span style="display:inline-block; width:10px; height:10px; border-radius:2px; background:#e5e7eb;"></span>
                                            <span>Ya pasó</span>
                                        </span>
                                    </div>

                                    <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-[var(--brand-muted)]">
                                        <input type="checkbox" x-model="soloDisponibles" class="rounded border-[var(--brand-border)] text-emerald-600 focus:ring-emerald-500">
                                        Solo mostrar disponibles
                                    </label>
                                </div>

                                <div class="max-h-[460px] space-y-2.5 overflow-y-auto pr-1">
                                    <template x-for="bloque in bloquesFiltrados" :key="bloque.hora">
                                        <button
                                            type="button"
                                            :disabled="! bloque.disponible"
                                            @click="bloque.disponible && (hora = bloque.hora)"
                                            :class="(() => {
                                                const sel = hora === bloque.hora;
                                                const base = 'flex w-full items-center justify-between gap-3 rounded-lg border px-4 py-2 text-left text-sm transition';
                                                if (! bloque.disponible) {
                                                    return base + (bloque.motivo === 'pasado'
                                                        ? ' cursor-not-allowed border-gray-200 bg-gray-50 text-gray-400'
                                                        : ' cursor-not-allowed border-rose-200 bg-rose-50 text-rose-600');
                                                }
                                                if (sel) return base + ' border-[var(--brand-primary)] bg-[var(--brand-primary)] text-[var(--brand-contrast)] shadow-sm';
                                                return base + ' border-emerald-300 bg-emerald-50 text-emerald-800 hover:border-emerald-600 hover:bg-emerald-100';
                                            })()"
                                        >
                                            <span class="font-semibold tabular-nums"
                                                x-text="bloque.hora + ' – ' + bloque.hora_fin"
                                            ></span>

                                            <span class="flex items-center gap-2">
                                                <span class="text-xs opacity-90"
                                                    x-text="bloque.disponible
                                                        ? (hora === bloque.hora ? 'Seleccionado' : 'Disponible')
                                                        : (bloque.motivo === 'pasado' ? 'Ya pasó' : 'Ocupado')"
                                                ></span>
                                                <svg x-show="hora === bloque.hora" x-cloak
                                                    class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0l-3.5-3.5a1 1 0 011.42-1.42l2.79 2.79 6.79-6.79a1 1 0 011.42 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        </button>
                                    </template>
                                </div>

                                <p class="text-xs text-[var(--brand-muted)]">
                                    Mostrando <span class="font-semibold text-[var(--brand-primary)]" x-text="bloquesFiltrados.length"></span>
                                    de <span x-text="bloques.length"></span> bloques
                                </p>
                            </div>

                            <input type="hidden" name="hora" :value="hora">
                        </div>
                    </div>
                </section>

                {{-- Paso 3 — Datos del paciente --}}
                @if ($needsPacienteData)
                    <section x-show="servicioId && hora" x-cloak x-transition>
                        <header class="mb-5">
                            <h2 class="font-display text-3xl text-[var(--brand-primary)]">3. Cuéntanos quién eres</h2>
                            <p class="mt-1 text-sm text-[var(--brand-muted)]">
                                Necesitamos tus datos para crear tu expediente.
                            </p>
                        </header>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="nombre_completo" class="mb-1 block text-sm font-medium text-[var(--brand-primary)]">
                                    Nombre completo <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="nombre_completo" name="nombre_completo" type="text"
                                    value="{{ old('nombre_completo') }}" required maxlength="255"
                                    class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                            </div>

                            <div>
                                <label for="correo" class="mb-1 block text-sm font-medium text-[var(--brand-primary)]">
                                    Correo electrónico <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="correo" name="correo" type="email"
                                    value="{{ old('correo') }}" required maxlength="255"
                                    class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                            </div>

                            <div>
                                <label for="telefono" class="mb-1 block text-sm font-medium text-[var(--brand-primary)]">
                                    Teléfono <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="telefono" name="telefono" type="tel"
                                    value="{{ old('telefono') }}" required maxlength="20"
                                    placeholder="+502 XXXX-XXXX"
                                    class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                            </div>

                            <div>
                                <label for="dpi" class="mb-1 block text-sm font-medium text-[var(--brand-primary)]">
                                    DPI <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="dpi" name="dpi" type="text"
                                    value="{{ old('dpi') }}" required maxlength="20"
                                    class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                            </div>

                            <div>
                                <label for="fecha_nacimiento" class="mb-1 block text-sm font-medium text-[var(--brand-primary)]">
                                    Fecha de nacimiento <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                                    value="{{ old('fecha_nacimiento') }}" required
                                    class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="direccion" class="mb-1 block text-sm font-medium text-[var(--brand-primary)]">
                                    Dirección <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="direccion" name="direccion" type="text"
                                    value="{{ old('direccion') }}" required maxlength="255"
                                    class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                            </div>

                            <div class="sm:col-span-2 mt-2 rounded-xl border border-[var(--brand-border)] bg-[var(--brand-soft)]/40 p-4">
                                <p class="text-sm font-semibold text-[var(--brand-primary)]">Crea tu contraseña</p>
                                <p class="mt-1 text-xs text-[var(--brand-muted)]">
                                    La necesitarás para ver y gestionar tus citas en el portal.
                                </p>
                            </div>

                            <div>
                                <label for="password" class="mb-1 block text-sm font-medium text-[var(--brand-primary)]">
                                    Contraseña <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                                    class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                                <p class="mt-1 text-xs text-[var(--brand-muted)]">Mínimo 8 caracteres.</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-[var(--brand-primary)]">
                                    Confirmar contraseña <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                                    class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                            </div>
                        </div>
                    </section>
                @else
                    <section x-show="servicioId && hora" x-cloak x-transition>
                        <div class="rounded-2xl border border-[var(--brand-border)] bg-white p-5 shadow-sm">
                            <p class="text-xs uppercase tracking-[0.2em] text-[var(--brand-muted)]">Agendarás como</p>
                            <p class="mt-2 font-display text-2xl text-[var(--brand-primary)]">
                                {{ $user?->paciente?->nombre_completo ?? $user?->name }}
                            </p>
                            <p class="mt-1 text-sm text-[var(--brand-muted)]">
                                {{ $user?->paciente?->correo ?? $user?->email }}
                            </p>
                        </div>
                    </section>
                @endif

                {{-- Motivo opcional --}}
                <section x-show="servicioId && hora" x-cloak x-transition>
                    <label for="motivo" class="mb-1 block text-sm font-medium text-[var(--brand-primary)]">
                        Motivo o nota adicional
                        <span class="text-xs font-normal text-[var(--brand-muted)]">(opcional)</span>
                    </label>
                    <input id="motivo" name="motivo" type="text"
                        value="{{ old('motivo') }}" maxlength="255"
                        placeholder="Ej. control general, dolor de muela, limpieza"
                        class="block w-full rounded-md border-[var(--brand-border)] shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                </section>

                <p class="text-xs text-[var(--brand-muted)]">
                    <span class="text-red-500">*</span> Campos obligatorios
                </p>

                <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('landing') }}"
                        class="inline-flex items-center justify-center rounded-md border border-[var(--brand-border)] bg-white px-6 py-3 text-sm font-semibold text-[var(--brand-primary)] transition hover:bg-[var(--brand-soft)]">
                        Volver al inicio
                    </a>
                    <button type="submit"
                        :disabled="! servicioId || ! hora"
                        :class="(! servicioId || ! hora) ? 'cursor-not-allowed opacity-50' : ''"
                        class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-6 py-3 text-sm font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-primary-strong)]">
                        Confirmar cita
                    </button>
                </div>
            </form>
        </div>
    </section>

    <script>
        function agendamiento(config) {
            return {
                servicioId: config.servicioInicial,
                fecha: config.fechaInicial,
                hora: config.horaInicial || '',
                endpoint: config.endpoint,
                bloques: [],
                cargando: false,
                servicioNombre: '',
                soloDisponibles: false,
                get hayBloquesPasados() {
                    return this.bloques.some(b => b.motivo === 'pasado');
                },
                get bloquesFiltrados() {
                    return this.soloDisponibles ? this.bloques.filter(b => b.disponible) : this.bloques;
                },
                altoBloque(bloque) {
                    const desde = this.toMinutes(bloque.hora);
                    const hasta = this.toMinutes(bloque.hora_fin);
                    const minutos = Math.max(15, hasta - desde);
                    return Math.min(140, 40 + minutos * 1.1);
                },
                toMinutes(hhmm) {
                    const [h, m] = hhmm.split(':').map(Number);
                    return h * 60 + m;
                },

                init() {
                    this.$watch('servicioId', () => this.cargarBloques());
                    this.$watch('fecha', () => this.cargarBloques());
                    if (this.servicioId && this.fecha) {
                        this.cargarBloques();
                    }
                },

                onChangeBase() {
                    this.hora = '';
                    this.cargarBloques();
                },

                async cargarBloques() {
                    if (! this.servicioId || ! this.fecha) {
                        this.bloques = [];
                        return;
                    }
                    this.cargando = true;
                    try {
                        const url = `${this.endpoint}?fecha=${this.fecha}&servicio_id=${this.servicioId}`;
                        const res = await fetch(url, { headers: { Accept: 'application/json' } });
                        if (! res.ok) throw new Error('Respuesta no válida');
                        const data = await res.json();
                        this.bloques = data.bloques || [];
                        this.servicioNombre = data.servicio?.nombre || '';
                    } catch (e) {
                        this.bloques = [];
                    } finally {
                        this.cargando = false;
                    }
                },
            };
        }
    </script>
</x-layouts.public>
