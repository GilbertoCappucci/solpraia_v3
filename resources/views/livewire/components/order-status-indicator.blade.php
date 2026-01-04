<div class="flex flex-col items-center justify-center {{ $padding }}">
    <span class="{{ $dotSize }} {{ $this->getConfig()['color'] }} rounded-full mb-1" 
            title="{{ $count }} {{ $this->getConfig()['label'] }}"></span>
    <span class="{{ $textSize }} font-semibold {{ $this->getConfig()['textColor'] }}">{{ $minutes }}m</span>
</div>

