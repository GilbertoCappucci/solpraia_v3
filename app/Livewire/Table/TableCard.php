<?php

namespace App\Livewire\Table;

use App\Models\Table;
use Livewire\Component;
use Livewire\Attributes\Computed;

class TableCard extends Component
{
    public $tableId;
    public $selectionMode = false;
    public $selectedTables = [];
    public $timeLimits = [];

    public function mount($tableId, $selectionMode = false, $selectedTables = [], $timeLimits = [])
    {
        $this->tableId = $tableId;
        $this->selectionMode = $selectionMode;
        $this->selectedTables = $selectedTables;
        $this->timeLimits = $timeLimits;
    }

    #[Computed]
    public function table()
    {
        // Carrega mesa com relacionamentos necessários
        return Table::with(['checks' => function ($query) {
            $query->with(['orders.currentStatusHistory', 'orders.product']);
        }])
        ->find($this->tableId);
    }

    #[Computed]
    public function enrichedTable()
    {
        $table = $this->table;
        
        if (!$table) {
            return null;
        }

        // Enriquece com dados computados (similar ao TableService::enrichTableData)
        $currentCheck = $table->checks->sortByDesc('created_at')->first();
        
        $table->checkId = $currentCheck?->id;
        $table->checkStatus = $currentCheck?->status;
        $table->checkTotal = $currentCheck?->total ?? 0;
        
        if ($currentCheck) {
            $orders = $currentCheck->orders;
            
            $table->ordersPending = $orders->filter(fn($o) => $o->currentStatusHistory?->status === 'pending')->count();
            $table->ordersInProduction = $orders->filter(fn($o) => $o->currentStatusHistory?->status === 'in_production')->count();
            $table->ordersInTransit = $orders->filter(fn($o) => $o->currentStatusHistory?->status === 'in_transit')->count();
            
            // Timestamps
            $pendingOrder = $orders->firstWhere(fn($o) => $o->currentStatusHistory?->status === 'pending');
            $productionOrder = $orders->firstWhere(fn($o) => $o->currentStatusHistory?->status === 'in_production');
            $transitOrder = $orders->firstWhere(fn($o) => $o->currentStatusHistory?->status === 'in_transit');
            
            $table->pendingTimestamp = $pendingOrder?->currentStatusHistory?->changed_at;
            $table->productionTimestamp = $productionOrder?->currentStatusHistory?->changed_at;
            $table->transitTimestamp = $transitOrder?->currentStatusHistory?->changed_at;
            
            if ($pendingOrder) {
                $table->pendingMinutes = abs((int) now()->diffInMinutes($table->pendingTimestamp));
            }
            if ($productionOrder) {
                $table->productionMinutes = abs((int) now()->diffInMinutes($table->productionTimestamp));
            }
            if ($transitOrder) {
                $table->transitMinutes = abs((int) now()->diffInMinutes($table->transitTimestamp));
            }
            
            if ($currentCheck->status === 'Closed') {
                $table->closedTimestamp = $currentCheck->updated_at;
                $table->closedMinutes = abs((int) now()->diffInMinutes($table->closedTimestamp));
            }
        }
        
        if ($table->status === 'releasing') {
            $table->releasingTimestamp = $table->updated_at;
            $table->releasingMinutes = abs((int) now()->diffInMinutes($table->releasingTimestamp));
        }
        
        return $table;
    }

    #[Computed]
    public function isSelected()
    {
        return $this->selectionMode && in_array($this->tableId, $this->selectedTables);
    }

    #[Computed]
    public function canTableBeMerged()
    {
        return !in_array($this->enrichedTable->status, ['releasing', 'close']);
    }

    #[Computed]
    public function isDisabled()
    {
        return $this->selectionMode && !$this->canTableBeMerged;
    }

    #[Computed]
    public function activeStatuses()
    {
        $table = $this->enrichedTable;
        $count = 0;
        
        if (isset($table->ordersPending) && $table->ordersPending > 0) $count++;
        if (isset($table->ordersInProduction) && $table->ordersInProduction > 0) $count++;
        if (isset($table->ordersInTransit) && $table->ordersInTransit > 0) $count++;
        
        return $count;
    }

