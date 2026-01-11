<?php

namespace App\Livewire\Order;

use App\Enums\CheckStatusEnum;
use App\Models\Check;
use App\Models\Table;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderTransferModal extends Component
{

    public $showModal = false;
    public $orderId;
    public $tableDestinationId = 1;


    protected $listeners = [
        'open-order-transfer-modal' => 'openModal',
    ];

    public function openModal($data)
    {
        $this->orderId = $data['orderId'];
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTablesData = [];
    }   

   #[Computed]
    public function selectedTablesData()
    {

        return Table::all()
            ->map(function ($table) {
                // Busca check ativo
                $check = Check::where('table_id', $table->id)
                    ->whereIn('status', [CheckStatusEnum::OPEN, CheckStatusEnum::CLOSED])
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

    public function render()
    {
        return view('livewire.order.order-transfer-modal');
    }
}
