<?php

namespace App\Services\Payment;

use App\Models\Check;
use App\Models\GlobalSetting;
use App\Services\CheckService;
use App\Services\GlobalSettingService;
use App\Services\PixService;
use Carbon\Traits\Timestamp;
use Illuminate\Support\Facades\Auth;

class PaymentService
{

    private PixService $pixService;
    private GlobalSettingService $globalSettingService;
    private CheckService $checkService;
    public bool $pixEnabled = false;


    public function __construct(PixService $pixService, GlobalSettingService $globalSettingService, CheckService $checkService)
    {
        $this->pixService = $pixService;
        $this->globalSettingService = $globalSettingService;
        $this->checkService = $checkService;
    }

    public function processOrderPayment($orderId)
    {
        $order = \App\Models\Order::find($orderId);
        if (!$order) {
            throw new \Exception('Pedido não encontrado.');
        }

        $order->is_paid = true;
        $order->save();

        CheckService::updateCheckTotalAfterOrderPayment($order->check_id);

    }

    //Monta o QR Code PIX para o check
    public function qrCodeCheck(Check $check){
 
        $globalSetting = $this->globalSettingService->loadGlobalSettings(Auth::user());
        $this->pixEnabled = $globalSetting->pix_enabled;

        if ($this->pixEnabled) {
            $pixKey = $globalSetting->pix_key;
            if ($pixKey) {
 
                $checkTotal = $this->checkService->calculateTotal($check);

                if ($checkTotal > 0) {
                    $pixKeyType = $globalSetting->pix_key_type;
                    $pixName = $globalSetting->pix_name;
                    $pixCity = $globalSetting->pix_city;


                    return $this->pixService->generatePayload(
                        $pixKey,
                        $pixKeyType,
                        $pixName,
                        $pixCity,
                        $checkTotal,
                        $check->id // Use Check ID as transaction ID
                    );
                }
            }
        }

        return null;
    }

    //Monta o QR Code PIX para the orders
    public function qrCodeOrders($orders){
 
        $globalSetting = $this->globalSettingService->loadGlobalSettings(Auth::user());
        $this->pixEnabled = $globalSetting->pix_enabled;

        if ($this->pixEnabled) {
            
            $pixKey = $globalSetting->pix_key;
            
            if ($pixKey) {
                $transactionId = 0;
                $ordersTotal = $this->checkService->calculateTotalOrders($orders);

                if ($ordersTotal > 0) {
                    $pixKeyType = $globalSetting->pix_key_type;
                    $pixName = $globalSetting->pix_name;
                    $pixCity = $globalSetting->pix_city;

                    return $this->pixService->generatePayload(
                        $pixKey,
                        $pixKeyType,
                        $pixName,
                        $pixCity,
                        $ordersTotal,
                        $transactionId
                    );
                }
            }
        }

        return null;
    }
}