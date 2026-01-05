<?php

namespace Tests\Unit\Orders;

use App\Enums\CheckStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Check;
use App\Models\Order;
use App\Services\Order\OrderValidationService;
use Tests\TestCase;

class OrderValidationServiceTest extends TestCase
{
    private OrderValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new OrderValidationService();
    }

    public function test_validate_quantity()
    {
        $res = $this->service->validateQuantity(0);
        $this->assertFalse($res['valid']);
        $this->assertNotNull($res['message']);

        $res = $this->service->validateQuantity(3);
        $this->assertTrue($res['valid']);
        $this->assertNull($res['message']);
    }

    public function test_can_cancel_order()
    {
        $order = new Order();
        // simulate current status via relation so accessor returns expected value
        $order->setRelation('currentStatusHistory', (object)['to_status' => OrderStatusEnum::CANCELED->value]);

        $res = $this->service->canCancelOrder($order);
        $this->assertFalse($res['valid']);

        $order->setRelation('currentStatusHistory', (object)['to_status' => OrderStatusEnum::PENDING->value]);
        $res = $this->service->canCancelOrder($order);
        $this->assertTrue($res['valid']);
    }

    public function test_can_duplicate_order()
    {
        $order = new Order();
        $order->setRelation('currentStatusHistory', (object)['to_status' => OrderStatusEnum::IN_PRODUCTION->value]);

        $res = $this->service->canDuplicateOrder($order);
        $this->assertFalse($res['valid']);

        $order->setRelation('currentStatusHistory', (object)['to_status' => OrderStatusEnum::PENDING->value]);
        $res = $this->service->canDuplicateOrder($order);
        $this->assertTrue($res['valid']);
    }

    public function test_can_add_orders_to_check()
    {
        $check = new Check();
        $check->status = CheckStatusEnum::OPEN->value;

        $res = $this->service->canAddOrdersToCheck($check);
        $this->assertTrue($res['valid']);

        $check->status = 'locked';
        $res = $this->service->canAddOrdersToCheck($check);
        $this->assertFalse($res['valid']);
    }
}
