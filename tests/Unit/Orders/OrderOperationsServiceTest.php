<?php

namespace Tests\Unit\Orders;

use App\Services\Order\OrderOperationsService;
use App\Services\CheckService;
use App\Services\StockService;
use Carbon\Carbon;
use Tests\TestCase;

class OrderOperationsServiceTest extends TestCase
{
    public function test_calculate_order_stats_empty()
    {
        $ops = new OrderOperationsService(
            \Mockery::mock(StockService::class),
            \Mockery::mock(CheckService::class)
        );

        $result = $ops->calculateOrderStats(collect());

        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['time']);
    }

    public function test_calculate_order_stats_with_orders()
    {
        $ops = new OrderOperationsService(
            \Mockery::mock(StockService::class),
            \Mockery::mock(CheckService::class)
        );

        $now = Carbon::now();
        $older = $now->copy()->subMinutes(30);

        $orders = collect([
            (object)[ 'price' => 10.0, 'quantity' => 2, 'status_changed_at' => $older ],
            (object)[ 'price' => 5.0, 'quantity' => 1, 'status_changed_at' => $now ],
        ]);

        $result = $ops->calculateOrderStats($orders);

        $this->assertEquals(25.0, $result['total']);
        $this->assertGreaterThanOrEqual(30, $result['time']);
    }
}
