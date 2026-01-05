<?php

namespace App\Livewire;

use App\Models\Table;
use App\Enums\TableStatusEnum;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TableCreateModal extends Component
{
    public $showModal = false;
    public $newTableName = '';
    public $newTableNumber = '';

    protected $listeners = ['open-new-table-modal' => 'openModal'];

    public function openModal()
    {
        $this->showModal = true;
        $this->newTableName = '';
        $this->newTableNumber = '';
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->newTableName = '';
        $this->newTableNumber = '';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function createTable()
    {
        $this->validate([
            'newTableNumber' => [
                'required',
                'integer',
                'min:1',
                'unique:tables,number,NULL,id,user_id,' . Auth::id()
            ],
            'newTableName' => 'nullable|string|max:255',
        ], [
            'newTableNumber.required' => 'O número do local é obrigatório.',
            'newTableNumber.integer' => 'O número deve ser um valor numérico.',
            'newTableNumber.min' => 'O número deve ser maior que zero.',
            'newTableNumber.unique' => 'Já existe um local com este número.',
        ]);

        Table::create([
            'user_id' => Auth::id(),
            'name' => $this->newTableName,
            'number' => $this->newTableNumber,
            'status' => TableStatusEnum::FREE->value,
        ]);

        session()->flash('success', 'Local criado com sucesso!');
        $this->closeModal();
        $this->dispatch('table-created');
    }

    public function render()
    {
        return view('livewire.table-create-modal');
    }
}
