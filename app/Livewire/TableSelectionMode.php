<?php

namespace App\Livewire;

use App\Services\OrderService;
use App\Services\TableService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Reactive;

class TableSelectionMode extends Component
{
    #[Reactive]
    public $selectionMode = false;
    
    #[Reactive]
    public $selectedTables = [];
    
    public $showMergeModal = false;
    public $mergeDestinationTableId = null;

    protected $tableService;
    protected $orderService;

    public function boot(TableService $tableService, OrderService $orderService)
    {
        $this->tableService = $tableService;
        $this->orderService = $orderService;
    }

    protected function getListeners()
    {
        return [
            'toggle-selection-mode' => 'toggleSelectionMode',
            'open-merge-modal' => 'openMergeModal',
            'cancel-selection' => 'cancelSelection',
            'select-table-for-merge' => 'selectTableForMerge',
            'merge-closed' => 'closeMergeModal',
            'merge-completed' => 'onMergeCompleted',
        ];
    }

    public function toggleSelectionMode()
    {
        $this->selectionMode = !$this->selectionMode;
        
        // Reseta a seleção ao sair do modo
        if (!$this->selectionMode) {
            $this->selectedTables = [];
            $this->showMergeModal = false;
        }
        
        $this->dispatch('selection-mode-changed', $this->selectionMode);
    }

    public function cancelSelection()
    {
        $this->toggleSelectionMode();
    }

    public function selectTableForMerge($tableId)
    {
        // Verifica se a mesa já está selecionada
        if (in_array($tableId, $this->selectedTables)) {
            // Remove da seleção
            $this->selectedTables = array_diff($this->selectedTables, [$tableId]);
        } else {
            // Adiciona na seleção
            $this->selectedTables[] = $tableId;
        }
        
        $this->dispatch('selected-tables-changed', $this->selectedTables);
    }

    public function openMergeModal($selectedTables = null)
    {
        // Se receber selectedTables como parâmetro, atualiza a propriedade
        if ($selectedTables) {
            $this->selectedTables = $selectedTables;
        }
        
        // Garante que há pelo menos 2 mesas para unir
        if (count($this->selectedTables) < 2) {
            session()->flash('error', 'Selecione pelo menos duas mesas para unir.');
            return;
        }

        // Define um destino padrão (a primeira mesa selecionada)
        $this->mergeDestinationTableId = $this->selectedTables[0] ?? null;
        $this->showMergeModal = true;
        
        logger('🎯 openMergeModal called in TableSelectionMode', [
            'selectedTables' => $this->selectedTables,
            'showMergeModal' => $this->showMergeModal
        ]);
    }

    public function closeMergeModal()
    {
        $this->showMergeModal = false;
        $this->mergeDestinationTableId = null;
    }

    public function onMergeCompleted($payload = null)
    {
        logger('🔀 TableSelectionMode.onMergeCompleted called', ['payload' => $payload, 'selectedTables' => $this->selectedTables]);

        $success = false;
        $message = null;
        if (is_array($payload)) {
            $success = $payload['success'] ?? false;
            $message = $payload['message'] ?? null;
        }

        if ($success) {
            session()->flash('success', $message ?? 'Operação realizada com sucesso.');
        } else {
            session()->flash('error', $message ?? 'Erro ao realizar operação.');
        }

        $this->closeMergeModal();
        if ($this->selectionMode) {
            $this->toggleSelectionMode();
        }
        
        $this->dispatch('merge-operation-completed');
    }

    public function mergeTables()
    {
        // 1. Validações finais
        if (count($this->selectedTables) < 2) {
            session()->flash('error', 'Selecione pelo menos duas mesas para unir.');
            $this->closeMergeModal();
            return;
        }
        if (!$this->mergeDestinationTableId) {
            session()->flash('error', 'Selecione uma mesa de destino para a união.');
            $this->closeMergeModal();
            return;
        }

        // 2. Coletar IDs das comandas
        $allSelectedTableIds = $this->selectedTables;
        $destinationTableId = $this->mergeDestinationTableId;

        // Garante que a mesa de destino é uma das selecionadas
        if (!in_array($destinationTableId, $allSelectedTableIds)) {
            session()->flash('error', 'A mesa de destino deve ser uma das mesas selecionadas.');
            $this->closeMergeModal();
            return;
        }

        // Encontra as mesas e seus checks ativos
        $selectedTablesData = $this->tableService->getFilteredTables(
            Auth::user()->user_id,
            [],
            [],
            [],
            [],
            'OR'
        )->whereIn('id', $allSelectedTableIds);

        $destinationTable = $selectedTablesData->where('id', $destinationTableId)->first();
        if (!$destinationTable || !$destinationTable->checkId) {
            session()->flash('error', "A mesa de destino (Mesa {$destinationTable->number}) não possui uma comanda ativa para receber os pedidos.");
            $this->closeMergeModal();
            return;
        }
        $destinationCheckId = $destinationTable->checkId;

        $sourceTableIds = array_diff($allSelectedTableIds, [$destinationTableId]);
        $sourceCheckIds = [];
        $tablesToFree = [];

        foreach ($sourceTableIds as $tableId) {
            $table = $selectedTablesData->where('id', $tableId)->first();
            if ($table && $table->checkId) {
                $sourceCheckIds[] = $table->checkId;
                $tablesToFree[] = $table->id;
            } else {
                // Caso alguma mesa de origem não tenha check ativo, apenas liberamos ela
                if ($table) {
                    $tablesToFree[] = $table->id;
                }
            }
        }

        // Se não houver comandas de origem válidas para unir, mas houver mesas para liberar, liberamos.
        if (empty($sourceCheckIds) && !empty($tablesToFree)) {
            $this->tableService->releaseTables($tablesToFree);
            session()->flash('success', 'As mesas selecionadas foram liberadas.');
            $this->closeMergeModal();
            $this->toggleSelectionMode();
            return;
        } elseif (empty($sourceCheckIds)) {
            session()->flash('error', 'Nenhuma comanda de origem válida encontrada para unir.');
            $this->closeMergeModal();
            return;
        }

        // 3. Chamar o serviço de união
        $mergeResult = $this->orderService->mergeChecks($sourceCheckIds, $destinationCheckId);

        if ($mergeResult['success']) {
            // 4. Liberar mesas de origem
            $this->tableService->releaseTables($tablesToFree);
            session()->flash('success', $mergeResult['message']);
        } else {
            session()->flash('error', $mergeResult['message']);
        }

        // 5. Resetar estado e atualizar UI
        $this->closeMergeModal();
        $this->toggleSelectionMode();
    }

    public function render()
    {
        return view('livewire.table-selection-mode');
    }
}