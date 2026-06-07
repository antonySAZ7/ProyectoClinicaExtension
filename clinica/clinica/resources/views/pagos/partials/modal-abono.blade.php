@props([
    'paciente',
    'saldoPendiente',
    'consultas' => [],
    'consultaPreasignada' => null,
    'modalName' => 'registrar-abono',
])

<x-modal :name="$modalName" focusable>
    <div
        class="p-6"
        x-data="modalAbono({
            endpoint: '{{ route('pacientes.pagos.store', $paciente) }}',
            saldoPendiente: {{ (float) $saldoPendiente }},
            consultas: @js($consultas),
            consultaPreasignada: {{ $consultaPreasignada ? (int) $consultaPreasignada : 'null' }},
            modalName: '{{ $modalName }}',
        })"
        x-on:open-modal.window="$event.detail === '{{ $modalName }}' && resetForm()"
    >
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-lg font-semibold text-brand-primary">Registrar abono</h2>
                <p class="mt-1 text-sm text-brand-muted">
                    Paciente: <span class="font-medium">{{ $paciente->nombre_completo }}</span>
                </p>
                <p class="text-xs text-brand-muted">
                    Saldo pendiente actual:
                    <span class="font-semibold text-brand-primary">Q{{ number_format((float) $saldoPendiente, 2) }}</span>
                </p>
            </div>

            <button
                type="button"
                class="text-brand-muted hover:text-brand-primary"
                @click="$dispatch('close-modal', '{{ $modalName }}')"
                aria-label="Cerrar"
            >
                <x-lucide-x class="h-5 w-5" />
            </button>
        </div>

        <template x-if="mensaje.texto">
            <div
                class="mt-4 rounded-md border px-4 py-2 text-sm"
                :class="mensaje.tipo === 'error'
                    ? 'border-rose-200 bg-rose-50 text-rose-700'
                    : 'border-emerald-200 bg-emerald-50 text-emerald-700'"
                x-text="mensaje.texto"
            ></div>
        </template>

        <form @submit.prevent="registrar()" class="mt-5 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="abono-monto" class="block text-sm font-medium text-brand-muted">
                        Monto (Q) <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="abono-monto"
                        type="number"
                        step="0.01"
                        min="0.01"
                        :max="saldoPendiente"
                        class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        x-model.number="form.monto"
                        required
                    >
                    <p class="mt-1 text-xs text-brand-muted">
                        Máximo: Q<span x-text="formatQ(saldoPendiente)"></span>
                    </p>
                </div>

                <div>
                    <label for="abono-fecha" class="block text-sm font-medium text-brand-muted">
                        Fecha de pago <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="abono-fecha"
                        type="date"
                        class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        x-model="form.fecha_pago"
                        required
                    >
                </div>
            </div>

            <div>
                <label for="abono-metodo" class="block text-sm font-medium text-brand-muted">
                    Método de pago <span class="text-red-500">*</span>
                </label>
                <select
                    id="abono-metodo"
                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                    x-model="form.metodo_pago"
                    required
                >
                    <option value="">Seleccionar…</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="cheque">Cheque</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            <div>
                <label for="abono-consulta" class="block text-sm font-medium text-brand-muted">
                    Vincular a consulta (opcional)
                </label>
                <select
                    id="abono-consulta"
                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary disabled:bg-gray-50"
                    x-model.number="form.consulta_id"
                    :disabled="consultaPreasignada !== null"
                >
                    <option :value="null">Abono general (sin consulta específica)</option>
                    <template x-for="c in consultas" :key="c.id">
                        <option :value="c.id" x-text="c.label"></option>
                    </template>
                </select>
                <template x-if="consultaPreasignada !== null">
                    <p class="mt-1 text-xs text-brand-muted">
                        Este abono se vinculará automáticamente a la consulta actual.
                    </p>
                </template>
            </div>

            <div>
                <label for="abono-notas" class="block text-sm font-medium text-brand-muted">Notas</label>
                <textarea
                    id="abono-notas"
                    rows="2"
                    maxlength="1000"
                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                    placeholder="Referencia, observaciones, etc."
                    x-model="form.notas"
                ></textarea>
            </div>

            <div class="flex flex-col gap-2 border-t border-brand-border pt-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-brand-primary transition hover:bg-brand-soft"
                    @click="$dispatch('close-modal', '{{ $modalName }}')"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                    :disabled="enviando || ! formValido"
                    x-text="enviando ? 'Registrando…' : 'Registrar abono'"
                ></button>
            </div>
        </form>
    </div>
</x-modal>

@once
    <script>
        function modalAbono(initial) {
            return {
                endpoint: initial.endpoint,
                saldoPendiente: initial.saldoPendiente,
                consultas: initial.consultas,
                consultaPreasignada: initial.consultaPreasignada,
                modalName: initial.modalName,
                enviando: false,
                mensaje: { texto: '', tipo: 'success' },
                form: {
                    monto: '',
                    fecha_pago: new Date().toISOString().slice(0, 10),
                    metodo_pago: '',
                    consulta_id: null,
                    notas: '',
                },

                get formValido() {
                    return Number(this.form.monto) > 0
                        && Number(this.form.monto) <= this.saldoPendiente
                        && this.form.metodo_pago !== ''
                        && this.form.fecha_pago !== '';
                },

                resetForm() {
                    this.form = {
                        monto: '',
                        fecha_pago: new Date().toISOString().slice(0, 10),
                        metodo_pago: '',
                        consulta_id: this.consultaPreasignada,
                        notas: '',
                    };
                    this.mensaje = { texto: '', tipo: 'success' };
                },

                formatQ(n) {
                    return (Number(n) || 0).toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },

                async registrar() {
                    this.enviando = true;
                    this.mensaje.texto = '';
                    try {
                        const res = await fetch(this.endpoint, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                            },
                            body: JSON.stringify({
                                monto: Number(this.form.monto),
                                metodo_pago: this.form.metodo_pago,
                                fecha_pago: this.form.fecha_pago,
                                consulta_id: this.form.consulta_id || null,
                                notas: (this.form.notas || '').trim() || null,
                            }),
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
                        this.mensaje = { texto: 'Abono registrado. Recargando…', tipo: 'success' };
                        setTimeout(() => location.reload(), 600);
                    } catch (e) {
                        this.mensaje = { texto: e.message, tipo: 'error' };
                    } finally {
                        this.enviando = false;
                    }
                },
            };
        }
    </script>
@endonce
