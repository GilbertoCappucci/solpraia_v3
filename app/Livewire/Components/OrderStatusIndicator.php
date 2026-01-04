<?php

namespace App\Livewire\Components;

use Livewire\Component;

class OrderStatusIndicator extends Component
{
    public string $status = 'pending'; // pending, production, transit
    public int $count = 0;
    public int $minutes = 0;
    public string $dotSize = 'w-4 h-4';
    public string $textSize = 'text-lg';
    public string $padding = 'py-3';

    public function mount(string $status = 'pending', int $count = 0, int $minutes = 0, string $dotSize = 'w-4 h-4', string $textSize = 'text-lg', string $padding = 'py-3')
    {
        $this->status = $status;
        $this->count = $count;
        $this->minutes = $minutes;
        $this->dotSize = $dotSize;
        $this->textSize = $textSize;
        $this->padding = $padding;
    }

    public function getConfig(): array
    {
        return match($this->status) {
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
    }

    public function render()
    {
        return view('livewire.components.order-status-indicator');
    }
}
