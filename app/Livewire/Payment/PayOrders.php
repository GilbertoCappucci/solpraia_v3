<?php

namespace App\Livewire\Payment;

use App\Models\Check;
use App\Models\Order;
use App\Models\Table;
use App\Services\CheckService;
use App\Services\GlobalSettingService;
use Livewire\Component;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isNull;

class PayOrders extends Component
{

    public $table;
    public $ordersId;

    public float $checkTotal = 0.0;

    public $pix_enabled;
    public $pixPayload;
    public $pixKey;

    public $showConfirmModal = false;
    public $paymentTypeToProcess = null;
    
    protected GlobalSettingService $globalSettings;
    protected PaymentService $paymentService;
    protected CheckService $checkService;

    private $userId;
    private $firstOrder;  

    public function getListeners()
    {        
        $userId = Auth::user()->admin_id ?? null;
        return [
            "echo-private:global-setting-updated.{$userId},.global.setting.updated" => 'refreshSetting',
            "echo-private:order-status-history-created.{$userId},.order.status.history.created" => 'handleOrderStatusHistoryCreated',
        ];
    }

    public function refreshSetting()
    {
        $this->setPix();
    }

    public function handleOrderStatusHistoryCreated($data)
    {
        logger("Order status history created event received in PayOrders Livewire component", $data);
        
        $orderStatusHistoryId = $data['orderStatusHistoryId'] ?? null;
        if (!$orderStatusHistoryId) {
            logger("Order status history ID not found in event data");
            return redirect()->route('orders', $this->table->id);
        }
        
        $orderStatusHistory = \App\Models\OrderStatusHistory::find($orderStatusHistoryId);
        if (!$orderStatusHistory) {
            logger("Order status history not found", ['id' => $orderStatusHistoryId]);
            return redirect()->route('orders', $this->table->id);;
        }

        $order = $orderStatusHistory->order;
        if (!$order) {
            logger("Order not found for order status history", ['order_status_history_id' => $orderStatusHistoryId]);
            return redirect()->route('orders', $this->table->id);
        }
    
        if (!in_array($order->id, $this->ordersId)) {
            logger("Order ID {$order->id} not in current PayOrders orders list, not refreshing", ['ordersId' => $this->ordersId]);
            return redirect()->route('orders', $this->table->id);
        }

        if($orderStatusHistory->to_status === \App\Enums\OrderStatusEnum::PENDING->value) {
            logger("Order ID {$order->id} new status not permitted", ['order_id' => $order->id]);
            session()->flash('error', "O pedido #{$order->id} voltou ao status Pendente. Por favor, verifique os pedidos novamente.");
            
            $this->forgotSessionOrders();
            return redirect()->route('orders', $this->table->id);
        }   

        logger("Refreshing PayOrders component due to order status history change", ['order_id' => $order->id, 'new_status' => $orderStatusHistory->to_status]);
    }

    public function mount(GlobalSettingService $globalSettings, PaymentService $paymentService, CheckService $checkService)
    {
        $this->userId = Auth::user()->admin_id;
        $this->globalSettings = $globalSettings;
        $this->paymentService = $paymentService;
        $this->checkService = $checkService;

        $this->setOrders();
        
        if($this->firstOrder === null || $this->ordersId === null) {
            return;
        }

        $this->table = $this->firstOrder->check->table;

        $this->checkTotal = $this->checkService->calculateTotalOrders($this->ordersId);

        $this->setPix();
    }

    private function getCheckOrders()
    {
        return Order::with(['product', 'currentStatusHistory'])
            ->whereIn('id', $this->ordersId)
            ->get()
            ->groupBy(fn ($order) => $order->product->name);
    }

    private function forgotSessionOrders()
    {
        session()->forget('pay_orders');
    }

    private function setOrders()
    {
        $this->ordersId = session('pay_orders');
        if (empty($this->ordersId) || isNull($this->ordersId)) {

            session()->flash('error', 'Nenhum pedido encontrado.');
            //return redirect()->route('tables');
        }

        $this->firstOrder = Order::with('check.table')->whereIn('id', $this->ordersId)->first();
        if (!$this->firstOrder) {
            session()->flash('error', 'Nenhum pedido encontrado.');
            //return redirect()->route('tables');
        }
    }

    private function setPix()
    {
        $this->pix_enabled = $this->globalSettings->getPixEnabled($this->userId);
        $this->pixKey = $this->globalSettings->getPixKey($this->userId);
        $this->pixPayload = $this->paymentService->qrCodeOrders($this->ordersId);
    }

    public function goBack()
    {
        $this->forgotSessionOrders();
        return redirect()->route('orders', $this->table->id);
    }

    public function goToOrders()
    {
        $this->forgotSessionOrders();
        return redirect()->route('orders', $this->table->id);
    }

    public function confirmProcessPayment($type)
    {
        $this->paymentTypeToProcess = $type;
        $this->showConfirmModal = true;
    }

    public function processPayment()
    {
        $this->setOrders();

        if($this->ordersId === null or count($this->ordersId) === 0) {
            session()->flash('error', 'Nenhum pedido encontrado.');
            return redirect()->route('tables');
        }

        $this->paymentService = app(PaymentService::class);

        $orders = Order::whereIn('id', $this->ordersId)->get();
        foreach($orders as $order) {
            $this->paymentService->processOrderPayment($order->id);
        }

        session()->flash('message', 'Pagamento processado com sucesso para os pedidos selecionados.');

        $this->forgotSessionOrders();
        $this->showConfirmModal = false;
        $this->paymentTypeToProcess = null;
        return redirect()->route('tables');
        
    }

    public function render()
    {
        return view('livewire.payment.pay-orders', [
            'checkOrders' => $this->getCheckOrders()
        ]);
    }
}
