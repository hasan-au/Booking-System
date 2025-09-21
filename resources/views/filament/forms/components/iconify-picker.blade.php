@php
    $statePath = $getStatePath();
    $prefixesList = $getPrefixes();
    $prefixesParam = $prefixesList ? implode(',', $prefixesList) : null;
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    {{-- Load Iconify web component for previews --}}
    <script type="module">
        if (!window.__iconify_loaded) {
            window.__iconify_loaded = true;
            const s = document.createElement('script');
            s.src = 'https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js';
            document.head.appendChild(s);
        }
    </script>

    <div
        x-data="window.iconifyPicker({
            entangled: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            limit: {{ $getLimit() }},
            prefixes: @js($prefixesParam),
        })"
    >
        <!-- Input row -->
        <div class="flex items-center gap-2 rounded-lg border px-3 py-2 w-full bg-white dark:bg-gray-900">
            {{-- <iconify-icon x-show="entangled" x-bind:icon="entangled" class="h-6 w-6"></iconify-icon> --}}
            <!-- Preview: prefer web component; fall back to <img> -->
            <div class="h-6 w-6 shrink-0 text-gray-700 dark:text-sky-400">
                <template x-if="entangled && supportsWebComponent()">
                    <iconify-icon :icon="entangled" class="h-6 w-6"></iconify-icon>
                </template>

                <template x-if="entangled && !supportsWebComponent()">
                    <img :src="iconUrl(entangled)" alt="" class="h-6 w-6" loading="lazy">
                </template>
            </div>

            <input
                type="text"
                placeholder="Search icons (e.g. home, calendar)"
                x-model.debounce.300ms="query"
                class="w-full bg-transparent border-0 focus:outline-none focus:ring-0"
            />

            <button type="button" @click="open = !open" class="px-2 py-1 text-sm border rounded">
                Browse
            </button>

            <button
                type="button"
                x-show="entangled"
                @click="clearSelection()"
                class="px-2 py-1 text-sm border rounded text-red-600"
                title="Clear"
            >
                Clear
            </button>
        </div>

        <!-- Results panel -->
        <div x-show="open" x-transition class="mt-2 max-h-80 overflow-auto rounded-lg border p-2 bg-white dark:bg-gray-900">
            <template x-if="loading">
                <div class="p-3 text-sm text-gray-500">Searching…</div>
            </template>

            <template x-if="!loading && results.length === 0 && query.trim().length >= 2">
                <div class="p-3 text-sm text-gray-500">No icons found.</div>
            </template>

            <div class="grid grid-cols-6 gap-2 sm:grid-cols-8 md:grid-cols-10">
                <template x-for="name in results" :key="name">
                    <button type="button" class="flex flex-col items-center gap-1 rounded-md border p-2 hover:bg-gray-50 dark:hover:bg-gray-800" @click="select(name)">
                        {{-- <iconify-icon :icon="name" class="h-6 w-6"></iconify-icon> --}}
                        <!-- Result item: component or <img> fallback -->
                            <iconify-icon
                                x-show="window.customElements && window.customElements.get('iconify-icon')"
                                :icon="name"
                                class="h-6 w-6"
                            ></iconify-icon>

                            <img
                                x-show="!(window.customElements && window.customElements.get('iconify-icon'))"
                                :src="`https://api.iconify.design/${name}.svg?color=currentColor`"
                                alt=""
                                class="h-6 w-6"
                                loading="lazy"
                            />
                        <span class="truncate text-[11px]" x-text="name"></span>
                    </button>
                </template>
            </div>

            <div class="flex justify-end gap-2 pt-2" x-show="hasMore">
                <button type="button" class="px-3 py-1 text-sm border rounded" @click="loadMore()">Load more</button>
            </div>
        </div>

        {{-- Keep Livewire model binding happy --}}
        <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}" />
    </div>
</x-dynamic-component>
