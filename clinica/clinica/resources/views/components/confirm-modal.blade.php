{{--
    Modal global de confirmación. Se monta UNA VEZ en el layout principal y
    cualquier código (Alpine, vanilla, formularios) puede invocarlo con:

        const ok = await window.confirmDialog({
            title: '¿Eliminar archivo?',
            message: 'Estás a punto de eliminar "radiografia.jpg". No se puede deshacer.',
            confirmText: 'Eliminar',
            variant: 'danger',  // danger | warning | info
        });
        if (! ok) return;

    Para formularios con onclick="return confirm(...)":
        <button type="button" onclick="window.confirmAndSubmit(this.closest('form'), {...})">
--}}

<div
    x-data="confirmModalApp()"
    x-on:confirm-dialog-open.window="open($event.detail)"
    x-on:keydown.escape.window="show && cancel()"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop con blur --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm"
        @click="cancel()"
    ></div>

    {{-- Card --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
    >
        <div class="px-6 pt-6 pb-4 text-center">
            {{-- Ícono circular --}}
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-full"
                :class="{
                    'bg-rose-100': variant === 'danger',
                    'bg-amber-100': variant === 'warning',
                    'bg-sky-100': variant === 'info',
                }"
            >
                <template x-if="variant === 'danger' || variant === 'warning'">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7"
                        :class="variant === 'danger' ? 'text-rose-600' : 'text-amber-600'"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </template>
                <template x-if="variant === 'info'">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7 text-sky-600"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                </template>
            </div>

            <h3 class="mt-4 text-lg font-semibold text-gray-900" x-text="title"></h3>

            <p class="mt-2 text-sm text-gray-600" x-text="message"></p>
        </div>

        <div class="flex flex-col gap-2 bg-gray-50 px-6 py-4 sm:flex-row-reverse">
            <button
                type="button"
                x-ref="confirmBtn"
                class="inline-flex w-full items-center justify-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
                :class="{
                    'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500': variant === 'danger',
                    'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500': variant === 'warning',
                    'bg-sky-600 hover:bg-sky-700 focus:ring-sky-500': variant === 'info',
                }"
                @click="confirm()"
                x-text="confirmText"
            ></button>

            <button
                type="button"
                class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:w-auto"
                @click="cancel()"
                x-text="cancelText"
            ></button>
        </div>
    </div>
</div>

@once
    <script>
        function confirmModalApp() {
            return {
                show: false,
                title: 'Confirmar acción',
                message: '¿Estás seguro?',
                confirmText: 'Confirmar',
                cancelText: 'Cancelar',
                variant: 'danger',
                _resolve: null,

                open(opts) {
                    this.title = opts.title || '¿Confirmar acción?';
                    this.message = opts.message || '¿Estás seguro de continuar?';
                    this.confirmText = opts.confirmText || 'Confirmar';
                    this.cancelText = opts.cancelText || 'Cancelar';
                    this.variant = opts.variant || 'danger';
                    this._resolve = opts.resolve;
                    this.show = true;

                    // Bloquear scroll del body mientras el modal está abierto
                    document.body.classList.add('overflow-hidden');
                },

                confirm() {
                    this.close(true);
                },

                cancel() {
                    this.close(false);
                },

                close(resultado) {
                    this.show = false;
                    document.body.classList.remove('overflow-hidden');
                    if (this._resolve) {
                        const resolver = this._resolve;
                        this._resolve = null;
                        resolver(resultado);
                    }
                },
            };
        }

        // API global Promise-based
        window.confirmDialog = function (opts) {
            return new Promise((resolve) => {
                window.dispatchEvent(new CustomEvent('confirm-dialog-open', {
                    detail: { ...opts, resolve },
                }));
            });
        };

        // Helper para reemplazar onclick="return confirm()" en formularios
        window.confirmAndSubmit = async function (form, opts) {
            if (! form) return;
            const ok = await window.confirmDialog(opts);
            if (ok) form.submit();
        };
    </script>
@endonce
