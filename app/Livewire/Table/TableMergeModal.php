<?php

namespace App\Livewire\Table;

use App\Enums\CheckStatusEnum;
use App\Models\Check;
use App\Services\Order\OrderService;
use App\Services\Table\TableService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

class TableMergeModal extends Component
{
    public $showModal = false;
    public $selectedTables = [];
    public $mergeDestinationTableId = null;
    public $tables = [];

    protected $tableService;
    protected $orderService;

    public function boot(TableService $tableService, OrderService $orderService)
    {
        $this->tableService = $tableService;
        $this->orderService = $orderService;
    }
    
    #[Computed]
    public function selectedTablesData()
    {
        if (empty($this->selectedTables)) {
            return [];
        }
        
        return $this->tableService->getTablesByIds($this->selectedTables)
            ->map(function ($table) {
                // Busca check ativo
                $check = Check::where('table_id', $table->id)
                    ->whereIn('status', [CheckStatusEnum::OPEN->value, CheckStatusEnum::CLOSED->value])
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                return [
                    'id' => $table->id,
                    'number' => $table->number,
                    'name' => $table->name,
                    'checkId' => $check?->id,
                    'checkTotal' => $check?->total ?? 0,
                ];
            })
            ->toArray();
    }

    public function getListeners()
    {
        return [
            'open-merge-modal-component' => 'openModal',
        ];
    }

    public function openModal($selectedTables)
    {
        $this->selectedTables = $selectedTables;
        $this->mergeDestinationTableId = $selectedTables[0] ?? null;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->dispatch('merge-cancelled');
    }

    public function mergeTables()
    {
        // 1. Validações básicas
        if (count($this->selectedTables) < 2) {
            session()->flash('error', 'Selecione pelo menos duas mesas para unir.');
            $this->closeModal();
            return redirect()->route('tables');
        }
        
        if (!$this->mergeDestinationTableId) {
            session()->flash('error', 'Selecione uma mesa de destino para a união.');
            $this->closeModal();
            return redirect()->route('tables');
        }

        $allSelectedTableIds = $this->selectedTables;
        $destinationTableId = $this->mergeDestinationTableId;

        // Garante que a mesa de destino é uma das selecionadas
        if (!in_array($destinationTableId, $allSelectedTableIds)) {
            session()->flash('error', 'A mesa de destino deve ser uma das mesas selecionadas.');
            $this->closeModal();
            return redirect()->route('tables');
        }

        // 2. Buscar check ativo da mesa de destino diretamente do banco
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

        // 3. Verificar se alguma mesa de origem tem check ativo
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

        // 4. Se não há checks de origem nem de destino, só liberar as mesas
        if (!$hasSourceChecks && !$destinationCheck) {
            $this->tableService->releaseTables($tablesToFree);
            session()->flash('success', 'As mesas selecionadas foram liberadas.');
            $this->closeModal();
            $this->dispatch('merge-completed');
            return redirect()->route('tables');
        }

        // 5. Se há checks de origem mas não há check de destino, criar um novo check na mesa de destino
        if ($hasSourceChecks && !$destinationCheck) {
            $destinationCheck = Check::create([
                'table_id' => $destinationTableId,
                'status' => CheckStatusEnum::OPEN->value,
                'admin_id' => Auth::id(),
                'total' => 0.00,
            ]);
        }

        // 6. Se não há checks de origem, não há o que unir
        if (!$hasSourceChecks) {
            session()->flash('error', 'Nenhuma comanda de origem válida encontrada para unir.');
            $this->closeModal();
            return redirect()->route('tables');
        }

        $destinationCheckId = $destinationCheck->id;

        // 7. Chamar o serviço de união
        $mergeResult = $this->orderService->mergeChecks($sourceCheckIds, $destinationCheckId);

        if (!empty($mergeResult['success'])) {
            // 8. Liberar mesas de origem
            $this->tableService->releaseTables($tablesToFree);
            session()->flash('success', $mergeResult['message'] ?? 'Mesas unidas com sucesso.');
        } else {
            session()->flash('error', $mergeResult['message'] ?? 'Erro ao unir mesas.');
        }

        // 9. Resetar estado e atualizar UI
        $this->closeModal();
        $this->dispatch('merge-completed');
        return redirect()->route('tables');
    }

    public function render()
    {
        return view('livewire.table.table-merge-modal');
    }
}
