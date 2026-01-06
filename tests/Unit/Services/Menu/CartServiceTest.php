<?php

use App\Models\Product;
use App\Services\Menu\CartService;
use App\Services\StockService;

beforeEach(function () {
    $this->stockService = Mockery::mock(StockService::class);
    $this->cartService = new CartService($this->stockService);
});

afterEach(function () {
    Mockery::close();
});

test('addItem adds product to empty cart', function () {
    $product = Product::factory()->make([
        'id' => 1,
        'name' => 'Test Product',
        'price' => 10.00,
    ]);

    $this->stockService
        ->shouldReceive('hasStock')
        ->once()
        ->with(1, 1)
        ->andReturnTrue();

    $cart = [];
    $result = $this->cartService->addItem($cart, $product, 1);

    expect($result)->toBeTrue()
        ->and($cart)->toHaveKey(1)
        ->and($cart[1]['quantity'])->toBe(1)
        ->and($cart[1]['product']['name'])->toBe('Test Product')
        ->and($cart[1]['product']['price'])->toBe(10.00);
});

test('addItem increases quantity for existing product', function () {
    $product = Product::factory()->make([
        'id' => 1,
        'name' => 'Test Product',
        'price' => 10.00,
    ]);

    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Test Product', 'price' => 10.00],
            'quantity' => 1,
        ],
    ];

    $this->stockService
        ->shouldReceive('hasStock')
        ->once()
        ->with(1, 2)
        ->andReturnTrue();

    $result = $this->cartService->addItem($cart, $product, 1);

    expect($result)->toBeTrue()
        ->and($cart[1]['quantity'])->toBe(2);
});

test('addItem returns false when stock is insufficient', function () {
    $product = Product::factory()->make([
        'id' => 1,
        'name' => 'Test Product',
        'price' => 10.00,
    ]);

    $this->stockService
        ->shouldReceive('hasStock')
        ->once()
        ->with(1, 1)
        ->andReturnFalse();

    $cart = [];
    $result = $this->cartService->addItem($cart, $product, 1);

    expect($result)->toBeFalse()
        ->and($cart)->toBeEmpty();
});

test('removeItem removes product from cart when quantity is 1', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Test Product', 'price' => 10.00],
            'quantity' => 1,
        ],
    ];

    $this->cartService->removeItem($cart, 1);

    expect($cart)->not->toHaveKey(1);
});

test('removeItem decreases quantity when quantity is greater than 1', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Test Product', 'price' => 10.00],
            'quantity' => 3,
        ],
    ];

    $this->cartService->removeItem($cart, 1);

    expect($cart[1]['quantity'])->toBe(2);
});

test('removeItem does nothing for non-existent product', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Test Product', 'price' => 10.00],
            'quantity' => 1,
        ],
    ];

    $this->cartService->removeItem($cart, 999);

    expect($cart)->toHaveKey(1)
        ->and($cart[1]['quantity'])->toBe(1);
});

test('clearCart empties the cart', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Product 1', 'price' => 10.00],
            'quantity' => 2,
        ],
        2 => [
            'product' => ['id' => 2, 'name' => 'Product 2', 'price' => 15.00],
            'quantity' => 1,
        ],
    ];

    $this->cartService->clearCart($cart);

    expect($cart)->toBeEmpty();
});

test('calculateTotal returns correct total', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Product 1', 'price' => 10.00],
            'quantity' => 2,
        ],
        2 => [
            'product' => ['id' => 2, 'name' => 'Product 2', 'price' => 15.00],
            'quantity' => 3,
        ],
    ];

    $total = $this->cartService->calculateTotal($cart);

    expect($total)->toBe(65.00); // (10 * 2) + (15 * 3) = 20 + 45
});

test('calculateTotal returns zero for empty cart', function () {
    $cart = [];

    $total = $this->cartService->calculateTotal($cart);

    expect($total)->toBe(0.0);
});

test('calculateItemCount returns correct count', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Product 1', 'price' => 10.00],
            'quantity' => 2,
        ],
        2 => [
            'product' => ['id' => 2, 'name' => 'Product 2', 'price' => 15.00],
            'quantity' => 3,
        ],
    ];

    $count = $this->cartService->calculateItemCount($cart);

    expect($count)->toBe(5); // 2 + 3
});

test('calculateItemCount returns zero for empty cart', function () {
    $cart = [];

    $count = $this->cartService->calculateItemCount($cart);

    expect($count)->toBe(0);
});

test('validateStock returns true when all items have sufficient stock', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Product 1', 'price' => 10.00],
            'quantity' => 2,
        ],
        2 => [
            'product' => ['id' => 2, 'name' => 'Product 2', 'price' => 15.00],
            'quantity' => 1,
        ],
    ];

    $this->stockService
        ->shouldReceive('hasStock')
        ->with(1, 2)
        ->andReturnTrue();

    $this->stockService
        ->shouldReceive('hasStock')
        ->with(2, 1)
        ->andReturnTrue();

    $result = $this->cartService->validateStock($cart);

    expect($result)->toBeTrue();
});

test('validateStock returns false when any item has insufficient stock', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Product 1', 'price' => 10.00],
            'quantity' => 2,
        ],
        2 => [
            'product' => ['id' => 2, 'name' => 'Product 2', 'price' => 15.00],
            'quantity' => 1,
        ],
    ];

    $this->stockService
        ->shouldReceive('hasStock')
        ->with(1, 2)
        ->andReturnTrue();

    $this->stockService
        ->shouldReceive('hasStock')
        ->with(2, 1)
        ->andReturnFalse();

    $result = $this->cartService->validateStock($cart);

    expect($result)->toBeFalse();
});

test('getStockErrors returns errors for items with insufficient stock', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Product 1', 'price' => 10.00],
            'quantity' => 2,
        ],
        2 => [
            'product' => ['id' => 2, 'name' => 'Product 2', 'price' => 15.00],
            'quantity' => 1,
        ],
    ];

    $this->stockService
        ->shouldReceive('hasStock')
        ->with(1, 2)
        ->andReturnTrue();

    $this->stockService
        ->shouldReceive('hasStock')
        ->with(2, 1)
        ->andReturnFalse();

    $errors = $this->cartService->getStockErrors($cart);

    expect($errors)->toHaveCount(1)
        ->and($errors[0])->toContain('Product 2');
});

test('getStockErrors returns empty array when all items have sufficient stock', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Product 1', 'price' => 10.00],
            'quantity' => 2,
        ],
    ];

    $this->stockService
        ->shouldReceive('hasStock')
        ->with(1, 2)
        ->andReturnTrue();

    $errors = $this->cartService->getStockErrors($cart);

    expect($errors)->toBeEmpty();
});
