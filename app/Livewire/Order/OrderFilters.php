<?php

namespace App\Livewire\Order;

use App\Enums\OrderStatusEnum;
use Livewire\Component;

class OrderFilters extends Component
{
    public $show = false;
    public $statusFilters = [];

    public function mount()
    {
        $this->statusFilters = session('orders.statusFilters', OrderStatusEnum::getValues());
    }

    public function getListeners()
    {
        return [
            'open-filter-modal' => 'openModal',
        ];
    }

    public function openModal()
    {
        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
    }

    public function toggleStatusFilter($status)
    {
        if (in_array($status, $this->statusFilters)) {
            $this->statusFilters = array_values(array_diff($this->statusFilters, [$status]));
        } else {
            $this->statusFilters[] = $status;
        }

        $this->saveAndEmit();
    }

    public function resetFilters()
    {
        $this->statusFilters = OrderStatusEnum::getValues();
        $this->saveAndEmit();
    }

    protected function saveAndEmit()
    {
        session(['orders.statusFilters' => $this->statusFilters]);
        $this->dispatch('filters-updated', statusFiltersCount: count($this->statusFilters));
        $this->dispatch('refresh-parent');
    }

    public function render()
    {
        return view('livewire.order.order-filters');
    }
}
