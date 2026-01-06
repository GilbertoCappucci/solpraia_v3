<?php

namespace App\Livewire\Components;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class CheckStatusSelector extends Component
{
    public $check;
    public $newCheckStatus;
    public $pendingCount = 0;
    public $inProductionCount = 0;
    public $inTransitCount = 0;
    public $checkStatusAllowed = [];

    public function mount($check, $newCheckStatus, $pendingCount = 0, $inProductionCount = 0, $inTransitCount = 0, $checkStatusAllowed = [])
    {
        $this->check = $check;
        $this->newCheckStatus = $newCheckStatus;
        $this->pendingCount = $pendingCount;
        $this->inProductionCount = $inProductionCount;
        $this->inTransitCount = $inTransitCount;
        $this->checkStatusAllowed = $checkStatusAllowed;
    }

    public function selectStatus($status)
    {
        $this->newCheckStatus = $status;
        
        // Atualiza diretamente o componente pai
        $this->dispatch('updateCheckStatus', status: $status)->to('order.order-status-modal');
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
