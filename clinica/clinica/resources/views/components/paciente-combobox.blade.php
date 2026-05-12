@props([
    'pacientes',
    'selected' => null,
    'name' => 'paciente_id',
    'inputId' => 'paciente_id',
    'placeholder' => 'Escribe para buscar...',
    'required' => true,
])

@php
    $items = collect($pacientes)->map(fn ($p) => [
        'id' => $p->id,
        'nombre' => $p->nombre_completo,
        'dpi' => $p->dpi,
    ])->values();

    $selectedId = old($name, $selected);
    $selectedItem = $items->firstWhere('id', is_numeric($selectedId) ? (int) $selectedId : $selectedId);
@endphp

<div
    x-data="pacienteCombobox({
        items: @js($items),
        initialId: @js($selectedId),
    })"
    class="relative"
>
    <input
        type="hidden"
        name="{{ $name }}"
        x-model="selectedId"
        @if ($required) required @endif
    >

    <input
        id="{{ $inputId }}"
        type="text"
        x-model="query"
        @focus="open = true"
        @click="open = true"
        @keydown.arrow-down.prevent="moveHighlight(1)"
        @keydown.arrow-up.prevent="moveHighlight(-1)"
        @keydown.enter.prevent="pickHighlighted()"
        @keydown.escape="open = false"
        autocomplete="off"
        placeholder="{{ $placeholder }}"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
    >

    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-md border border-gray-200 bg-white py-1 text-sm shadow-lg"
    >
        <template x-for="(p, index) in filtered" :key="p.id">
            <button
                type="button"
                @click="pick(p)"
                @mouseenter="highlightIndex = index"
                :class="{
                    'bg-gray-100': highlightIndex === index,
                    'text-gray-900': true,
                }"
                class="flex w-full flex-col items-start px-3 py-2 text-left hover:bg-gray-100"
            >
                <span class="font-medium" x-text="p.nombre"></span>
                <span class="text-xs text-gray-500" x-text="'DPI: ' + p.dpi"></span>
            </button>
        </template>

        <div
            x-show="filtered.length === 0"
            class="px-3 py-2 text-gray-500"
        >
            Sin coincidencias.
        </div>
    </div>
</div>

@once
    <script>
        function pacienteCombobox({ items, initialId }) {
                return {
                    items: items,
                    query: '',
                    open: false,
                    selectedId: initialId ?? '',
                    highlightIndex: 0,

                    init() {
                        if (this.selectedId) {
                            const found = this.items.find(p => String(p.id) === String(this.selectedId));
                            if (found) {
                                this.query = found.nombre;
                            }
                        }
                        this.$watch('query', () => {
                            this.highlightIndex = 0;
                            const match = this.items.find(p => p.nombre === this.query);
                            this.selectedId = match ? match.id : '';
                            this.open = true;
                        });
                    },

                    get filtered() {
                        const q = this.query.trim().toLowerCase();
                        if (q === '') {
                            return this.items.slice(0, 50);
                        }
                        return this.items.filter(p =>
                            p.nombre.toLowerCase().includes(q) ||
                            (p.dpi ?? '').toLowerCase().includes(q)
                        ).slice(0, 50);
                    },

                    pick(p) {
                        this.selectedId = p.id;
                        this.query = p.nombre;
                        this.open = false;
                    },

                    moveHighlight(delta) {
                        this.open = true;
                        const max = this.filtered.length - 1;
                        if (max < 0) return;
                        this.highlightIndex = Math.min(max, Math.max(0, this.highlightIndex + delta));
                    },

                    pickHighlighted() {
                        if (!this.open) {
                            this.open = true;
                            return;
                        }
                        const item = this.filtered[this.highlightIndex];
                        if (item) {
                            this.pick(item);
                        }
                    },
                };
        }
    </script>
@endonce
