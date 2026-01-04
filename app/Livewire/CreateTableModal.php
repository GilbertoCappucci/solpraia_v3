<?php

namespace App\Livewire;

use App\Services\TableService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateTableModal extends Component
{
    public $showModal = false;
    public $newTableName = '';
    public $newTableNumber = '';

    protected $tableService;

    public function boot(TableService $tableService)
    {
        $this->tableService = $tableService;
    }

    protected function getListeners()
    {
        return [
            'open-new-table-modal' => 'openModal',
        ];
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->resetForm();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->newTableName = '';
        $this->newTableNumber = '';
        $this->resetValidation();
    }

    public function createTable()
    {
        $validation = $this->tableService->validateTableData([
            'newTableName' => $this->newTableName,
            'newTableNumber' => $this->newTableNumber,
            'userId' => Auth::user()->user_id,
        ]);

        $this->validate($validation['rules'], $validation['messages']);

        $this->tableService->createTable(
            Auth::user()->user_id,
            $this->newTableName,
            $this->newTableNumber
        );

        session()->flash('success', 'Local criado com sucesso!');
        $this->closeModal();
        
        // Notifica o componente pai para atualizar a lista
        $this->dispatch('table-created');
    }

    public function render()
    {
        return view('livewire.create-table-modal');
    }
}