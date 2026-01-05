<div 
    x-data="{
        minutes: {{ $minutes }},
        timestamp: @js($timestamp),
        updateMinutes() {
            if (this.timestamp) {
                const now = Math.floor(Date.now() / 1000);
                const statusTime = Math.floor(new Date(this.timestamp).getTime() / 1000);
                this.minutes = Math.floor((now - statusTime) / 60);
            }
        }
    }"
    x-init="if (timestamp) { updateMinutes(); setInterval(() => updateMinutes(), 5000); }"
    class="flex flex-col items-center justify-center {{ $padding }}">
    <span class="{{ $dotSize }} {{ $this->getConfig()['color'] }} rounded-full mb-1" 
            title="{{ $count }} {{ $this->getConfig()['label'] }}"></span>
    <span class="{{ $textSize }} font-semibold {{ $this->getConfig()['textColor'] }}" x-text="minutes + 'm'"></span>
</div>

