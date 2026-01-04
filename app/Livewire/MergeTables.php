<?php

namespace App\Livewire;

use App\Services\OrderService;
use App\Services\TableService;
use App\Models\Check;
use App\Enums\CheckStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Livewire\Component;

class MergeTables extends Component
{
    public $selectedTables = [];
    /** @var Collection<int, mixed> */
    public $tables;
    public $mergeDestinationTableId = null;

    protected $tableService;
    protected $orderService;

    public function boot(TableService $tableService, OrderService $orderService)
    {
        $this->tableService = $tableService;
        $this->orderService = $orderService;
    }

    public function mount($selectedTables = [])
    {
        $this->selectedTables = is_array($selectedTables) ? $selectedTables : [];
        
        // Buscar as mesas selecionadas diretamente pelo TableService
        if (!empty($this->selectedTables)) {
            $this->tables = collect($this->tableService->getTablesByIds($this->selectedTables));
        } else {
            $this->tables = collect();
        }
        
        $this->mergeDestinationTableId = $this->selectedTables[0] ?? null;
    }

    public function close()
    {
        $this->dispatch('merge-closed');
    }

    public function confirmMerge()
    {

        if (count($this->selectedTables) < 2) {
            $this->dispatch('merge-completed', ['success' => false, 'message' => 'Selecione pelo menos duas mesas para unir.']);
            $this->dispatch('merge-closed');
            return;
        }

        if (!$this->mergeDestinationTableId) {
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

        $sourceTableIds = array_diff($allSelectedTableIds, [$destinationTableId]);
        $sourceCheckIds = [];
        $tablesToFree = [];

        // Verificar se alguma mesa de origem tem check ativo
        $hasSourceChecks = false;
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
                $hasSourceChecks = true;
            }
            
            $tablesToFree[] = $tableId;
        }

        logger('🔀 MergeTables: collected source checks', [
            'sourceTableIds' => $sourceTableIds,
            'sourceCheckIds' => $sourceCheckIds,
            'tablesToFree' => $tablesToFree,
            'hasSourceChecks' => $hasSourceChecks,
            'destinationCheckExists' => !is_null($destinationCheck)
        ]);

        // Se não há checks de origem nem de destino, só liberar as mesas
        if (!$hasSourceChecks && !$destinationCheck) {
            $this->tableService->releaseTables($tablesToFree);
            logger('🔀 MergeTables: releasing all tables (no checks anywhere)', ['tablesToFree' => $tablesToFree]);
            $this->dispatch('merge-completed', ['success' => true, 'message' => 'As mesas selecionadas foram liberadas.']);
            $this->dispatch('merge-closed');
            return;
        }

        // Se há checks de origem mas não há check de destino, criar um novo check na mesa de destino
        if ($hasSourceChecks && !$destinationCheck) {
            logger('🔀 MergeTables: creating new check for destination table', ['destinationTableId' => $destinationTableId]);
            
            // Criar novo check na mesa de destino
            $destinationCheck = Check::create([
                'table_id' => $destinationTableId,
                'status' => CheckStatusEnum::OPEN->value,
                'user_id' => Auth::id(),
                'total' => 0.00,
            ]);
            
            logger('🔀 MergeTables: new destination check created', ['checkId' => $destinationCheck->id]);
        }

        // Se não há checks de origem, não há o que unir
        if (!$hasSourceChecks) {
            logger('🔀 MergeTables: no source checks found', ['selectedTables' => $this->selectedTables]);
            $this->dispatch('merge-completed', ['success' => false, 'message' => 'Nenhuma comanda de origem válida encontrada para unir.']);
            $this->dispatch('merge-closed');
            return;
        }

        $destinationCheckId = $destinationCheck->id;

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
