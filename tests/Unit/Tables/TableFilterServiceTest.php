<?php

use App\Services\TableFilterService;
use App\Services\GlobalSettingService;
use App\Enums\CheckStatusEnum;
use App\Models\Table;

test('applyFilters returns true when no filters active', function () {
    $global = Mockery::mock(GlobalSettingService::class);
    $service = new TableFilterService($global);

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
    $table->setRelation('checks', collect());
    $table->status = 'free';

    expect($service->applyFilters($table, [], [], [], [], 'OR'))->toBeTrue();
});

test('applyFilters matches table status filter', function () {
    $global = Mockery::mock(GlobalSettingService::class);
    $service = new TableFilterService($global);

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
    $table->setRelation('checks', collect());
    $table->status = 'occupied';
    $table->checks = collect();

    expect($service->applyFilters($table, ['occupied'], [], [], [], 'OR'))->toBeTrue();
});

test('applyFilters recognizes delayed_closed via global time limits', function () {
    $global = Mockery::mock(GlobalSettingService::class);
    $global->shouldReceive('getTimeLimits')->andReturn(['closed' => 5, 'pending' => 10, 'in_production' => 10, 'in_transit' => 10]);
    $service = new TableFilterService($global);

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
    $table->status = 'close';
    $check = new stdClass();
    $check->status = CheckStatusEnum::CLOSED->value;
    $check->updated_at = now()->subMinutes(10);
    $table->setRelation('checks', collect([$check]));
    $table->setRelation('user', new \App\Models\User());

    expect($service->applyFilters($table, [], ['delayed_closed'], [], [], 'OR'))->toBeTrue();
});
