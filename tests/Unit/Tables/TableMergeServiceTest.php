<?php

use App\Services\Table\TableMergeService;

test('canTableBeMerged rejects excluded statuses', function () {
    $service = new TableMergeService();

    $reserved = (object)['status' => 'reserved'];
    $releasing = (object)['status' => 'releasing'];
    $close = (object)['status' => 'close'];
    $ok = (object)['status' => 'occupied'];

    expect($service->canTableBeMerged($reserved))->toBeFalse();
    expect($service->canTableBeMerged($releasing))->toBeFalse();
    expect($service->canTableBeMerged($close))->toBeFalse();
    expect($service->canTableBeMerged($ok))->toBeTrue();
});

test('getMergeableTables filters correctly', function () {
    $service = new TableMergeService();

    $t1 = (object)['status' => 'occupied'];
    $t2 = (object)['status' => 'reserved'];
    $collection = collect([$t1, $t2]);

    $result = $service->getMergeableTables($collection);
    expect($result->count())->toBe(1);
    expect($result->first())->toBe($t1);
});

test('canMergeTables requires at least two mergeable and one active check', function () {
    $service = new TableMergeService();

    // Less than 2 mergeable
    $a = (object)['status' => 'occupied', 'checkId' => 1];
    $only = collect([$a]);
    expect($service->canMergeTables($only))->toBeFalse();

    // Two mergeable but none has active check
    $b = (object)['status' => 'occupied'];
    $c = (object)['status' => 'occupied'];
    expect($service->canMergeTables(collect([$b, $c])))->toBeFalse();

    // Two mergeable and one has checkId
    $c->checkId = 2;
    expect($service->canMergeTables(collect([$b, $c])))->toBeTrue();
});
