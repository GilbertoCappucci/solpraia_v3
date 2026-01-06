<?php

namespace App\Livewire\Components;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class CheckStatusSelector extends Component
{
    #[Reactive]
    public $check;
    
    #[Reactive]
    public $newCheckStatus;
    
    #[Reactive]
    public $pendingCount = 0;
    
    #[Reactive]
    public $inProductionCount = 0;
    
    #[Reactive]
    public $inTransitCount = 0;
    
    #[Reactive]
    public $checkStatusAllowed = [];

    public function selectStatus($status)
    {
        $this->dispatch('check-status-selected', status: $status);
    }

    public function render()
    {
        // Verifica se há pedidos não entregues (excluindo cancelados)
        $hasIncompleteOrders = ($this->pendingCount > 0 ||
            $this->inProductionCount > 0 ||
            $this->inTransitCount > 0);

        // Verifica se pode cancelar (total zero)
        $canCancelCheck = $this->check->total == 0;

        // Regras de bloqueio baseadas no status atual do check
        $blockClosedButton = match($this->check->status) {
            'Open' => $hasIncompleteOrders,
            default => false
        };
        $blockPaidButton = ($this->check->status === 'Open');

        $isOpenAllowed = in_array('Open', $this->checkStatusAllowed) || $this->newCheckStatus === 'Open';
        $isClosedAllowed = in_array('Closed', $this->checkStatusAllowed) || $this->newCheckStatus === 'Closed';
        $isPaidAllowed = in_array('Paid', $this->checkStatusAllowed) || $this->newCheckStatus === 'Paid';
        $isCanceledAllowed = in_array('Canceled', $this->checkStatusAllowed) || $this->newCheckStatus === 'Canceled';

        return view('livewire.components.check-status-selector', [
            'hasIncompleteOrders' => $hasIncompleteOrders,
            'canCancelCheck' => $canCancelCheck,
            'blockClosedButton' => $blockClosedButton,
            'blockPaidButton' => $blockPaidButton,
            'isOpenAllowed' => $isOpenAllowed,
            'isClosedAllowed' => $isClosedAllowed,
            'isPaidAllowed' => $isPaidAllowed,
            'isCanceledAllowed' => $isCanceledAllowed,
        ]);
    }
}
