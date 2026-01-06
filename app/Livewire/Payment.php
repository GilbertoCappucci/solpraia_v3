<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\CheckService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Url;

class Payment extends Component
{
    #[Url]
    public $productId;
    
    #[Url]
    public $status;
    
    public $orders;
    public $selectedOrders = [];
    public $totalAmount = 0;
    public $check;
    public $table;

    protected $checkService;

    public function boot(CheckService $checkService)
    {
        $this->checkService = $checkService;
    }

    public function mount()
    {
        // productId e status já são populados via query string pelo atributo #[Url]

        // Busca todos os pedidos deste produto com este status que não foram pagos
        $this->orders = Order::with(['product', 'check.table', 'currentStatusHistory'])
            ->where('product_id', $this->productId)
            ->whereHas('currentStatusHistory', function ($query) {
                $query->where('to_status', $this->status);
            })
            ->where('is_paid', false)
            ->get();

        if ($this->orders->isEmpty()) {
            session()->flash('error', 'Nenhum pedido encontrado para pagamento.');
            return redirect()->route('tables');
        }

        // Pega informações do check e table do primeiro pedido
        $firstOrder = $this->orders->first();
        $this->check = $firstOrder->check;
        $this->table = $firstOrder->check->table;

        // Inicializa todos os pedidos como selecionados
        $this->selectedOrders = $this->orders->pluck('id')->toArray();
        
        // Calcula total inicial
        $this->calculateTotal();
    }

    public function toggleOrder($orderId)
    {
        if (in_array($orderId, $this->selectedOrders)) {
            $this->selectedOrders = array_values(array_diff($this->selectedOrders, [$orderId]));
        } else {
            $this->selectedOrders[] = $orderId;
        }
        
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->totalAmount = $this->orders
            ->whereIn('id', $this->selectedOrders)
            ->sum(function ($order) {
                return $order->quantity * $order->price;
            });
    }

    public function confirmPayment()
    {
        if (empty($this->selectedOrders)) {
            session()->flash('error', 'Selecione pelo menos um pedido para pagar.');
            return;
        }

        DB::transaction(function () {
            // Marca apenas os pedidos selecionados como pagos
            Order::whereIn('id', $this->selectedOrders)->update([
                'is_paid' => true,
                'paid_at' => now()
            ]);

            // Recalcula total do check (exclui pedidos pagos do total)
            $this->checkService->recalculateCheckTotal($this->check);
        });

        $count = count($this->selectedOrders);
        session()->flash('success', "Pagamento de {$count} pedido(s) confirmado com sucesso!");
        return redirect()->route('orders', ['tableId' => $this->table->id]);
    }

    public function cancel()
    {
        return redirect()->route('orders', ['tableId' => $this->table->id]);
    }

    public function render()
    {
        return view('livewire.payment');
    }
}
