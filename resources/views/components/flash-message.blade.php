@props(['enabled'])

@if($enabled??false)
    @if (session()->has('success'))
        <div 
            x-data="{ show: false }" 
            x-init="$nextTick(() => { show = true; setTimeout(() => show = false, 4000) })"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="bg-green-500 text-white px-4 py-3 text-center font-medium shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div 
            x-data="{ show: false }" 
            x-init="$nextTick(() => { show = true; setTimeout(() => show = false, 5000) })"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="bg-red-500 text-white px-4 py-3 text-center font-medium shadow-lg">
            {{ session('error') }}
        </div>
    @endif
@endif