@props(['label', 'value', 'color', 'isDivider' => false])

@php
    $colorConfig = match($color) {
        'gray' => 'bg-gray-400',
        'blue' => 'bg-blue-400',
        'purple' => 'bg-purple-400',
        'green' => 'bg-green-400',
        'yellow' => 'bg-yellow-400',
        'red' => 'bg-red-400',
        'orange' => 'bg-orange-400',
        default => 'bg-gray-400'
    };
@endphp

<div class="{{ $isDivider ? 'border-l border-white/30 pl-2 ml-2' : '' }} flex flex-col items-start">
    <span class="text-[10px] opacity-75 uppercase tracking-wider">{{ $label }}</span>
    <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full {{ $colorConfig }}"></span>
        <span class="text-sm font-medium">{{ $value }}</span>
    </div>
</div>
