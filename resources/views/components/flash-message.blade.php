<div style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.5rem;">
    @if (session()->has('success'))
        <div
            x-data="{ show: false }"
            x-init="$nextTick(() => { show = true; setTimeout(() => show = false, 4000) })"
            x-show="show"
            x-transition:enter="transition-all ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition-all ease-in duration-500"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="bg-white border border-green-500 text-green-700 px-6 py-4 rounded-lg shadow-lg font-semibold min-w-[260px] max-w-xs text-sm flex items-center justify-between"
        >
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div
            x-data="{ show: false }"
            x-init="$nextTick(() => { show = true; setTimeout(() => show = false, 5000) })"
            x-show="show"
            x-transition:enter="transition-all ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition-all ease-in duration-500"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="bg-white border border-red-500 text-red-700 px-6 py-4 rounded-lg shadow-lg font-semibold min-w-[260px] max-w-xs text-sm flex items-center justify-between"
        >
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>