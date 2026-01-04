<?php

namespace App\Livewire\Components;

use Livewire\Component;

class OrderStatusBadge extends Component
{
    public string $label;
    public $value;
    public string $color;
    public bool $isDivider = false;

    public function mount(string $label, $value, string $color, bool $isDivider = false)
    {
        $this->label = $label;
        $this->value = $value;
        $this->color = $color;
        $this->isDivider = $isDivider;
    }

    public function getColorConfig(): string
    {
        return match($this->color) {
            'gray' => 'bg-gray-400',
            'blue' => 'bg-blue-400',
            'purple' => 'bg-purple-400',
            'green' => 'bg-green-400',
            'yellow' => 'bg-yellow-400',
            'red' => 'bg-red-400',
            'orange' => 'bg-orange-400',
            'teal' => 'bg-teal-400',
            default => 'bg-gray-400'
        };
    }

    public function render()
    {
        return view('livewire.components.order-status-badge');
    }
}
