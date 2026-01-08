<?php

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckService;
use App\Services\GlobalSettingService;
use App\Services\Menu\MenuService;
use App\Services\Order\OrderService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->checkService = Mockery::mock(CheckService::class);
    $this->stockService = Mockery::mock(StockService::class);
    $this->orderService = Mockery::mock(OrderService::class);
    $this->globalSettingService = Mockery::mock(GlobalSettingService::class);
    
    $this->menuService = new MenuService(
        $this->checkService,
        $this->stockService,
        $this->orderService,
        $this->globalSettingService
    );
});

afterEach(function () {
    Mockery::close();
});

test('getMenuName returns menu name', function () {
    $menu = Menu::factory()->create([
        'name' => 'Cardápio Principal',
        'admin_id' => $this->user->id,
    ]);

    $name = $this->menuService->getMenuName($menu->id);

    expect($name)->toBe('Cardápio Principal');
});

test('getActiveMenu returns active menu for user', function () {
    $menu = Menu::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
    ]);

    $this->globalSettingService
        ->shouldReceive('getActiveMenu')
        ->once()
        ->with($this->user->id)
        ->andReturn($menu);

    $result = $this->menuService->getActiveMenu($this->user->id);

    expect($result)->toBeInstanceOf(Menu::class)
        ->and($result->id)->toBe($menu->id);
});

test('getActiveMenu returns null when no active menu', function () {
    $this->globalSettingService
        ->shouldReceive('getActiveMenu')
        ->once()
        ->with($this->user->id)
        ->andReturnNull();

    $result = $this->menuService->getActiveMenu($this->user->id);

    expect($result)->toBeNull();
});

test('getActiveMenuId returns active menu id', function () {
    $menu = Menu::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
    ]);

    $this->globalSettingService
        ->shouldReceive('getActiveMenu')
        ->once()
        ->with($this->user->id)
        ->andReturn($menu);

    $menuId = $this->menuService->getActiveMenuId($this->user->id);

    expect($menuId)->toBe($menu->id);
});

test('getParentCategories returns only parent categories', function () {
    // Parent categories (category_id is null)
    $parent1 = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => null,
        'name' => 'Bebidas',
    ]);

    $parent2 = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => null,
        'name' => 'Comidas',
    ]);

    // Child category (should not be returned)
    Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => $parent1->id,
        'name' => 'Refrigerantes',
    ]);

    // Inactive parent (should not be returned)
    Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => false,
        'category_id' => null,
        'name' => 'Sobremesas',
    ]);

    $categories = $this->menuService->getParentCategories($this->user->id);

    expect($categories)->toHaveCount(2)
        ->and($categories->pluck('name')->toArray())->toContain('Bebidas', 'Comidas');
});

test('getChildCategories returns only child categories of parent', function () {
    $parent = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => null,
        'name' => 'Bebidas',
    ]);

    $child1 = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => $parent->id,
        'name' => 'Refrigerantes',
    ]);

    $child2 = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => $parent->id,
        'name' => 'Sucos',
    ]);

    // Child of different parent (should not be returned)
    $otherParent = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => null,
    ]);

    Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => $otherParent->id,
        'name' => 'Pizzas',
    ]);

    $categories = $this->menuService->getChildCategories($this->user->id, $parent->id);

    expect($categories)->toHaveCount(2)
        ->and($categories->pluck('name')->toArray())->toContain('Refrigerantes', 'Sucos');
});

test('getFilteredProducts returns products filtered by parent category', function () {
    $menu = Menu::factory()->create(['admin_id' => $this->user->id, 'active' => true]);
    
    $this->globalSettingService
        ->shouldReceive('getActiveMenu')
        ->andReturn($menu);

    $parent = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => null,
    ]);

    $child = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
        'category_id' => $parent->id,
    ]);

    $product = Product::factory()->create([
        'category_id' => $child->id,
        'active' => true,
        'name' => 'Coca Cola',
    ]);

    // Add product to menu
    $menu->products()->attach($product->id, ['price' => 5.00, 'active' => true]);

    $products = $this->menuService->getFilteredProducts($this->user->id, $parent->id);

    expect($products)->toHaveCount(1)
        ->and($products->first()->name)->toBe('Coca Cola');
});

test('getFilteredProducts returns only favorite products when flag is true', function () {
    $menu = Menu::factory()->create(['admin_id' => $this->user->id, 'active' => true]);
    
    $this->globalSettingService
        ->shouldReceive('getActiveMenu')
        ->andReturn($menu);

    $category = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
    ]);

    $favoriteProduct = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
        'favorite' => true,
        'name' => 'Produto Favorito',
    ]);

    $normalProduct = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
        'favorite' => false,
        'name' => 'Produto Normal',
    ]);

    // Add products to menu
    $menu->products()->attach($favoriteProduct->id, ['price' => 10.00, 'active' => true]);
    $menu->products()->attach($normalProduct->id, ['price' => 8.00, 'active' => true]);

    $products = $this->menuService->getFilteredProducts(
        $this->user->id,
        null,
        null,
        true // showFavoritesOnly
    );

    expect($products)->toHaveCount(1)
        ->and($products->first()->name)->toBe('Produto Favorito');
});

test('getFilteredProducts filters by search term', function () {
    $menu = Menu::factory()->create(['admin_id' => $this->user->id, 'active' => true]);
    
    $this->globalSettingService
        ->shouldReceive('getActiveMenu')
        ->andReturn($menu);

    $category = Category::factory()->create([
        'admin_id' => $this->user->id,
        'active' => true,
    ]);

    $product1 = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
        'name' => 'Coca Cola',
    ]);

    $product2 = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
        'name' => 'Pepsi',
    ]);

    // Add products to menu
    $menu->products()->attach($product1->id, ['price' => 5.00, 'active' => true]);
    $menu->products()->attach($product2->id, ['price' => 5.00, 'active' => true]);

    $products = $this->menuService->getFilteredProducts(
        $this->user->id,
        null,
        null,
        false,
        'coca'
    );

    expect($products)->toHaveCount(1)
        ->and($products->first()->name)->toBe('Coca Cola');
});

test('getProductWithMenuPrice returns product with menu price', function () {
    $menu = Menu::factory()->create(['admin_id' => $this->user->id, 'active' => true]);
    
    $this->globalSettingService
        ->shouldReceive('getActiveMenu')
        ->andReturn($menu);

    $category = Category::factory()->create(['admin_id' => $this->user->id]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
    ]);

    // Add product to menu with specific price
    $menu->products()->attach($product->id, ['price' => 15.50, 'active' => true]);

    $result = $this->menuService->getProductWithMenuPrice($this->user->id, $product->id);

    expect($result)->not->toBeNull()
        ->and($result->price)->toBe(15.50);
});

test('calculateCartTotal returns correct total', function () {
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

    $total = $this->menuService->calculateCartTotal($cart);

    expect($total)->toBe(35.00); // (10 * 2) + (15 * 1)
});

test('calculateCartItemCount returns correct count', function () {
    $cart = [
        1 => [
            'product' => ['id' => 1, 'name' => 'Product 1', 'price' => 10.00],
            'quantity' => 3,
        ],
        2 => [
            'product' => ['id' => 2, 'name' => 'Product 2', 'price' => 15.00],
            'quantity' => 2,
        ],
    ];

    $count = $this->menuService->calculateCartItemCount($cart);

    expect($count)->toBe(5); // 3 + 2
});
