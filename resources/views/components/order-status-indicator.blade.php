@props([
    'status' => 'pending', // pending, production, transit
    'count' => 0,
    'minutes' => 0,
    'dotSize' => 'w-4 h-4',
    'textSize' => 'text-lg',
    'padding' => 'py-3'
])

@php
    $config = match($status) {
        'pending' => [
            'color' => 'bg-yellow-500',
            'textColor' => 'text-yellow-700',
            'label' => 'aguardando'
        ],
        'production' => [
            'color' => 'bg-blue-500',
            'textColor' => 'text-blue-700',
            'label' => 'em preparo'
        ],
        'transit' => [
            'color' => 'bg-purple-500',
            'textColor' => 'text-purple-700',
            'label' => 'em trânsito'
        ],
        default => [
            'color' => 'bg-gray-500',
            'textColor' => 'text-gray-700',
            'label' => ''
        ]
    };
@endphp

@if($count > 0)
    <div class="flex flex-col items-center justify-center {{ $padding }}">
        <span class="{{ $dotSize }} {{ $config['color'] }} rounded-full mb-1" 
              title="{{ $count }} {{ $config['label'] }}"></span>
        <span class="{{ $textSize }} font-semibold {{ $config['textColor'] }}">{{ $minutes }}m</span>
    </div>
@endif
