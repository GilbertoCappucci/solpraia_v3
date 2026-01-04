<div class="{{ $isDivider ? 'border-l border-white/30 pl-2 ml-2' : '' }} flex flex-col items-start">
    <span class="text-[10px] opacity-75 uppercase tracking-wider">{{ $label }}</span>
    <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full {{ $this->getColorConfig() }}"></span>
        <span class="text-sm font-medium">{{ $value }}</span>
    </div>
</div>