    #[Computed]
    public function cardClasses()
    {
        $table = $this->enrichedTable;
        
        return match(true) {
            $table->status === 'releasing' => 'bg-gradient-to-br from-teal-50 to-teal-100 border-teal-400 hover:border-teal-500',
            $table->checkStatus === 'Open' => 'bg-white border-green-400 hover:border-green-500',
            $table->checkStatus === 'Closed' => 'bg-gradient-to-br from-orange-50 to-orange-100 border-orange-400 hover:border-orange-500',
            $table->checkStatus === 'Paid' => 'bg-gradient-to-br from-gray-50 to-gray-100 border-gray-400 hover:border-gray-500',
            $table->status === 'occupied' => 'bg-white border-green-400 hover:border-green-500',
            $table->status === 'reserved' => 'bg-gradient-to-br from-purple-50 to-purple-100 border-purple-400 hover:border-purple-500',
            $table->status === 'close' => 'bg-gradient-to-br from-red-50 to-red-100 border-red-600 hover:border-red-700',
            default => 'bg-white border-gray-300 hover:border-gray-400'
        };
    }

    #[Computed]
    public function bottomBarBg()
    {
        $table = $this->enrichedTable;
        
        return match(true) {
            $table->status === 'releasing' => 'bg-teal-100',
            $table->checkStatus === 'Open' => 'bg-white',
            $table->checkStatus === 'Closed' => 'bg-orange-100',
            $table->checkStatus === 'Paid' => 'bg-gray-100',
            $table->status === 'occupied' => 'bg-white',
            $table->status === 'reserved' => 'bg-purple-100',
            $table->status === 'close' => 'bg-red-100',
            default => 'bg-white'
        };
    }

    #[Computed]
    public function selectionClasses()
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

    #[Computed]
    public function hasDelay()
    {
        $table = $this->enrichedTable;
        
        if (isset($table->pendingMinutes) && $table->pendingMinutes > ($this->timeLimits['pending'] ?? 0)) return true;
        if (isset($table->productionMinutes) && $table->productionMinutes > ($this->timeLimits['in_production'] ?? 0)) return true;
        if (isset($table->transitMinutes) && $table->transitMinutes > ($this->timeLimits['in_transit'] ?? 0)) return true;
        if (isset($table->closedMinutes) && $table->closedMinutes > ($this->timeLimits['closed'] ?? 0)) return true;
        if (isset($table->releasingMinutes) && $table->releasingMinutes > ($this->timeLimits['releasing'] ?? 0)) return true;
        
        return false;
    }

    #[Computed]
    public function statusTimestamps()
    {
        $table = $this->enrichedTable;
        
        return [
            'pending' => $table->pendingTimestamp ?? null,
            'production' => $table->productionTimestamp ?? null,
            'transit' => $table->transitTimestamp ?? null,
            'closed' => $table->closedTimestamp ?? null,
            'releasing' => $table->releasingTimestamp ?? null,
        ];
    }

    #[Computed]
    public function gridClass()
    {
        return match($this->activeStatuses) {
            1 => 'grid-cols-1',
            2 => 'grid-cols-2',
            3 => 'grid-cols-3',
            default => 'grid-cols-1'
        };
    }

    #[Computed]
    public function dotSize()
    {
        return match($this->activeStatuses) {
            1 => 'w-6 h-6',
            2 => 'w-4 h-4',
            default => 'w-3 h-3'
        };
    }

    #[Computed]
    public function textSize()
    {
        return match($this->activeStatuses) {
            1 => 'text-2xl',
            2 => 'text-lg',
            default => 'text-sm'
        };
    }

    #[Computed]
    public function padding()
    {
        return match($this->activeStatuses) {
            1 => 'py-4',
            2 => 'py-3',
            default => 'py-2'
        };
    }

    #[Computed]
    public function showCenterLabel()
    {
        $table = $this->enrichedTable;
        return $table->checkStatus === 'Paid' && $this->activeStatuses === 0;
    }

    #[Computed]
    public function showClosedIndicator()
    {
        $table = $this->enrichedTable;
        return $table->checkStatus === 'Closed' && $this->activeStatuses === 0;
    }

    #[Computed]
    public function showReleasingIndicator()
    {
        return $this->enrichedTable->status === 'releasing';
    }

    public function selectTable()
    {
        if ($this->isDisabled) {
            return;
        }
        
        if ($this->selectionMode) {
            // Em modo de seleção, despacha evento para adicionar/remover da seleção
            $this->dispatch('select-table-for-merge', tableId: $this->tableId)->to(Tables::class);
        } else {
            // Modo normal, navega para a tela de pedidos/check
            $this->dispatch('table-selected', tableId: $this->tableId)->to(Tables::class);
        }
    }

    public function render()
    {
        return view('livewire.table.table-card');
    }
}
