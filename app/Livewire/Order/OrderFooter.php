<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class OrderFooter extends Component
{
    #[Reactive]
    public $selectedTable;
    
    #[Reactive]
    public $currentCheck;
    
    #[Reactive]
    public $checkTotal = 0;
    
    public $userId;
    public $tableId;

    public function mount($selectedTable, $currentCheck, $checkTotal = 0, $tableId = null, $userId = null)
    {
        $this->selectedTable = $selectedTable;
        $this->currentCheck = $currentCheck;
        $this->checkTotal = $checkTotal;
        $this->tableId = $tableId;
        $this->userId = $userId;
    }

    public function getListeners()
    {
        $listeners = [
            'check-updated' => 'onCheckUpdated',
        ];

        if ($this->userId) {
            $listeners["echo-private:tables-updated.{$this->userId},.table.updated"] = 'onTableUpdated';
            $listeners["echo-private:tables-updated.{$this->userId},.check.updated"] = 'onCheckUpdated';
        }

        return $listeners;
    }

    public function onTableUpdated($data)
    {
        if (isset($data['tableId']) && $data['tableId'] == $this->selectedTable->id) {
            $this->dispatch('refresh-parent');
        }
    }

    public function onCheckUpdated($data)
    {
        if (isset($data['checkId']) && $this->currentCheck && $data['checkId'] == $this->currentCheck->id) {
            $this->dispatch('refresh-parent');
        }
    }

    public function goToMenu()
    {
        // Verifica se a mesa está fechada
        if ($this->selectedTable->status === \App\Enums\TableStatusEnum::CLOSE->value) {
            session()->flash('error', 'Mesa fechada! Não é possível adicionar pedidos.');
            return;
        }

        // Verifica se o check está aberto (permite se check for NULL - primeiro pedido)
        if ($this->currentCheck && $this->currentCheck->status !== 'Open') {
            session()->flash('error', 'Check não está aberto! Não é possível adicionar pedidos.');
            return;
        }

        return redirect()->route('menu', ['tableId' => $this->tableId]);
    }

    public function render()
    {
        $isCheckOpen = $this->currentCheck && $this->currentCheck->status === 'Open';
        
        return view('livewire.order.order-footer', [
            'isCheckOpen' => $isCheckOpen,
        ]);
    }
}
