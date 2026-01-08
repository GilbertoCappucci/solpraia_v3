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

class PayOrders extends Component
{

    public $table;
    public $ordersId;

    public float $checkTotal = 0.0;

    public $pix_enabled;
    public $pixPayload;
    public $pixKey;
    
    protected GlobalSettingService $globalSettings;
    protected PaymentService $paymentService;
    protected CheckService $checkService;

    private $userId;
    private $firstOrder;  

    public function mount(GlobalSettingService $globalSettings, PaymentService $paymentService, CheckService $checkService)
    {
        $this->userId = Auth::user()->user_id;
        $this->globalSettings = $globalSettings;
        $this->paymentService = $paymentService;
        $this->checkService = $checkService;

        $this->setOrders();
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

        if (empty($this->ordersId)) {
            session()->flash('error', 'Nenhum pedido encontrado.');
            return redirect()->route('tables');
        }

        $this->firstOrder = Order::with('check.table')->whereIn('id', $this->ordersId)->first();
        if (!$this->firstOrder) {
            session()->flash('error', 'Nenhum pedido encontrado.');
            return redirect()->route('tables');
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

    public function processPayment()
    {
        $this->setOrders();


        $this->paymentService = app(PaymentService::class);

        $orders = Order::whereIn('id', $this->ordersId)->get();
        foreach($orders as $order) {
            $this->paymentService->processOrderPayment($order->id);
        }

        session()->flash('message', 'Pagamento processado com sucesso para os pedidos selecionados.');

        $this->forgotSessionOrders();
        return redirect()->route('tables');
    }

    public function render()
    {
        return view('livewire.payment.pay-orders', [
            'checkOrders' => $this->getCheckOrders()
        ]);
    }
}
