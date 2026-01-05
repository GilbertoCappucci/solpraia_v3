<?php

namespace Tests\Unit\Orders;

use App\Services\Check\CheckManagementService;
use App\Services\Order\OrderCancellationService;
use App\Services\Order\OrderOperationsService;
use App\Services\Order\OrderService;
use App\Services\Order\OrderStatusService;
use App\Services\Order\OrderValidationService;
use Mockery;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_delegates_to_underlying_services()
    {
        $checkManagement = Mockery::mock(CheckManagementService::class);
        $statusService = Mockery::mock(OrderStatusService::class);
        $cancellationService = Mockery::mock(OrderCancellationService::class);
        $operationsService = Mockery::mock(OrderOperationsService::class);
        $validationService = Mockery::mock(OrderValidationService::class);

        $checkManagement->shouldReceive('recalculateAllActiveChecks')->once();
        // return a Check instance (or null) to satisfy typed return
        $checkManagement->shouldReceive('findCheck')->with(10)->andReturn(new \App\Models\Check());
        $statusService->shouldReceive('updateOrderStatus')->with(1, 'new', 0)->andReturn(['ok' => true]);
        $operationsService->shouldReceive('duplicatePendingOrder')->with(5)->andReturn(['success' => true]);
        $operationsService->shouldReceive('calculateOrderStats')->andReturn(['total' => 0, 'time' => 0]);

        $service = new OrderService(
            $checkManagement,
            $statusService,
            $cancellationService,
            $operationsService,
            $validationService
        );

        $service->recalculateAllActiveChecks();
        $this->assertInstanceOf(\App\Models\Check::class, $service->findCheck(10));
        $this->assertEquals(['ok' => true], $service->updateOrderStatus(1, 'new'));
        $this->assertEquals(['success' => true], $service->duplicatePendingOrder(5));
        $this->assertEquals(['total' => 0, 'time' => 0], $service->calculateOrderStats(collect()));
    }
}
