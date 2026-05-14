@php
    $brand = config('site.brand');
@endphp

<x-layouts.public
    title="Agendar cita | {{ $brand['legal_name'] }}"
    description="Agenda una cita en DENS32 desde el portal publico."
>
    <section class="px-6 pb-20 pt-32 md:px-12">
        <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div>
                <p class="font-script text-5xl text-[var(--brand-muted)]">Agenda tu cita</p>
                <h1 class="mt-3 font-display text-6xl leading-none text-[var(--brand-primary)]">
                    DENS32
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-[var(--brand-muted)]">
                    Selecciona el servicio, fecha y hora disponible. Te enviaremos confirmacion por correo y WhatsApp.
                </p>
            </div>

            <div class="rounded-2xl border border-[var(--brand-border)] bg-white p-6 shadow-sm sm:p-8">
                @if (session('success'))
                    <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.citas.store') }}" class="space-y-6">
                    @csrf

                    @if ($needsPacienteData)
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="nombre_completo" class="mb-2 block text-sm font-medium text-gray-700">Nombre completo</label>
                                <input id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            </div>
                            <div>
                                <label for="correo" class="mb-2 block text-sm font-medium text-gray-700">Correo</label>
                                <input id="correo" type="email" name="correo" value="{{ old('correo') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            </div>
                            <div>
                                <label for="telefono" class="mb-2 block text-sm font-medium text-gray-700">WhatsApp</label>
                                <input id="telefono" name="telefono" value="{{ old('telefono') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            </div>
                            <div>
                                <label for="dpi" class="mb-2 block text-sm font-medium text-gray-700">DPI</label>
                                <input id="dpi" name="dpi" value="{{ old('dpi') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            </div>
                            <div>
                                <label for="fecha_nacimiento" class="mb-2 block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
                                <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="direccion" class="mb-2 block text-sm font-medium text-gray-700">Direccion</label>
                                <input id="direccion" name="direccion" value="{{ old('direccion') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="servicio_id" class="mb-2 block text-sm font-medium text-gray-700">Servicio</label>
                            <select id="servicio_id" name="servicio_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                                <option value="">Selecciona un servicio</option>
                                @foreach ($servicios as $servicio)
                                    <option value="{{ $servicio->id }}" data-duration="{{ $servicio->duracion_minutos }}" @selected((string) old('servicio_id') === (string) $servicio->id)>
                                        {{ $servicio->nombre }} ({{ $servicio->duracion_minutos }} min)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fecha" class="mb-2 block text-sm font-medium text-gray-700">Fecha</label>
                            <input id="fecha" type="date" name="fecha" value="{{ old('fecha') }}" min="{{ now()->toDateString() }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                        </div>

                        <div>
                            <label for="hora" class="mb-2 block text-sm font-medium text-gray-700">Hora disponible</label>
                            <select id="hora" name="hora" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                                <option value="">Selecciona fecha y servicio</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="motivo" class="mb-2 block text-sm font-medium text-gray-700">Motivo adicional</label>
                        <textarea id="motivo" name="motivo" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('motivo') }}</textarea>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-[var(--brand-primary)] px-5 py-3 text-sm font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-primary-strong)]">
                        Solicitar cita
                    </button>
                </form>
            </div>
        </div>
    </section>

    <script>
        (function () {
            const fecha = document.getElementById('fecha');
            const servicio = document.getElementById('servicio_id');
            const hora = document.getElementById('hora');
            if (!fecha || !servicio || !hora) return;

            async function loadSlots() {
                hora.innerHTML = '<option value="">Cargando horarios...</option>';

                if (!fecha.value || !servicio.value) {
                    hora.innerHTML = '<option value="">Selecciona fecha y servicio</option>';
                    return;
                }

                const params = new URLSearchParams({ fecha: fecha.value, servicio_id: servicio.value });
                const response = await fetch('/api/disponibilidad?' + params.toString(), { headers: { Accept: 'application/json' } });
                const data = await response.json();

                hora.innerHTML = '';

                if (!data.bloques || data.bloques.length === 0) {
                    hora.innerHTML = '<option value="">No hay horarios disponibles</option>';
                    return;
                }

                data.bloques.forEach((slot) => {
                    const option = document.createElement('option');
                    option.value = slot.hora;
                    option.textContent = slot.label;
                    hora.appendChild(option);
                });
            }

            fecha.addEventListener('change', loadSlots);
            servicio.addEventListener('change', loadSlots);
            loadSlots();
        })();
    </script>
</x-layouts.public>
