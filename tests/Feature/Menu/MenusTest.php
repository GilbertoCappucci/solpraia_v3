<?php

use App\Livewire\Menu\Menus;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create a table
    $this->table = Table::factory()->create([
        'user_id' => $this->user->id,
        'number' => '10',
        'name' => 'Mesa 10',
        'status' => 'free',
    ]);

    // Create a menu
    $this->menu = Menu::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Cardápio Principal',
        'active' => true,
    ]);

    // Set active menu in global settings
    \App\Models\GlobalSetting::updateOrCreate(
        ['user_id' => $this->user->id, 'key' => 'active_menu_id'],
        ['value' => $this->menu->id]
    );
});

test('menus component can be rendered', function () {
    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->assertStatus(200)
        ->assertViewIs('livewire.menu.menus');
});

test('menus component loads table and menu data correctly', function () {
    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->assertSet('tableId', $this->table->id)
        ->assertSet('selectedTable.id', $this->table->id)
        ->assertSet('activeMenuId', $this->menu->id)
        ->assertSet('title', 'Cardápio Principal');
});

test('menus component loads parent categories', function () {
    Category::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Bebidas',
        'active' => true,
        'category_id' => null,
    ]);

    Category::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Comidas',
        'active' => true,
        'category_id' => null,
    ]);

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->assertCount('parentCategories', 2);
});

test('menus component handles search updated event', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'active' => true,
    ]);

    $product1 = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Coca Cola',
        'active' => true,
    ]);

    $product2 = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Pepsi',
        'active' => true,
    ]);

    $this->menu->products()->attach($product1->id, ['price' => 5.00, 'active' => true]);
    $this->menu->products()->attach($product2->id, ['price' => 5.00, 'active' => true]);

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('search-updated', searchTerm: 'Coca')
        ->assertSet('searchTerm', 'Coca')
        ->assertCount('products', 1);
});

test('menus component handles category filter changed event', function () {
    $parent = Category::factory()->create([
        'user_id' => $this->user->id,
        'active' => true,
        'category_id' => null,
    ]);

    $child = Category::factory()->create([
        'user_id' => $this->user->id,
        'active' => true,
        'category_id' => $parent->id,
    ]);

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('category-filter-changed', [
            'parentCategoryId' => $parent->id,
            'childCategoryId' => null,
            'showFavoritesOnly' => false,
        ])
        ->assertSet('selectedParentCategoryId', $parent->id)
        ->assertSet('selectedChildCategoryId', null)
        ->assertSet('showFavoritesOnly', false);
});

test('menus component can add product to cart', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'active' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $this->menu->products()->attach($product->id, ['price' => 10.00, 'active' => true]);

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('add-to-cart', productId: $product->id)
        ->assertSet('cart.' . $product->id . '.quantity', 1)
        ->assertSet('cart.' . $product->id . '.product.price', 10.00);
});

test('menus component cannot add product without stock', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'active' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 0,
    ]);

    $this->menu->products()->attach($product->id, ['price' => 10.00, 'active' => true]);

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('add-to-cart', productId: $product->id)
        ->assertHasErrors()
        ->assertNotSet('cart.' . $product->id);
});

test('menus component can remove product from cart', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'active' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $this->menu->products()->attach($product->id, ['price' => 10.00, 'active' => true]);

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('add-to-cart', productId: $product->id)
        ->assertSet('cart.' . $product->id . '.quantity', 1)
        ->dispatch('remove-from-cart', productId: $product->id)
        ->assertNotSet('cart.' . $product->id);
});

test('menus component decreases quantity when removing from cart with multiple items', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'active' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $this->menu->products()->attach($product->id, ['price' => 10.00, 'active' => true]);

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('add-to-cart', productId: $product->id)
        ->dispatch('add-to-cart', productId: $product->id)
        ->assertSet('cart.' . $product->id . '.quantity', 2)
        ->dispatch('remove-from-cart', productId: $product->id)
        ->assertSet('cart.' . $product->id . '.quantity', 1);
});

test('menus component can clear entire cart', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'active' => true,
    ]);

    $product1 = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
    ]);

    $product2 = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
    ]);

    Stock::factory()->create(['product_id' => $product1->id, 'quantity' => 10]);
    Stock::factory()->create(['product_id' => $product2->id, 'quantity' => 10]);

    $this->menu->products()->attach($product1->id, ['price' => 10.00, 'active' => true]);
    $this->menu->products()->attach($product2->id, ['price' => 15.00, 'active' => true]);

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('add-to-cart', productId: $product1->id)
        ->dispatch('add-to-cart', productId: $product2->id)
        ->assertCount('cart', 2)
        ->dispatch('clear-cart')
        ->assertCount('cart', 0);
});

test('menus component handles back to orders event', function () {
    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('back-to-orders')
        ->assertRedirect(route('orders', ['tableId' => $this->table->id]));
});

test('menus component shows empty cart error when confirming without items', function () {
    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('confirm-order')
        ->assertHasErrors();
});

test('menus component can confirm order with items in cart', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'active' => true,
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'active' => true,
    ]);

    Stock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $this->menu->products()->attach($product->id, ['price' => 10.00, 'active' => true]);

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->dispatch('add-to-cart', productId: $product->id)
        ->dispatch('confirm-order')
        ->assertRedirect(route('orders', ['tableId' => $this->table->id]));

    // Verify order was created
    expect(\App\Models\Order::count())->toBe(1);
    
    // Verify check was created
    expect(\App\Models\Check::count())->toBe(1);
    
    // Verify stock was decremented
    expect($product->stock->fresh()->quantity)->toBe(9);
});

test('menus component refreshes on global setting updated event', function () {
    $newMenu = Menu::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Novo Cardápio',
        'active' => true,
    ]);

    \App\Models\GlobalSetting::updateOrCreate(
        ['user_id' => $this->user->id, 'key' => 'active_menu_id'],
        ['value' => $newMenu->id]
    );

    Livewire::test(Menus::class, ['tableId' => $this->table->id])
        ->assertSet('activeMenuId', $this->menu->id)
        ->dispatch('global.setting.updated')
        ->assertSet('activeMenuId', $newMenu->id)
        ->assertSet('title', 'Novo Cardápio');
});
