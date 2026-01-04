<?php

namespace App\Livewire;

use App\Services\OrderService;
use App\Services\TableService;
use App\Models\Check;
use App\Enums\CheckStatusEnum;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MergeTables extends Component
{
    public $selectedTables = [];
    public $tables = [];
    public $mergeDestinationTableId = null;

    protected $tableService;
    protected $orderService;

    public function boot(TableService $tableService, OrderService $orderService)
    {
        $this->tableService = $tableService;
        $this->orderService = $orderService;
    }

    public function mount($selectedTables = [], $tables = [])
    {
        $this->selectedTables = is_array($selectedTables) ? $selectedTables : [];
        $this->tables = collect($tables);
        $this->mergeDestinationTableId = $this->selectedTables[0] ?? null;
        
        logger('🔀 MergeTables.mount', [
            'selectedTables' => $this->selectedTables,
            'tables_count' => $this->tables->count(),
            'tables_with_checkId' => $this->tables->filter(fn($t) => !empty($t->checkId))->count(),
            'sample_table' => $this->tables->first() ? [
                'id' => $this->tables->first()->id,
                'number' => $this->tables->first()->number,
                'checkId' => $this->tables->first()->checkId ?? 'NULL',
            ] : null,
        ]);
    }

    public function close()
    {
        $this->dispatch('merge-closed');
    }

    public function confirmMerge()
    {
        logger('🔀 MergeTables.confirmMerge called', [
            'selectedTables' => $this->selectedTables,
            'mergeDestinationTableId' => $this->mergeDestinationTableId,
            'user' => Auth::id(),
        ]);

        if (count($this->selectedTables) < 2) {
            logger('🔀 MergeTables: less than 2 tables selected', ['selectedTables' => $this->selectedTables]);
            $this->dispatch('merge-completed', ['success' => false, 'message' => 'Selecione pelo menos duas mesas para unir.']);
            $this->dispatch('merge-closed');
            return;
        }

        if (!$this->mergeDestinationTableId) {
            logger('🔀 MergeTables: no destination selected', ['selectedTables' => $this->selectedTables]);
            $this->dispatch('merge-completed', ['success' => false, 'message' => 'Selecione uma mesa de destino para a união.']);
            $this->dispatch('merge-closed');
            return;
        }

        $allSelectedTableIds = $this->selectedTables;
        $destinationTableId = $this->mergeDestinationTableId;

        if (!in_array($destinationTableId, $allSelectedTableIds)) {
            logger('🔀 MergeTables: destination not in selected list', ['destination' => $destinationTableId, 'selected' => $allSelectedTableIds]);
            $this->dispatch('merge-completed', ['success' => false, 'message' => 'A mesa de destino deve ser uma das mesas selecionadas.']);
            $this->dispatch('merge-closed');
            return;
        }

        $selectedTablesData = $this->tables->whereIn('id', $allSelectedTableIds);
        logger('🔀 MergeTables: selectedTablesData resolved', [
            'count' => $selectedTablesData->count(),
            'ids' => $allSelectedTableIds,
        ]);

        // Buscar o check ativo da mesa de destino diretamente do banco
        $destinationCheck = Check::where('table_id', $destinationTableId)
            ->whereIn('status', [
                CheckStatusEnum::OPEN->value,
                CheckStatusEnum::CLOSED->value,
            ])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$destinationCheck) {
            $destinationTable = $selectedTablesData->where('id', $destinationTableId)->first();
            $num = $destinationTable->number ?? $destinationTableId;
            logger('🔀 MergeTables: destination has no active check', [
                'destination' => $destinationTableId, 
                'check_found' => $destinationCheck ? 'yes' : 'no'
            ]);
            $this->dispatch('merge-completed', ['success' => false, 'message' => "A mesa de destino (Mesa {$num}) não possui uma comanda ativa para receber os pedidos."]);
            $this->dispatch('merge-closed');
            return;
        }

        $destinationCheckId = $destinationCheck->id;

        $sourceTableIds = array_diff($allSelectedTableIds, [$destinationTableId]);
        $sourceCheckIds = [];
        $tablesToFree = [];

        foreach ($sourceTableIds as $tableId) {
            // Buscar check ativo diretamente do banco para cada mesa de origem
            $sourceCheck = Check::where('table_id', $tableId)
                ->whereIn('status', [
                    CheckStatusEnum::OPEN->value,
                    CheckStatusEnum::CLOSED->value,
                ])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($sourceCheck) {
                $sourceCheckIds[] = $sourceCheck->id;
            }
            
            $tablesToFree[] = $tableId;
        }

        logger('🔀 MergeTables: collected source checks', [
            'sourceTableIds' => $sourceTableIds,
            'sourceCheckIds' => $sourceCheckIds,
            'tablesToFree' => $tablesToFree,
        ]);

        if (empty($sourceCheckIds) && !empty($tablesToFree)) {
            $this->tableService->releaseTables($tablesToFree);
            logger('🔀 MergeTables: releasing tables (no source checks)', ['tablesToFree' => $tablesToFree]);
            $this->dispatch('merge-completed', ['success' => true, 'message' => 'As mesas selecionadas foram liberadas.']);
            $this->dispatch('merge-closed');
            return;
        } elseif (empty($sourceCheckIds)) {
            logger('🔀 MergeTables: no source checks and no tables to free', ['selectedTables' => $this->selectedTables]);
            $this->dispatch('merge-completed', ['success' => false, 'message' => 'Nenhuma comanda de origem válida encontrada para unir.']);
            $this->dispatch('merge-closed');
            return;
        }

        logger('🔀 MergeTables: calling OrderService::mergeChecks', ['sourceCheckIds' => $sourceCheckIds, 'destinationCheckId' => $destinationCheckId]);

        $mergeResult = $this->orderService->mergeChecks($sourceCheckIds, $destinationCheckId);

        logger('🔀 MergeTables: mergeChecks result', ['mergeResult' => $mergeResult]);

        if (!empty($mergeResult['success'])) {
            $this->tableService->releaseTables($tablesToFree);
            $this->dispatch('merge-completed', ['success' => true, 'message' => $mergeResult['message'] ?? 'Mesas unidas com sucesso.']);
        } else {
            $this->dispatch('merge-completed', ['success' => false, 'message' => $mergeResult['message'] ?? 'Erro ao unir mesas.']);
        }

        $this->dispatch('merge-closed');
    }

    public function render()
    {
        return view('livewire.merge-tables');
    }
}
