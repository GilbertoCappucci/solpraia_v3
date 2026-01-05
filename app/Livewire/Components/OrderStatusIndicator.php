<?php

namespace App\Livewire\Components;

use Livewire\Component;

class OrderStatusIndicator extends Component
{
    public string $status = 'pending'; // pending, production, transit
    public int $count = 0;
    public int $minutes = 0;
    public ?string $timestamp = null;
    public string $dotSize = 'w-4 h-4';
    public string $textSize = 'text-lg';
    public string $padding = 'py-3';

    public function mount(string $status = 'pending', int $count = 0, int $minutes = 0, ?string $timestamp = null, string $dotSize = 'w-4 h-4', string $textSize = 'text-lg', string $padding = 'py-3')
    {
        $this->status = $status;
        $this->count = $count;
        $this->minutes = $minutes;
        $this->timestamp = $timestamp;
        $this->dotSize = $dotSize;
        $this->textSize = $textSize;
        $this->padding = $padding;
    }

    public function getConfig(): array
    {
        return match ($this->status) {
            'pending' => [
                'label' => 'Aguardando',
                'color' => 'bg-yellow-400',
                'textColor' => 'text-yellow-600',
            ],
            'production' => [
                'label' => 'Em Preparo',
                'color' => 'bg-blue-400',
                'textColor' => 'text-blue-600',
            ],
            'transit' => [
                'label' => 'Em Trânsito',
                'color' => 'bg-purple-400',
                'textColor' => 'text-purple-600',
            ],
            default => [
                'label' => 'Desconhecido',
                'color' => 'bg-gray-400',
                'textColor' => 'text-gray-600',
            ],
        };
    }

    public function render()
    {
        return view('livewire.components.order-status-indicator');
    }
}
