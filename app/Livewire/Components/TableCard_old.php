<?php

namespace App\Livewire\Components;

use App\Models\Table as TableModel;
use App\Services\Table\TableService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TableCard extends Component
{
    public TableModel $table;
    public bool $delayAlarmEnabled = true;
    public bool $selectionMode = false;
    public array $selectedTables = [];
    public array $timeLimits = [];
    
    protected $tableService;
    
    public function getListeners()
    {
        return [
            "echo-private:tables-updated.{$this->table->user_id},.table.updated" => 'handleTableUpdated',
        ];
    }

    public function handleTableUpdated($data)
    {
        logger('📶 TableCard received table.updated event', ['data' => $data]);

        // Verifica se o evento é para esta mesa
        if (isset($data['tableId']) && $data['tableId'] == $this->table->id) {

        }

        // Recarrega os dados da mesa
        $this->dispatch('$refresh');
    }

    public function boot(TableService $tableService)
    {
        $this->tableService = $tableService;
    }

    public function mount(TableModel $table, bool $delayAlarmEnabled = true, bool $selectionMode = false, array $selectedTables = [], array $timeLimits = [])
    {
        $this->table = $table;
        $this->delayAlarmEnabled = $delayAlarmEnabled;
        $this->selectionMode = $selectionMode;
        $this->selectedTables = $selectedTables;
        $this->timeLimits = $timeLimits;
    }

    #[Computed]
    public function isSelected(): bool
    {
        return $this->selectionMode && in_array($this->table->id, $this->selectedTables);
    }

    #[Computed]
    public function isDisabled(): bool
    {
        return $this->selectionMode && !$this->tableService->canTableBeMerged($this->table);
    }

    #[Computed]
    public function activeStatuses(): int
    {
        $count = 0;
        if (isset($this->table->ordersPending) && $this->table->ordersPending > 0) $count++;
        if (isset($this->table->ordersInProduction) && $this->table->ordersInProduction > 0) $count++;
        if (isset($this->table->ordersInTransit) && $this->table->ordersInTransit > 0) $count++;
        return $count;
    }

    #[Computed]
    public function gridClass(): string
    {
        return match($this->activeStatuses) {
            1 => 'grid-cols-1',
            2 => 'grid-cols-2',
            3 => 'grid-cols-3',
            default => 'grid-cols-1'
        };
    }

    #[Computed]
    public function dotSize(): string
    {
        return match($this->activeStatuses) {
            1 => 'w-6 h-6',
            2 => 'w-4 h-4',
            default => 'w-3 h-3'
        };
    }

    #[Computed]
    public function textSize(): string
    {
        return match($this->activeStatuses) {
            1 => 'text-2xl',
            2 => 'text-lg',
            default => 'text-sm'
        };
    }

    #[Computed]
    public function padding(): string
    {
        return match($this->activeStatuses) {
            1 => 'py-4',
            2 => 'py-3',
            default => 'py-2'
        };
    }

    #[Computed]
    public function cardClasses(): string
    {
        return match(true) {
            $this->table->status === 'releasing' => 'bg-gradient-to-br from-teal-50 to-teal-100 border-teal-400 hover:border-teal-500',
            $this->table->checkStatus === 'Open' => 'bg-white border-green-400 hover:border-green-500',
            $this->table->checkStatus === 'Closed' => 'bg-gradient-to-br from-orange-50 to-orange-100 border-orange-400 hover:border-orange-500',
            $this->table->checkStatus === 'Paid' => 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-400 hover:border-gray-500',
            $this->table->status === 'occupied' => 'bg-white border-green-400 hover:border-green-500',
            $this->table->status === 'reserved' => 'bg-gradient-to-br from-purple-50 to-purple-100 border-purple-400 hover:border-purple-500',
            $this->table->status === 'close' => 'bg-gradient-to-br from-red-50 to-red-100 border-red-600 hover:border-red-700',
            default => 'bg-white border-gray-300 hover:border-gray-400'
        };
    }

    #[Computed]
    public function bottomBarBg(): string
    {
        return match(true) {
            $this->table->status === 'releasing' => 'bg-teal-100',
            $this->table->checkStatus === 'Open' => 'bg-white',
            $this->table->checkStatus === 'Closed' => 'bg-orange-100',
            $this->table->checkStatus === 'Paid' => 'bg-gray-100',
            $this->table->status === 'occupied' => 'bg-white',
            $this->table->status === 'reserved' => 'bg-purple-100',
            $this->table->status === 'close' => 'bg-red-100',
            default => 'bg-white'
        };
    }

    #[Computed]
    public function showCenterLabel(): bool
    {
        return $this->table->checkStatus === 'Paid' && $this->activeStatuses === 0;
    }

    #[Computed]
    public function showClosedIndicator(): bool
    {
        return $this->table->checkStatus === 'Closed' && $this->activeStatuses === 0;
    }

    #[Computed]
    public function showReleasingIndicator(): bool
    {
        return $this->table->status === 'releasing';
    }

    #[Computed]
    public function hasDelay(): bool
    {
        if (isset($this->table->pendingMinutes) && $this->table->pendingMinutes > ($this->timeLimits['pending'] ?? 0)) return true;
        if (isset($this->table->productionMinutes) && $this->table->productionMinutes > ($this->timeLimits['in_production'] ?? 0)) return true;
        if (isset($this->table->transitMinutes) && $this->table->transitMinutes > ($this->timeLimits['in_transit'] ?? 0)) return true;
        if (isset($this->table->closedMinutes) && $this->table->closedMinutes > ($this->timeLimits['closed'] ?? 0)) return true;
        if (isset($this->table->releasingMinutes) && $this->table->releasingMinutes > ($this->timeLimits['releasing'] ?? 0)) return true;
        return false;
    }

    #[Computed]
    public function delayAnimation(): string
    {
        return $this->hasDelay ? 'animate-pulse-warning' : '';
    }

    #[Computed]
    public function statusTimestamps(): array
    {
        return [
            'pending' => $this->table->pendingTimestamp ?? null,
            'production' => $this->table->productionTimestamp ?? null,
            'transit' => $this->table->transitTimestamp ?? null,
            'closed' => $this->table->closedTimestamp ?? null,
            'releasing' => $this->table->releasingTimestamp ?? null,
        ];
    }

    #[Computed]
    public function selectionClasses(): string
    {
        if (!$this->selectionMode) {
            return '';
        }

        if ($this->isDisabled) {
            return 'opacity-40 cursor-not-allowed grayscale';
        }

        $classes = 'cursor-pointer';
        if ($this->isSelected) {
            $classes .= ' ring-4 ring-offset-2 ring-blue-500';
        }

        return $classes;
    }

    public function selectTable($tableId)
    {
        logger('🎯 TableCard::selectTable called', [
            'tableId' => $tableId,
            'selectionMode' => $this->selectionMode,
            'isDisabled' => $this->isDisabled,
        ]);
        
        if (!$this->selectionMode) {
            // Se não está em modo de seleção, navega para a mesa
            return redirect()->route('orders', ['tableId' => $tableId]);
        }

        // Em modo de seleção, dispara evento para o componente pai
        $this->dispatch('select-table-for-merge', tableId: $tableId);
    }

    public function render()
    {
        return view('livewire.components.table-card');
    }
}
