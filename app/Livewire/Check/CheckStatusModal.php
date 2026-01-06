<?php

namespace App\Livewire\Check;

use App\Models\Check;
use App\Services\CheckService;
use Livewire\Component;

class CheckStatusModal extends Component
{
    public $show = false;
    public $embedded = false; // Modo embedded (sem modal próprio)
    public $check;
    public $newCheckStatus;
    public $checkStatusAllowed = [];
    
    protected $checkService;

    public function boot(CheckService $checkService)
    {
        $this->checkService = $checkService;
    }

    public function mount(bool $embedded = false, ?int $checkId = null)
    {
        $this->embedded = $embedded;
        
        if ($embedded && $checkId) {
            $this->loadCheck($checkId);
        }
    }

    public function getListeners()
    {
        return [
            'open-check-status-modal' => 'openModal',
        ];
    }

    public function loadCheck($checkId)
    {
        $this->check = Check::with('orders')->find($checkId);
        
        if (!$this->check) {
            session()->flash('error', 'Check não encontrado.');
            return;
        }

        // Inicializa com o status atual
        $this->newCheckStatus = $this->check->status;
        
        // Calcula status permitidos
        $this->checkStatusAllowed = $this->checkService->getAllowedCheckStatuses(
            $this->newCheckStatus,
            $this->check
        );
    }

    public function openModal($checkId)
    {
        $this->loadCheck($checkId);
        $this->show = true;
    }

    public function setCheckStatus($status)
    {
        $this->newCheckStatus = $status;
    }

    public function closeModal()
    {
        if (!$this->embedded) {
            $this->show = false;
        }
        
        $this->check = null;
        $this->newCheckStatus = null;
        $this->checkStatusAllowed = [];
    }

    public function updateStatus()
    {
        if (!$this->check || !$this->newCheckStatus) {
            return;
        }

        $result = $this->checkService->validateAndUpdateCheckStatus(
            $this->check,
            $this->newCheckStatus
        );

        if (!$result['success']) {
            session()->flash('error', implode(' ', $result['errors']));
            return;
        }

        // Se alterou para Fechado, redireciona para a tela do check
        if ($this->newCheckStatus === 'Closed') {
            session()->flash('success', 'Check fechado! Finalize o pagamento.');
            return redirect()->route('check', ['checkId' => $this->check->id]);
        }

        session()->flash('success', 'Status do check atualizado com sucesso!');
        
        if (!$this->embedded) {
            $this->closeModal();
        }
        
        $this->dispatch('check-status-updated');
    }

    public function render()
    {
        $orders = $this->check?->orders ?? collect();
        
        $pendingCount = $orders->where('status', 'pending')->count();
        $inProductionCount = $orders->where('status', 'in_production')->count();
        $inTransitCount = $orders->where('status', 'in_transit')->count();
        
        $hasIncompleteOrders = ($pendingCount > 0 || $inProductionCount > 0 || $inTransitCount > 0);
        
        $isOpenAllowed = in_array('Open', $this->checkStatusAllowed) || $this->newCheckStatus === 'Open';
        $isClosedAllowed = in_array('Closed', $this->checkStatusAllowed) || $this->newCheckStatus === 'Closed';
        $isPaidAllowed = in_array('Paid', $this->checkStatusAllowed) || $this->newCheckStatus === 'Paid';
        $isCanceledAllowed = in_array('Canceled', $this->checkStatusAllowed) || $this->newCheckStatus === 'Canceled';
        
        return view('livewire.check.check-status-modal', [
            'hasIncompleteOrders' => $hasIncompleteOrders,
            'isOpenAllowed' => $isOpenAllowed,
            'isClosedAllowed' => $isClosedAllowed,
            'isPaidAllowed' => $isPaidAllowed,
            'isCanceledAllowed' => $isCanceledAllowed,
        ]);
    }
}
