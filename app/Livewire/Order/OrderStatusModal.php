<?php

namespace App\Livewire\Order;

use App\Services\CheckService;
use App\Services\Order\OrderService;
use Livewire\Component;

class OrderStatusModal extends Component
{
    public $show = false;
    public $selectedTable;
    public $currentCheck;
    public $orders;
    public $newTableStatus;
    public $newCheckStatus;
    public $hasActiveCheck = false;
    public $checkStatusAllowed = [];
    
    protected $orderService;
    protected $checkService;

    public function boot(OrderService $orderService, CheckService $checkService)
    {
        $this->orderService = $orderService;
        $this->checkService = $checkService;
    }

    public function getListeners()
    {
        return [
            'open-status-modal' => 'openModal',
        ];
    }

    public function openModal()
    {
        $this->show = true;
        $this->dispatch('refresh-modal-data');
    }

    public function updatedNewCheckStatus($value)
    {
        // Atualiza os status permitidos para o check com base no novo status selecionado
        $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses($value, $this->currentCheck);
    }

    public function closeModal()
    {
        $this->show = false;
        $this->newTableStatus = null;
        $this->newCheckStatus = null;
    }

    public function updateStatuses()
    {
        $result = $this->orderService->updateStatuses(
            $this->selectedTable,
            $this->currentCheck,
            $this->newTableStatus,
            $this->newCheckStatus
        );

        if (!$result['success']) {
            session()->flash('error', implode(' ', $result['errors']));
            return;
        }

        // Se alterou para Fechado, redireciona para a tela do check
        if ($this->newCheckStatus === 'Closed') {
            session()->flash('success', 'Check fechado! Finalize o pagamento.');
            return redirect()->route('check', ['checkId' => $this->currentCheck->id]);
        }

        session()->flash('success', 'Status atualizado com sucesso!');
        $this->closeModal();
        $this->dispatch('refresh-parent');
    }

    public function render()
    {
        return view('livewire.order.order-status-modal');
    }
}
