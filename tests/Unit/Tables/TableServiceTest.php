<?php

use App\Services\Table\TableService;
use Illuminate\Support\Collection;

afterEach(function () {
    Mockery::close();
});

test('releaseTables delegates to merge service', function () {
    $merge = Mockery::mock(App\Services\Table\TableMergeService::class);
    $merge->shouldReceive('releaseTables')->once()->with([1,2]);

    $service = new App\Services\Table\TableService(
        Mockery::mock(App\Services\GlobalSettingService::class),
        Mockery::mock(App\Services\Table\TableFilterService::class),
        Mockery::mock(App\Services\Table\TableEnrichmentService::class),
        $merge
    );

    $service->releaseTables([1,2]);
});

test('canTableBeMerged delegates to merge service', function () {
    $table = new stdClass();
    $merge = Mockery::mock(App\Services\Table\TableMergeService::class);
    $merge->shouldReceive('canTableBeMerged')->once()->with($table)->andReturnTrue();

    $service = new App\Services\Table\TableService(
        Mockery::mock(App\Services\GlobalSettingService::class),
        Mockery::mock(App\Services\Table\TableFilterService::class),
        Mockery::mock(App\Services\Table\TableEnrichmentService::class),
        $merge
    );

    expect($service->canTableBeMerged($table))->toBeTrue();
});

test('getMergeableTables returns collection from merge service', function () {
    $expected = collect([1,2]);

    $merge = Mockery::mock(App\Services\Table\TableMergeService::class);
    $merge->shouldReceive('getMergeableTables')
        ->once()
        ->withArgs(function ($arg) {
            return $arg instanceof Collection;
        })
        ->andReturn($expected);

    $service = new App\Services\Table\TableService(
        Mockery::mock(App\Services\GlobalSettingService::class),
        Mockery::mock(App\Services\Table\TableFilterService::class),
        Mockery::mock(App\Services\Table\TableEnrichmentService::class),
        $merge
    );

    $result = $service->getMergeableTables(collect([1,2]));
    expect($result)->toBe($expected);
});

test('canMergeTables delegates to merge service', function () {
    $tables = collect([1,2]);

    $merge = Mockery::mock(App\Services\Table\TableMergeService::class);
    $merge->shouldReceive('canMergeTables')->once()->with($tables)->andReturnFalse();

    $service = new App\Services\Table\TableService(
        Mockery::mock(App\Services\GlobalSettingService::class),
        Mockery::mock(App\Services\Table\TableFilterService::class),
        Mockery::mock(App\Services\Table\TableEnrichmentService::class),
        $merge
    );

    expect($service->canMergeTables($tables))->toBeFalse();
});
