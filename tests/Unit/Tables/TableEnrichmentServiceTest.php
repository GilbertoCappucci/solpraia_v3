<?php

use App\Services\TableEnrichmentService;
use App\Services\GlobalSettingService;
use App\Models\Table;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use App\Enums\OrderStatusEnum;
use App\Enums\CheckStatusEnum;

test('setEmptyData sets defaults based on table status', function () {
    $global = Mockery::mock(GlobalSettingService::class);
    $service = new TableEnrichmentService($global);
    \Illuminate\Database\Eloquent\Model::setConnectionResolver(new class implements \Illuminate\Database\ConnectionResolverInterface {
        public function connection($name = null) {
            return new class {
                public function getPdo() {}
                public function getQueryGrammar() { return new class { public function getDateFormat() { return 'Y-m-d H:i:s'; } }; }
                public function query() { return new class { public function table($table = null) { return null; } }; }
            };
        }
        public function getDefaultConnection() { return null; }
        public function setDefaultConnection($name) {}
    });

    $table = new Table();
    $table->status = 'occupied';
    $table->updated_at = now()->subMinutes(3);
    $table->setRelation('checks', collect());

    $service->setEmptyData($table);

    expect($table->checkStatus)->toBeNull();
    expect($table->checkStatusLabel)->toBe('Ocupada');
    expect($table->ordersPending)->toBe(0);
});

test('enrichTableData fills check and orders info when active check exists', function () {
    $global = Mockery::mock(GlobalSettingService::class);
    $global->shouldReceive('getTimeLimits')->andReturn(['pending' => 5, 'in_production' => 5, 'in_transit' => 5]);
    $service = new TableEnrichmentService($global);
    \Illuminate\Database\Eloquent\Model::setConnectionResolver(new class implements \Illuminate\Database\ConnectionResolverInterface {
        public function connection($name = null) {
            return new class {
                public function getPdo() {}
                public function getQueryGrammar() { return new class { public function getDateFormat() { return 'Y-m-d H:i:s'; } }; }
                public function query() { return new class { public function table($table = null) { return null; } }; }
            };
        }
        public function getDefaultConnection() { return null; }
        public function setDefaultConnection($name) {}
    });

    // Ensure Auth facade resolves to a user to avoid facade root errors
    $container = new Container();
    $container->instance('auth', new class {
        public function user() {
            return new \App\Models\User();
        }
    });
    Facade::setFacadeApplication($container);

    $table = new Table();
    $table->status = 'occupied';
    $check = new stdClass();
    $check->id = 42;
    $check->status = CheckStatusEnum::OPEN->value;
    $check->total = 123.45;
    $check->created_at = now()->subMinutes(20);
    $check->updated_at = now()->subMinutes(10);

    $order = new stdClass();
    $order->status = OrderStatusEnum::PENDING->value;
    $order->status_changed_at = now()->subMinutes(15);
    $order->product = new stdClass();
    $order->product->production_local = 'kitchen';

    $check->orders = collect([$order]);

    $table->checks = collect([$check]);

    $result = $service->enrichTableData($table);

    expect($result->checkId)->toBe(42);
    expect($result->ordersPending)->toBe(1);
    expect($result->pendingMinutes)->toBeGreaterThan(0);
});
