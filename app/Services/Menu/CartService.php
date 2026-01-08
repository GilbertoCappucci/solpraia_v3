<?php

namespace App\Services\Menu;

use App\Models\Product;
use App\Services\StockService;

class CartService
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Add item to cart with stock validation
     */
    public function addItem(array &$cart, Product $product, int $adminId): bool
    {
        $productId = $product->id;

        // Check stock availability
        $currentQty = isset($cart[$productId]) ? $cart[$productId]['quantity'] : 0;
        if (!$this->stockService->hasStock($productId, $currentQty + 1)) {
            return false;
        }

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                ],
                'quantity' => 1,
            ];
        }

        return true;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(array &$cart, int $productId): void
    {
        if (isset($cart[$productId])) {
            if ($cart[$productId]['quantity'] > 1) {
                $cart[$productId]['quantity']--;
            } else {
                unset($cart[$productId]);
            }
        }
    }

    /**
     * Clear cart
     */
    public function clearCart(array &$cart): void
    {
        $cart = [];
    }

    /**
     * Calculate cart total
     */
    public function calculateTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['product']['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Calculate cart item count
     */
    public function calculateItemCount(array $cart): int
    {
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Validate all cart items have sufficient stock
     */
    public function validateStock(array $cart): bool
    {
        foreach ($cart as $productId => $item) {
            if (!$this->stockService->hasStock($productId, $item['quantity'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get cart validation errors
     */
    public function getStockErrors(array $cart): array
    {
        $errors = [];
        foreach ($cart as $productId => $item) {
            if (!$this->stockService->hasStock($productId, $item['quantity'])) {
                $errors[] = "Estoque insuficiente para: {$item['product']['name']}";
            }
        }
        return $errors;
    }
}
